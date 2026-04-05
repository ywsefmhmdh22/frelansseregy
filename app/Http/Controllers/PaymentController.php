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
        // 1. التحقق من البيانات المدخلة بقواعد صارمة
        $request->validate([
            'amount' => 'required|numeric|min:1|max:100000', // حد أقصى للحماية
            'currency' => 'required|in:EGP,USD',
            'payment_method' => 'required|in:card,wallet',
            'phone_number' => 'required_if:payment_method,wallet|nullable|regex:/^01[0125][0-9]{8}$/',
        ]);

        try {
            $originalAmount = (float) $request->amount;
            $originalCurrency = $request->currency;
            $method = $request->payment_method;
            $user = auth()->user();

            // تحويل العملات بطريقة آمنة
            $paymobAmount = ($originalCurrency === 'USD') ? ($originalAmount * $this->exchangeRate) : $originalAmount;
            $paymobCurrency = 'EGP';

            // 2. الحصول على Token التوثيق
            $authResponse = Http::post('https://accept.paymob.com/api/auth/tokens', [
                'api_key' => env('PAYMOB_API_KEY'),
            ]);

            if (!$authResponse->successful()) {
                throw new Exception('Authentication failed with payment gateway.');
            }

            $token = $authResponse->json()['token'];

            // 3. تسجيل الطلب (Order Creation)
            $orderResponse = Http::withToken($token)->post('https://accept.paymob.com/api/ecommerce/orders', [
                'delivery_needed' => 'false',
                'amount_cents' => (int) round($paymobAmount * 100),
                'currency' => $paymobCurrency,
                'items' => [],
            ]);

            if (!$orderResponse->successful()) {
                throw new Exception('Order registration failed in Paymob.');
            }

            $orderId = $orderResponse->json()['id'];

            // 4. تحديد الـ Integration ID بناءً على الطريقة
            $integrationId = ($method == 'wallet')
                ? env('PAYMOB_WALLET_INTEGRATION_ID')
                : env('PAYMOB_INTEGRATION_ID');

            // 5. إنشاء مفتاح الدفع (Payment Key)
            $paymentKeyResponse = Http::withToken($token)->post('https://accept.paymob.com/api/acceptance/payment_keys', [
                'amount_cents' => (int) round($paymobAmount * 100),
                'expiration' => 3600,
                'order_id' => $orderId,
                'billing_data' => [
                    'first_name' => str_replace(['<', '>'], '', $user->name ?? 'User'), // تنظيف بسيط للاسم
                    'last_name'  => 'Client',
                    'email'      => $user->email ?? 'no-email@example.com',
                    'phone_number' => $request->phone_number ?? '01000000000',
                    'apartment'   => 'NA', 'floor' => 'NA', 'street' => 'NA', 'building' => 'NA',
                    'shipping_method' => 'NA', 'postal_code' => 'NA', 'city' => 'Cairo',
                    'country' => 'EG', 'state' => 'Cairo',
                ],
                'currency' => $paymobCurrency,
                'integration_id' => $integrationId,
            ]);

            if (!$paymentKeyResponse->successful()) {
                throw new Exception('Payment key creation failed.');
            }

            $paymentToken = $paymentKeyResponse->json()['token'];

            // 6. تسجيل العملية في الداتابيز
            Transaction::create([
                'user_id' => $user->id,
                'amount' => $originalAmount,
                'currency' => $originalCurrency,
                'type' => 'deposit',
                'payment_id' => (string) $orderId,
                'payment_method' => $method,
                'status' => 'pending',
            ]);

            // 7. التوجيه لبوابة الدفع بطريقة آمنة
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
                return back()->with('error', 'Wallet payment initiation failed.');
            } else {
                $iframeId = (int) env('PAYMOB_IFRAME_ID');
                // بناء الرابط يدويًا لضمان عدم حقن أي شيء
                $url = "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token=" . urlencode($paymentToken);
                return redirect()->away($url);
            }

        } catch (Exception $e) {
            Log::error('Payment Error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred during processing.');
        }
    }

    /**
     * الـ Callback: هنا قمنا بإضافة نظام الـ HMAC للحماية القصوى
     */
    public function callback(Request $request)
    {
        // 1. جلب البيانات الأساسية وتنظيفها
        $success = $request->query('success');
        $orderId = (string) $request->query('order');
        $hmac = $request->query('hmac');

        // 2. التحقق من HMAC لضمان أن الطلب قادم من Paymob حصراً (Security 1000%)
        if (!$this->isHmacValid($request)) {
            Log::warning('Unauthorized payment callback attempt detected!', ['data' => $request->all()]);
            return redirect()->route('wallet.index')->with('error', 'Security check failed: Invalid signature.');
        }

        if ($success === 'true' && $orderId) {
            try {
                return DB::transaction(function () use ($orderId) {
                    $transaction = Transaction::where('payment_id', $orderId)
                        ->where('status', 'pending')
                        ->lockForUpdate()
                        ->first();

                    if ($transaction) {
                        $amountInUsd = ($transaction->currency == 'USD')
                            ? $transaction->amount
                            : ($transaction->amount / $this->exchangeRate);

                        $finalNetUsd = $amountInUsd * (1 - $this->platformFee);

                        $transaction->update([
                            'status' => 'success',
                            'converted_amount' => $finalNetUsd
                        ]);

                        $wallet = Wallet::firstOrCreate(
                            ['user_id' => $transaction->user_id],
                            ['balance' => 0]
                        );

                        $wallet->increment('balance', $finalNetUsd);

                        return redirect()->route('wallet.index')->with('success', 'Amount deposited successfully!');
                    }

                    return redirect()->route('wallet.index')->with('warning', 'Already processed.');
                });
            } catch (Exception $e) {
                Log::error('Callback DB Error: ' . $e->getMessage());
            }
        }

        // تحديث الحالة للفشل في حال لم تنجح العملية
        if ($orderId) {
            Transaction::where('payment_id', $orderId)
                ->where('status', 'pending')
                ->update(['status' => 'failed']);
        }

        return redirect()->route('wallet.index')->with('error', 'Payment failed or canceled.');
    }

    /**
     * دالة التحقق من توقيع Paymob (HMAC)
     */
    protected function isHmacValid(Request $request): bool
    {
        $hmac = $request->query('hmac');
        $data = $request->only([
            'amount_cents', 'created_at', 'currency', 'error_occured',
            'has_parent_transaction', 'id', 'integration_id', 'is_3d_secure',
            'is_auth', 'is_capture', 'is_refunded', 'is_standalone_payment',
            'is_voided', 'order', 'owner', 'pending', 'source_data_pan',
            'source_data_sub_type', 'source_data_type', 'success'
        ]);

        // ترتيب البيانات أبجديًا حسب المفتاح كما تتطلب Paymob
        ksort($data);
        $concatenatedString = implode('', $data);
        $secret = env('PAYMOB_HMAC_SECRET'); // لازم تضيف HMAC Secret في ملف .env

        $calculatedHmac = hash_hmac('sha512', $concatenatedString, $secret);

        return hash_equals($calculatedHmac, (string) $hmac);
    }
}
