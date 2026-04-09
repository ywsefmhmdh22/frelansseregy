<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use Exception;

class PaymentController extends Controller
{
    // نسبة عمولة المنصة (9%)
    protected $platformFee = 0.09;

    /**
     * جلب سعر الصرف اللحظي مع التخزين المؤقت.
     */
    protected function getExchangeRate()
    {
        return Cache::remember('usd_to_egp_rate', 3600, function () {
            try {
                $apiKey = config('services.paymob.exchange_rate_api_key');
                if (!$apiKey) return (float) config('services.paymob.exchange_rate', 50.0);

                $response = Http::get("https://v6.exchangerate-api.com/v6/{$apiKey}/latest/USD");
                if ($response->successful()) {
                    $data = $response->json();
                    return (float) ($data['conversion_rates']['EGP'] ?? config('services.paymob.exchange_rate', 50.0));
                }
                return (float) config('services.paymob.exchange_rate', 50.0);
            } catch (Exception $e) {
                Log::error('Exchange Rate API Error: ' . $e->getMessage());
                return (float) config('services.paymob.exchange_rate', 50.0);
            }
        });
    }

    public function showDepositForm()
    {
        $exchangeRate = $this->getExchangeRate();
        $platformFee = $this->platformFee;
        return view('wallet.deposit', compact('exchangeRate', 'platformFee'));
    }

    public function initiatePayment(Request $request)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:1|max:100000',
            'currency'       => 'required|in:EGP,USD',
            'payment_method' => 'required|in:card,wallet',
            'phone_number'   => 'required_if:payment_method,wallet|nullable|regex:/^01[0125][0-9]{8}$/',
        ]);

        try {
            $user = auth()->user();
            $exchangeRate = $this->getExchangeRate();

            $paymobAmount = ($validated['currency'] === 'USD')
                ? ($validated['amount'] * $exchangeRate)
                : $validated['amount'];

            // 1. التوثيق
            $authResponse = Http::post('https://accept.paymob.com/api/auth/tokens', [
                'api_key' => config('services.paymob.api_key'),
            ]);

            if (!$authResponse->successful()) throw new Exception('فشل التوثيق مع بوابة الدفع.');
            $token = $authResponse->json()['token'];

            // 2. تسجيل الطلب
            $orderResponse = Http::withToken($token)->post('https://accept.paymob.com/api/ecommerce/orders', [
                'delivery_needed' => 'false',
                'amount_cents'    => (int) round($paymobAmount * 100),
                'currency'        => 'EGP',
                'items'           => [],
            ]);

            if (!$orderResponse->successful()) throw new Exception('فشل تسجيل الطلب.');
            $orderId = $orderResponse->json()['id'];

            // 3. توليد مفتاح الدفع
            $integrationId = ($validated['payment_method'] == 'wallet')
                ? config('services.paymob.wallet_integration_id')
                : config('services.paymob.integration_id');

            $paymentKeyResponse = Http::withToken($token)->post('https://accept.paymob.com/api/acceptance/payment_keys', [
                'amount_cents'    => (int) round($paymobAmount * 100),
                'expiration'      => 3600,
                'order_id'        => $orderId,
                'billing_data'    => [
                    'first_name'   => e($user->name ?? 'User'),
                    'last_name'    => 'Client',
                    'email'        => $user->email,
                    'phone_number' => $validated['phone_number'] ?? '01000000000',
                    'apartment' => 'NA', 'floor' => 'NA', 'street' => 'NA', 'building' => 'NA',
                    'shipping_method' => 'NA', 'postal_code' => 'NA', 'city' => 'Cairo',
                    'country' => 'EG', 'state' => 'Cairo',
                ],
                'currency'       => 'EGP',
                'integration_id' => $integrationId,
            ]);

            if (!$paymentKeyResponse->successful()) throw new Exception('فشل الحصول على تصريح الدفع.');
            $paymentToken = $paymentKeyResponse->json()['token'];

            // 4. تسجيل العملية كـ "معلقة"
            Transaction::create([
                'user_id'         => $user->id,
                'amount'          => (float) $validated['amount'],
                'currency'        => $validated['currency'],
                'type'            => 'deposit',
                'payment_id'      => (string) $orderId,
                'payment_method'  => $validated['payment_method'],
                'status'          => 'pending',
            ]);

            if ($validated['payment_method'] == 'wallet') {
                $walletPayResponse = Http::post('https://accept.paymob.com/api/acceptance/payments/pay', [
                    'source' => ['identifier' => $validated['phone_number'], 'subtype' => 'WALLET'],
                    'payment_token' => $paymentToken
                ]);
                $walletData = $walletPayResponse->json();
                if ($walletPayResponse->successful() && !empty($walletData['redirect_url'])) {
                    return redirect()->away($walletData['redirect_url']);
                }
                throw new Exception('فشل توجيه المحفظة.');
            } else {
                $iframeId = config('services.paymob.iframe_id');
                return redirect()->away("https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token=" . urlencode($paymentToken));
            }

        } catch (Exception $e) {
            Log::error('Payment Error: ' . $e->getMessage());
            return back()->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * الـ Callback العادي (لتوجيه المستخدم فقط)
     */
    public function callback(Request $request)
    {
        if (!$this->verifySecureSignature($request)) {
            return redirect()->route('wallet.index')->with('error', 'فشل التحقق الأمني.');
        }

        $success = $request->query('success');
        if ($success === 'true') {
            return redirect()->route('wallet.index')->with('success', 'جاري معالجة طلبك، سيظهر الرصيد فور التأكيد.');
        }

        return redirect()->route('wallet.index')->with('error', 'فشلت عملية الدفع.');
    }

    /**
     * الـ Webhook الاحترافي (تحديث الرصيد الحقيقي)
     */
    public function processedCallback(Request $request)
    {
        $data = $request->all();
        $hmac = $request->query('hmac');

        // ملاحظة: التحقق من HMAC في الـ Webhook يختلف قليلاً في ترتيب البيانات
        if (!$this->verifyWebhookSignature($request)) {
            Log::critical('SECURITY ALERT: Invalid Webhook HMAC');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $obj = $data['obj'];
        $orderId = (string) $obj['order']['id'];
        $success = $obj['success'];

        return DB::transaction(function () use ($orderId, $success, $obj) {
            $transaction = Transaction::where('payment_id', $orderId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if ($transaction && $success === true) {
                $exchangeRate = $this->getExchangeRate();
                $amountInUsd = ($transaction->currency == 'USD') ? $transaction->amount : ($transaction->amount / $exchangeRate);
                $finalNetUsd = $amountInUsd * (1 - $this->platformFee);

                $transaction->update([
                    'status' => 'completed',
                    'details' => 'Paymob ID: ' . $obj['id']
                ]);

                $wallet = Wallet::firstOrCreate(['user_id' => $transaction->user_id], ['balance' => 0]);
                $wallet->increment('balance', $finalNetUsd);

                Log::info("Wallet Credited: Order #{$orderId}");
                return response()->json(['status' => 'success']);
            }

            if ($transaction && $success !== true) {
                $transaction->update(['status' => 'failed']);
            }

            return response()->json(['status' => 'processed']);
        });
    }

    protected function verifySecureSignature(Request $request): bool
    {
        $hmac = $request->query('hmac');
        $secret = config('services.paymob.hmac_secret');
        if (!$hmac || !$secret) return false;

        $keys = ['amount_cents', 'created_at', 'currency', 'error_occured', 'has_parent_transaction', 'id', 'integration_id', 'is_3d_secure', 'is_auth', 'is_capture', 'is_refunded', 'is_standalone_payment', 'is_voided', 'order', 'owner', 'pending', 'source_data_pan', 'source_data_sub_type', 'source_data_type', 'success'];
        $data = $request->only($keys);
        ksort($data);
        $concatenatedString = implode('', $data);
        return hash_equals(hash_hmac('sha512', $concatenatedString, $secret), (string) $hmac);
    }

    protected function verifyWebhookSignature(Request $request): bool
    {
        $hmac = $request->query('hmac');
        $secret = config('services.paymob.hmac_secret');
        $obj = $request->json('obj');

        if (!$hmac || !$secret || !$obj) return false;

        // ترتيب البيانات للـ Webhook (يختلف عن الـ Callback)
        $concatenatedString =
            $obj['amount_cents'] .
            $obj['created_at'] .
            $obj['currency'] .
            $obj['error_occured'] .
            $obj['has_parent_transaction'] .
            $obj['id'] .
            $obj['integration_id'] .
            $obj['is_3d_secure'] .
            $obj['is_auth'] .
            $obj['is_capture'] .
            $obj['is_refunded'] .
            $obj['is_standalone_payment'] .
            $obj['is_voided'] .
            $obj['order']['id'] .
            $obj['owner'] .
            $obj['pending'] .
            $obj['source_data']['pan'] .
            $obj['source_data']['sub_type'] .
            $obj['source_data']['type'] .
            $obj['success'];

        return hash_equals(hash_hmac('sha512', $concatenatedString, $secret), (string) $hmac);
    }
}
