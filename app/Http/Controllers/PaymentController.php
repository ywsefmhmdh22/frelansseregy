<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;

class PaymentController extends Controller
{
    // سعر الصرف الثابت
    protected $exchangeRate = 50.0;

    // نسبة عمولة المنصة (9%)
    protected $platformFee = 0.09;

    public function initiatePayment(Request $request)
    {
        // 1. التحقق من البيانات
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|in:EGP,USD',
            'payment_method' => 'required|in:card,wallet',
            'phone_number' => 'required_if:payment_method,wallet',
        ]);

        $amount = $request->amount;
        $currency = $request->currency;
        $method = $request->payment_method;
        $user = auth()->user();

        // 2. Token التوثيق
        $authResponse = Http::post('https://accept.paymob.com/api/auth/tokens', [
            'api_key' => env('PAYMOB_API_KEY'),
        ]);

        if (!$authResponse->successful()) {
            return back()->with('error', 'خطأ في اتصال بوابة الدفع.');
        }

        $token = $authResponse->json()['token'];

        // 3. تسجيل الطلب
        $orderResponse = Http::withToken($token)->post('https://accept.paymob.com/api/ecommerce/orders', [
            'delivery_needed' => 'false',
            'amount_cents' => $amount * 100,
            'currency' => $currency,
            'items' => [],
        ]);

        if (!$orderResponse->successful()) {
            return back()->with('error', 'فشل تسجيل الطلب في Paymob.');
        }

        $orderId = $orderResponse->json()['id'];

        // 4. تحديد الـ Integration ID
        $integrationId = ($method == 'wallet')
                         ? env('PAYMOB_WALLET_INTEGRATION_ID')
                         : env('PAYMOB_INTEGRATION_ID');

        // 5. مفتاح الدفع
        $paymentKeyResponse = Http::withToken($token)->post('https://accept.paymob.com/api/acceptance/payment_keys', [
            'amount_cents' => $amount * 100,
            'expiration' => 3600,
            'order_id' => $orderId,
            'billing_data' => [
                'first_name' => $user->name ?? 'User',
                'last_name'  => 'Client',
                'email'      => $user->email ?? 'test@example.com',
                'phone_number' => $request->phone_number ?? '01000000000',
                'apartment'  => 'NA', 'floor' => 'NA', 'street' => 'NA', 'building' => 'NA',
                'shipping_method' => 'NA', 'postal_code' => 'NA', 'city' => 'Cairo',
                'country' => 'EG', 'state' => 'Cairo',
            ],
            'currency' => $currency,
            'integration_id' => $integrationId,
        ]);

        if (!$paymentKeyResponse->successful()) {
            return back()->with('error', 'خطأ في إنشاء مفتاح الدفع.');
        }

        $paymentToken = $paymentKeyResponse->json()['token'];

        // 6. تسجيل العملية (حفظ البيانات الأساسية قبل الدفع)
        Transaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'currency' => $currency,
            'type' => 'deposit',
            'payment_id' => $orderId,
            'payment_method' => $method,
            'status' => 'pending',
        ]);

        // 7. التوجيه النهائي
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
    }

    public function callback(Request $request)
    {
        $success = $request->query('success');
        $orderId = $request->query('order');

        if ($success == 'true') {
            $transaction = Transaction::where('payment_id', $orderId)->first();

            if ($transaction && $transaction->status !== 'success') {

                // 1. تحويل المبلغ المكتوب للدولار أولاً (إذا كان بالجنيه)
                $amountInUsd = $transaction->amount;
                if ($transaction->currency == 'EGP') {
                    $amountInUsd = $transaction->amount / $this->exchangeRate;
                }

                // 2. تطبيق خصم عمولة المنصة (9%)
                // الصافي = المبلغ بالدولار * (1 - 0.09)
                $finalNetUsd = $amountInUsd * (1 - $this->platformFee);

                // 3. تحديث حالة العملية وحفظ الصافي النهائي
                $transaction->update([
                    'status' => 'success',
                    'converted_amount' => $finalNetUsd
                ]);

                // 4. إضافة الرصيد الصافي للمحفظة
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $transaction->user_id],
                    ['balance' => 0]
                );

                $wallet->increment('balance', $finalNetUsd);

                return redirect()->route('wallet.index')->with('success', 'تم شحن $' . number_format($finalNetUsd, 2) . ' في محفظتك بنجاح!');
            }
        }

        return redirect()->route('wallet.index')->with('error', 'فشلت عملية الدفع أو تم إلغاؤها.');
    }
}
