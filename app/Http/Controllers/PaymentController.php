<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use App\Models\Wallet;

class PaymentController extends Controller
{
    public function initiatePayment(Request $request)
    {
        // 1. التحقق من البيانات
        $request->validate([
            'amount' => 'required|numeric|min:5',
            'payment_method' => 'required|in:card,wallet',
            'phone_number' => 'required_if:payment_method,wallet',
        ]);

        $amount = $request->amount;
        $method = $request->payment_method;
        $user = auth()->user();

        // 2. Token التوثيق
        $authResponse = Http::post('https://accept.paymob.com/api/auth/tokens', [
            'api_key' => env('PAYMOB_API_KEY'),
        ]);

        if (!$authResponse->successful()) {
            return "خطأ في التوثيق: " . $authResponse->body();
        }

        $token = $authResponse->json()['token'];

        // 3. تسجيل الطلب
        $orderResponse = Http::withToken($token)->post('https://accept.paymob.com/api/ecommerce/orders', [
            'delivery_needed' => 'false',
            'amount_cents' => $amount * 100,
            'currency' => 'EGP',
            'items' => [],
        ]);

        if (!$orderResponse->successful()) {
            return "خطأ في تسجيل الطلب: " . $orderResponse->body();
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
                'apartment'  => 'NA',
                'floor'      => 'NA',
                'street'     => 'NA',
                'building'   => 'NA',
                'shipping_method' => 'NA',
                'postal_code' => 'NA',
                'city'       => 'Cairo',
                'country'    => 'EG',
                'state'      => 'Cairo',
            ],
            'currency' => 'EGP',
            'integration_id' => $integrationId,
        ]);

        if (!$paymentKeyResponse->successful()) {
            return "خطأ في إنشاء مفتاح الدفع: " . $paymentKeyResponse->body();
        }

        $paymentToken = $paymentKeyResponse->json()['token'];

        // 6. تسجيل العملية
        Transaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => 'deposit',
            'payment_id' => $orderId,
            'payment_method' => $method,
            'status' => 'pending',
        ]);

        // 7. التوجيه النهائي (التعامل مع خطأ الـ Redirect URL)
        if ($method == 'wallet') {
            $walletPayResponse = Http::post('https://accept.paymob.com/api/acceptance/payments/pay', [
                'source' => [
                    'identifier' => $request->phone_number,
                    'subtype' => 'WALLET'
                ],
                'payment_token' => $paymentToken
            ]);

            $walletData = $walletPayResponse->json();

            // تأكد من نجاح الطلب ووجود الرابط قبل التوجيه
            if ($walletPayResponse->successful() && !empty($walletData['redirect_url'])) {
                return redirect()->away($walletData['redirect_url']);
            }

            // لو الرابط فاضي، اطبع الرد بالكامل عشان نعرف Paymob معترضة على إيه
            return "خطأ: Paymob لم ترسل رابط تحويل. الرد كان: " . json_encode($walletData);

        } else {
            $iframeId = env('PAYMOB_IFRAME_ID');
            $url = "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$paymentToken}";
            return redirect()->away($url);
        }
    }

    public function callback(Request $request)
    {
        $success = $request->query('success');
        $orderId = $request->query('order');

        if ($success == 'true') {
            $transaction = Transaction::where('payment_id', $orderId)->first();
            if ($transaction && $transaction->status !== 'success') {
                $transaction->update(['status' => 'success']);
                $wallet = Wallet::where('user_id', $transaction->user_id)->first();
                if ($wallet) {
                    $wallet->increment('balance', $transaction->amount);
                }
            }
            return redirect()->route('wallet.index')->with('success', 'تم شحن رصيدك بنجاح!');
        }
        return redirect()->route('wallet.index')->with('error', 'فشلت عملية الدفع.');
    }
}
