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
     * جلب سعر الصرف اللحظي (USD to EGP) من الـ API وتخزينه ساعة في الكاش
     */
    protected function getExchangeRate()
    {
        return Cache::remember('usd_to_egp_rate', 3600, function () {
            try {
                $apiKey = config('services.paymob.exchange_rate_api_key');

                // التأكد من وجود المفتاح قبل إرسال الطلب
                if (!$apiKey) {
                    return (float) config('services.paymob.exchange_rate', 50.0);
                }

                $response = Http::get("https://v6.exchangerate-api.com/v6/{$apiKey}/latest/USD");

                if ($response->successful()) {
                    $data = $response->json();
                    return (float) $data['conversion_rates']['EGP'];
                }

                return (float) config('services.paymob.exchange_rate', 50.0);
            } catch (Exception $e) {
                Log::error('Exchange Rate API Error: ' . $e->getMessage());
                return (float) config('services.paymob.exchange_rate', 50.0);
            }
        });
    }

    /**
     * عرض صفحة شحن الرصيد
     */
    public function showDepositForm()
    {
        $exchangeRate = $this->getExchangeRate();
        $platformFee = $this->platformFee;

        return view('wallet.deposit', compact('exchangeRate', 'platformFee'));
    }

    /**
     * بدء عملية الدفع - معالجة مشفرة وآمنة تماماً
     */
    public function initiatePayment(Request $request)
    {
        // 1. التحقق الصارم من المدخلات
        $request->validate([
            'amount' => 'required|numeric|min:1|max:100000',
            'currency' => 'required|in:EGP,USD',
            'payment_method' => 'required|in:card,wallet',
            'phone_number' => 'required_if:payment_method,wallet|nullable|regex:/^01[0125][0-9]{8}$/',
        ]);

        try {
            $user = auth()->user();
            $exchangeRate = $this->getExchangeRate();

            // تحويل العملة للسعر المحلي (EGP) بناءً على سعر السوق اللحظي
            $paymobAmount = ($request->currency === 'USD')
                ? ($request->amount * $exchangeRate)
                : $request->amount;

            // 2. الحصول على Token التوثيق
            $authResponse = Http::post('https://accept.paymob.com/api/auth/tokens', [
                'api_key' => config('services.paymob.api_key'),
            ]);

            if (!$authResponse->successful()) {
                throw new Exception('فشل التوثيق مع بوابة الدفع. يرجى مراجعة الإعدادات.');
            }

            $token = $authResponse->json()['token'];

            // 3. تسجيل الطلب
            $orderResponse = Http::withToken($token)->post('https://accept.paymob.com/api/ecommerce/orders', [
                'delivery_needed' => 'false',
                'amount_cents' => (int) round($paymobAmount * 100),
                'currency' => 'EGP',
                'items' => [],
            ]);

            if (!$orderResponse->successful()) {
                throw new Exception('فشل تسجيل الطلب لدى بوابة الدفع.');
            }

            $orderId = $orderResponse->json()['id'];

            // 4. الحصول على مفتاح الدفع (Payment Key)
            $integrationId = ($request->payment_method == 'wallet')
                ? config('services.paymob.wallet_integration_id')
                : config('services.paymob.integration_id');

            $paymentKeyResponse = Http::withToken($token)->post('https://accept.paymob.com/api/acceptance/payment_keys', [
                'amount_cents' => (int) round($paymobAmount * 100),
                'expiration' => 3600,
                'order_id' => $orderId,
                'billing_data' => [
                    'first_name' => e($user->name ?? 'User'),
                    'last_name'  => 'Client',
                    'email'      => $user->email,
                    'phone_number' => $request->phone_number ?? '01000000000',
                    'apartment' => 'NA', 'floor' => 'NA', 'street' => 'NA', 'building' => 'NA',
                    'shipping_method' => 'NA', 'postal_code' => 'NA', 'city' => 'Cairo',
                    'country' => 'EG', 'state' => 'Cairo',
                ],
                'currency' => 'EGP',
                'integration_id' => $integrationId,
            ]);

            if (!$paymentKeyResponse->successful()) {
                throw new Exception('فشل الحصول على مفتاح تصريح الدفع.');
            }

            $paymentToken = $paymentKeyResponse->json()['token'];

            // 5. تسجيل العملية في الداتابيز
            Transaction::create([
                'user_id' => $user->id,
                'amount' => (float) $request->amount,
                'currency' => $request->currency,
                'type' => 'deposit',
                'payment_id' => (string) $orderId,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ]);

            // 6. التوجيه الآمن لبوابة الدفع
            if ($request->payment_method == 'wallet') {
                $walletPayResponse = Http::post('https://accept.paymob.com/api/acceptance/payments/pay', [
                    'source' => [
                        'identifier' => $request->phone_number,
                        'subtype' => 'WALLET'
                    ],
                    'payment_token' => $paymentToken
                ]);

                $walletData = $walletPayResponse->json();
                if ($walletPayResponse->successful() && !empty($walletData['redirect_url'])) {
                    return redirect()->away($walletData['redirect_url']);
                }
                throw new Exception('فشل توجيه المحفظة الإلكترونية.');
            } else {
                $iframeId = config('services.paymob.iframe_id');
                $secureUrl = "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token=" . urlencode($paymentToken);
                return redirect()->away($secureUrl);
            }

        } catch (Exception $e) {
            Log::error('Secure Payment Error: ' . $e->getMessage());

            // بدلاً من dd، نعود للخلف برسالة خطأ آمنة للمستخدم
            return back()->with('error', 'حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage());
        }
    }

    /**
     * الـ Callback: التحقق من صحة البيانات (HMAC)
     */
    public function callback(Request $request)
    {
        if (!$this->verifySecureSignature($request)) {
            Log::critical('تحذير: محاولة تلاعب ببيانات الدفع من IP: ' . $request->ip());
            return redirect()->route('wallet.index')->with('error', 'فشل التحقق من أمان العملية.');
        }

        $success = $request->query('success');
        $orderId = $request->query('order');

        if ($success === 'true' && $orderId) {
            try {
                return DB::transaction(function () use ($orderId) {
                    $transaction = Transaction::where('payment_id', (string)$orderId)
                        ->where('status', 'pending')
                        ->lockForUpdate()
                        ->first();

                    if ($transaction) {
                        $exchangeRate = $this->getExchangeRate();

                        $amountInUsd = ($transaction->currency == 'USD')
                            ? $transaction->amount
                            : ($transaction->amount / $exchangeRate);

                        $finalNetUsd = $amountInUsd * (1 - $this->platformFee);

                        $transaction->update([
                            'status' => 'success',
                            'converted_amount' => $finalNetUsd
                        ]);

                        $wallet = Wallet::firstOrCreate(['user_id' => $transaction->user_id], ['balance' => 0]);
                        $wallet->increment('balance', $finalNetUsd);

                        return redirect()->route('wallet.index')->with('success', 'تم شحن الرصيد بنجاح!');
                    }
                    return redirect()->route('wallet.index');
                });
            } catch (Exception $e) {
                Log::error('Callback DB Error: ' . $e->getMessage());
                return redirect()->route('wallet.index')->with('error', 'حدث خطأ أثناء تحديث المحفظة.');
            }
        }

        return redirect()->route('wallet.index')->with('error', 'فشلت عملية الدفع أو تم إلغاؤها.');
    }

    /**
     * التحقق من التوقيع الرقمي (HMAC Validation)
     */
    protected function verifySecureSignature(Request $request): bool
    {
        $hmac = $request->query('hmac');
        $secret = config('services.paymob.hmac_secret');

        if (!$hmac || !$secret) return false;

        $data = $request->only([
            'amount_cents', 'created_at', 'currency', 'error_occured',
            'has_parent_transaction', 'id', 'integration_id', 'is_3d_secure',
            'is_auth', 'is_capture', 'is_refunded', 'is_standalone_payment',
            'is_voided', 'order', 'owner', 'pending', 'source_data_pan',
            'source_data_sub_type', 'source_data_type', 'success'
        ]);

        ksort($data);
        $concatenatedString = implode('', $data);
        $calculatedHmac = hash_hmac('sha512', $concatenatedString, $secret);

        return hash_equals($calculatedHmac, (string) $hmac);
    }
}
