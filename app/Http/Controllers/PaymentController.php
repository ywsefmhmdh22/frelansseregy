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

/**
 * Class PaymentController
 * مسؤول عن بوابة الدفع (Paymob)، تحويل العملات، وشحن أرصدة المحافظ.
 * تم تحصين الكود ضد هجمات SQL Injection و Race Conditions مع توثيق كامل للعمليات المالية.
 */
class PaymentController extends Controller
{
    // نسبة عمولة المنصة (9%)
    protected $platformFee = 0.09;

    /**
     * جلب سعر الصرف اللحظي (USD to EGP) مع التخزين المؤقت لتقليل استهلاك الـ API.
     * * @return float
     */
    protected function getExchangeRate()
    {
        return Cache::remember('usd_to_egp_rate', 3600, function () {
            try {
                $apiKey = config('services.paymob.exchange_rate_api_key');

                if (!$apiKey) {
                    return (float) config('services.paymob.exchange_rate', 50.0);
                }

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

    /**
     * عرض صفحة شحن الرصيد للمستخدم.
     */
    public function showDepositForm()
    {
        $exchangeRate = $this->getExchangeRate();
        $platformFee = $this->platformFee;

        return view('wallet.deposit', compact('exchangeRate', 'platformFee'));
    }

    /**
     * بدء عملية الدفع الرقمي وتوليد تصاريح الدفع من Paymob.
     * * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initiatePayment(Request $request)
    {
        // 1. التحقق الصارم من المدخلات (يمنع أي محاولة حقن بيانات خبيثة)
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:1|max:100000',
            'currency'       => 'required|in:EGP,USD',
            'payment_method' => 'required|in:card,wallet',
            'phone_number'   => 'required_if:payment_method,wallet|nullable|regex:/^01[0125][0-9]{8}$/',
        ]);

        try {
            $user = auth()->user();
            $exchangeRate = $this->getExchangeRate();

            // تحويل المبلغ بناءً على العملة المختارة
            $paymobAmount = ($validated['currency'] === 'USD')
                ? ($validated['amount'] * $exchangeRate)
                : $validated['amount'];

            // 2. التوثيق مع بوابة الدفع
            $authResponse = Http::post('https://accept.paymob.com/api/auth/tokens', [
                'api_key' => config('services.paymob.api_key'),
            ]);

            if (!$authResponse->successful()) {
                throw new Exception('فشل التوثيق مع بوابة الدفع.');
            }

            $token = $authResponse->json()['token'];

            // 3. تسجيل الطلب في Paymob
            $orderResponse = Http::withToken($token)->post('https://accept.paymob.com/api/ecommerce/orders', [
                'delivery_needed' => 'false',
                'amount_cents'    => (int) round($paymobAmount * 100),
                'currency'        => 'EGP',
                'items'           => [],
            ]);

            if (!$orderResponse->successful()) {
                throw new Exception('فشل تسجيل الطلب لدى بوابة الدفع.');
            }

            $orderId = $orderResponse->json()['id'];

            // 4. توليد Payment Key
            $integrationId = ($validated['payment_method'] == 'wallet')
                ? config('services.paymob.wallet_integration_id')
                : config('services.paymob.integration_id');

            $paymentKeyResponse = Http::withToken($token)->post('https://accept.paymob.com/api/acceptance/payment_keys', [
                'amount_cents'    => (int) round($paymobAmount * 100),
                'expiration'      => 3600,
                'order_id'        => $orderId,
                'billing_data'    => [
                    'first_name'   => e($user->name ?? 'User'), // Sanitized Name
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

            if (!$paymentKeyResponse->successful()) {
                throw new Exception('فشل الحصول على تصريح الدفع.');
            }

            $paymentToken = $paymentKeyResponse->json()['token'];

            // 5. تسجيل العملية في قاعدة البيانات باستخدام Eloquent (مؤمن ضد SQL Injection)
            Transaction::create([
                'user_id'        => $user->id,
                'amount'         => (float) $validated['amount'],
                'currency'       => $validated['currency'],
                'type'           => 'deposit',
                'payment_id'     => (string) $orderId,
                'payment_method' => $validated['payment_method'],
                'status'         => 'pending',
            ]);

            // 6. التوجيه لبوابة الدفع بناءً على الطريقة المختارة
            if ($validated['payment_method'] == 'wallet') {
                $walletPayResponse = Http::post('https://accept.paymob.com/api/acceptance/payments/pay', [
                    'source' => [
                        'identifier' => $validated['phone_number'],
                        'subtype'    => 'WALLET'
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
            Log::error('Secure Payment Initiation Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage());
        }
    }

    /**
     * استقبال نتيجة الدفع (Callback) والتحقق من التوقيع الرقمي (HMAC).
     */
    public function callback(Request $request)
    {
        // 1. التحقق من أمان التوقيع (HMAC) لمنع التلاعب بالنتائج
        if (!$this->verifySecureSignature($request)) {
            Log::critical('SECURITY ALERT: Tampered payment attempt from IP: ' . $request->ip());
            return redirect()->route('wallet.index')->with('error', 'فشل التحقق الأمني من العملية.');
        }

        $success = $request->query('success');
        $orderId = (string) $request->query('order'); // تأمين النوع

        if ($success === 'true' && $orderId) {
            try {
                // 2. استخدام Transaction و Row Locking لضمان الدقة المالية ومنع الـ Race Conditions
                return DB::transaction(function () use ($orderId) {
                    $transaction = Transaction::where('payment_id', $orderId)
                        ->where('status', 'pending')
                        ->lockForUpdate() // منع أي عملية أخرى من لمس هذا السجل حالياً
                        ->first();

                    if ($transaction) {
                        $exchangeRate = $this->getExchangeRate();

                        // حساب المبلغ الصافي بالدولار للمحفظة
                        $amountInUsd = ($transaction->currency == 'USD')
                            ? $transaction->amount
                            : ($transaction->amount / $exchangeRate);

                        $finalNetUsd = $amountInUsd * (1 - $this->platformFee);

                        // تحديث السجل
                        $transaction->update([
                            'status'           => 'success',
                            'converted_amount' => $finalNetUsd
                        ]);

                        // تحديث رصيد المحفظة بأمان
                        $wallet = Wallet::firstOrCreate(['user_id' => $transaction->user_id], ['balance' => 0]);
                        $wallet->increment('balance', $finalNetUsd);

                        return redirect()->route('wallet.index')->with('success', 'تم شحن رصيد محفظتك بنجاح!');
                    }
                    return redirect()->route('wallet.index');
                });
            } catch (Exception $e) {
                Log::error('Secure Callback Transaction Error: ' . $e->getMessage());
                return redirect()->route('wallet.index')->with('error', 'خطأ في معالجة تحديث الرصيد.');
            }
        }

        return redirect()->route('wallet.index')->with('error', 'تم إلغاء عملية الدفع أو فشلت.');
    }

    /**
     * التحقق من الـ HMAC المرسل من Paymob لضمان عدم تزوير الـ Callback.
     * * @param  Request $request
     * @return bool
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
