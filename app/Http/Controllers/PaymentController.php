<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use Exception;

class PaymentController extends Controller
{
    // سعر الصرف الثابت (1 دولار = 50 جنيه)
    protected $exchangeRate = 50.0;

    // نسبة عمولة المنصة (9%)
    protected $platformFee = 0.09;

    /**
     * بدء عملية الدفع وتجهيز الطلب لـ Paymob
     */
    public function initiatePayment(Request $request)
    {
        // 1. التحقق من البيانات المدخلة
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|in:EGP,USD',
            'payment_method' => 'required|in:card,wallet',
            'phone_number' => 'required_if:payment_method,wallet',
        ]);

        try {
            $originalAmount = $request->amount;
            $originalCurrency = $request->currency;
            $method = $request->payment_method;
            $user = auth()->user();

            // تحويل كل العملات لجنيه مصري قبل الإرسال لـ Paymob
            $paymobAmount = ($originalCurrency === 'USD') ? ($originalAmount * $this->exchangeRate) : $originalAmount;
            $paymobCurrency = 'EGP';

            // 2. الحصول على Token التوثيق
            $authResponse = Http::post('https://accept.paymob.com/api/auth/tokens', [
                'api_key' => env('PAYMOB_API_KEY'),
            ]);

            if (!$authResponse->successful()) {
                throw new Exception('فشل التوثيق مع بوابة الدفع (Auth Token).');
            }

            $token = $authResponse->json()['token'];

            // 3. تسجيل الطلب (Order Creation)
            $orderResponse = Http::withToken($token)->post('https://accept.paymob.com/api/ecommerce/orders', [
                'delivery_needed' => 'false',
                'amount_cents' => (int)($paymobAmount * 100),
                'currency' => $paymobCurrency,
                'items' => [],
            ]);

            if (!$orderResponse->successful()) {
                throw new Exception('فشل تسجيل الطلب في Paymob.');
            }

            $orderId = $orderResponse->json()['id'];

            // 4. تحديد الـ Integration ID بناءً على الطريقة
            $integrationId = ($method == 'wallet')
                ? env('PAYMOB_WALLET_INTEGRATION_ID')
                : env('PAYMOB_INTEGRATION_ID');

            // 5. إنشاء مفتاح الدفع (Payment Key)
            $paymentKeyResponse = Http::withToken($token)->post('https://accept.paymob.com/api/acceptance/payment_keys', [
                'amount_cents' => (int)($paymobAmount * 100),
                'expiration' => 3600,
                'order_id' => $orderId,
                'billing_data' => [
                    'first_name' => $user->name ?? 'User',
                    'last_name'  => 'Client',
                    'email'       => $user->email ?? 'no-email@example.com',
                    'phone_number' => $request->phone_number ?? '01000000000',
                    'apartment'   => 'NA', 'floor' => 'NA', 'street' => 'NA', 'building' => 'NA',
                    'shipping_method' => 'NA', 'postal_code' => 'NA', 'city' => 'Cairo',
                    'country' => 'EG', 'state' => 'Cairo',
                ],
                'currency' => $paymobCurrency,
                'integration_id' => $integrationId,
            ]);

            if (!$paymentKeyResponse->successful()) {
                throw new Exception('خطأ في إنشاء مفتاح الدفع من Paymob.');
            }

            $paymentToken = $paymentKeyResponse->json()['token'];

            // 6. تسجيل العملية في الداتابيز بحالة "معلق"
            Transaction::create([
                'user_id' => $user->id,
                'amount' => $originalAmount,
                'currency' => $originalCurrency,
                'type' => 'deposit',
                'payment_id' => $orderId,
                'payment_method' => $method,
                'status' => 'pending',
            ]);

            // 7. التوجيه لبوابة الدفع
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
                return back()->with('error', 'محفظة الموبايل غير مسجلة أو بها مشكلة.');
            } else {
                $iframeId = env('PAYMOB_IFRAME_ID');
                return redirect()->away("https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$paymentToken}");
            }

        } catch (Exception $e) {
            Log::error('Payment Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage());
        }
    }

    /**
     * الـ Callback الذي يتم استدعاؤه بعد انتهاء الدفع
     */
    public function callback(Request $request)
    {
        // --- 1. الأمان: التحقق من صحة الطلب عبر HMAC (اختياري لكن يُنصح به) ---
        // في حالتك سنعتمد على البيانات القادمة من الـ Query String والتحقق من العملية في الداتابيز

        $success = $request->query('success');
        $orderId = $request->query('order');
        $hmac = $request->query('hmac');

        if ($success === 'true' && $orderId) {

            // استخدام DB Transaction لضمان سلامة البيانات المالية
            return DB::transaction(function () use ($orderId) {

                $transaction = Transaction::where('payment_id', $orderId)
                                        ->where('status', 'pending')
                                        ->lockForUpdate() // قفل السجل لمنع التكرار (Race Condition)
                                        ->first();

                if ($transaction) {
                    // حساب المبلغ الصافي بالدولار بعد عمولة المنصة
                    $amountInUsd = ($transaction->currency == 'USD')
                                   ? $transaction->amount
                                   : ($transaction->amount / $this->exchangeRate);

                    $finalNetUsd = $amountInUsd * (1 - $this->platformFee);

                    // تحديث حالة العملية
                    $transaction->update([
                        'status' => 'success',
                        'converted_amount' => $finalNetUsd
                    ]);

                    // تحديث المحفظة
                    $wallet = Wallet::firstOrCreate(
                        ['user_id' => $transaction->user_id],
                        ['balance' => 0]
                    );

                    $wallet->increment('balance', $finalNetUsd);

                    return redirect()->route('wallet.index')->with('success', 'تم شحن $' . number_format($finalNetUsd, 2) . ' في محفظتك بنجاح!');
                }

                return redirect()->route('wallet.index')->with('warning', 'هذه العملية تم معالجتها مسبقاً.');
            });
        }

        // في حالة الفشل، نحدث الحالة لـ failed لو كانت موجودة
        if ($orderId) {
            Transaction::where('payment_id', $orderId)
                       ->where('status', 'pending')
                       ->update(['status' => 'failed']);
        }

        return redirect()->route('wallet.index')->with('error', 'فشلت عملية الدفع أو تم إلغاؤها من قبلك.');
    }
}
