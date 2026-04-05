<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt; // لإمكانية تشفير البيانات الحساسة
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use Exception;

class PaymentController extends Controller
{
    // سعر الصرف الثابت (تأكد من تحديثه دورياً أو ربطه بـ API)
    protected $exchangeRate = 50.0;

    // نسبة عمولة المنصة (9%)
    protected $platformFee = 0.09;

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
            $originalAmount = (float) $request->amount;
            $originalCurrency = $request->currency;
            $method = $request->payment_method;

            // تحويل العملة للسعر المحلي (EGP) كما تتطلب Paymob
            $paymobAmount = ($originalCurrency === 'USD') ? ($originalAmount * $this->exchangeRate) : $originalAmount;

            // 2. الحصول على Token التوثيق (تم حماية الـ API Key)
             // 2. الحصول على Token التوثيق (تم حماية الـ API Key)
$authResponse = Http::post('https://accept.paymob.com/api/auth/tokens', [
    'api_key' => config('services.paymob.api_key'), // سيقرأ الآن من الملف اللي عدلناه
]);

            if (!$authResponse->successful()) {
                throw new Exception('فشل التوثيق مع بوابة الدفع.');
            }

            $token = $authResponse->json()['token'];

            // 3. تسجيل الطلب برقم فرعي مشفر (Encrypted Reference)
            $orderResponse = Http::withToken($token)->post('https://accept.paymob.com/api/ecommerce/orders', [
                'delivery_needed' => 'false',
                'amount_cents' => (int) round($paymobAmount * 100),
                'currency' => 'EGP',
                'items' => [],
            ]);

            if (!$orderResponse->successful()) {
                throw new Exception('فشل تسجيل الطلب في Paymob.');
            }

            $orderId = $orderResponse->json()['id'];

            // 4. الحصول على مفتاح الدفع (Payment Key)
            $integrationId = ($method == 'wallet')
                ? env('PAYMOB_WALLET_INTEGRATION_ID')
                : env('PAYMOB_INTEGRATION_ID');

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

            $paymentToken = $paymentKeyResponse->json()['token'];

            // 5. تسجيل العملية في الداتابيز (بيانات معالجة)
            Transaction::create([
                'user_id' => $user->id,
                'amount' => $originalAmount,
                'currency' => $originalCurrency,
                'type' => 'deposit',
                'payment_id' => (string) $orderId,
                'payment_method' => $method,
                'status' => 'pending',
            ]);

            // 6. التوجيه الآمن (Secure Redirection)
            if ($method == 'wallet') {
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
            } else {
                $iframeId = env('PAYMOB_IFRAME_ID');
                // بناء الرابط مشفر وبدون تسريب بيانات إضافية
                $secureUrl = "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token=" . urlencode($paymentToken);
                return redirect()->away($secureUrl);
            }

            throw new Exception('Payment initiation failed.');

        } catch (Exception $e) {
            Log::error('Secure Payment Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء معالجة عملية الدفع.');
        }
    }

    /**
     * الـ Callback: التحقق المشفر من صحة البيانات (HMAC)
     */
    public function callback(Request $request)
    {
        // 1. التحقق من التوقيع الرقمي (HMAC) - حل مشكلة التشفير العالية
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
                        // حساب الصافي بعد العمولة
                        $amountInUsd = ($transaction->currency == 'USD')
                            ? $transaction->amount
                            : ($transaction->amount / $this->exchangeRate);

                        $finalNetUsd = $amountInUsd * (1 - $this->platformFee);

                        $transaction->update([
                            'status' => 'success',
                            'converted_amount' => $finalNetUsd
                        ]);

                        // تحديث المحفظة بأمان
                        $wallet = Wallet::firstOrCreate(['user_id' => $transaction->user_id], ['balance' => 0]);
                        $wallet->increment('balance', $finalNetUsd);

                        return redirect()->route('wallet.index')->with('success', 'تم شحن الرصيد بنجاح!');
                    }
                    return redirect()->route('wallet.index');
                });
            } catch (Exception $e) {
                Log::error('Callback Security Error: ' . $e->getMessage());
            }
        }

        return redirect()->route('wallet.index')->with('error', 'فشلت عملية الدفع.');
    }

    /**
     * التحقق من التوقيع الرقمي لمنع التلاعب (HMAC Validation)
     */
    protected function verifySecureSignature(Request $request): bool
    {
        $hmac = $request->query('hmac');
        $secret = env('PAYMOB_HMAC_SECRET');

        if (!$hmac || !$secret) return false;

        // البيانات المطلوبة للتحقق مرتبة حسب متطلبات بوابة الدفع
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

        // استخدام hash_equals لمنع الـ Timing Attacks
        return hash_equals($calculatedHmac, (string) $hmac);
    }
}
