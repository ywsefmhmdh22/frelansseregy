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
use App\Models\Service;
use App\Models\Order;
use Exception;

class PaymentController extends Controller
{
    // نسبة عمولة المنصة (9%)
    protected $platformFee = 0.09;

    /**
     * عرض صفحة المحفظة الرئيسية
     */
    public function index()
    {
        $user = auth()->user();

        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0, 'currency' => 'USD']);

        $transactions = Transaction::where('user_id', $user->id)
            ->whereIn('status', ['completed', 'failed', 'canceled'])
            ->latest()
            ->paginate(10);

        return view('wallet.index', compact('wallet', 'transactions'));
    }

    /**
     * جلب سعر الصرف اللحظي من الـ API المباشر لضمان دقة العمليات ومطابقتها التامة للسيستم (مع تخزين مؤقت ساعة)
     */
    protected function getExchangeRate()
    {
        return Cache::remember('usd_to_egp_rate', 3600, function () {
            try {
                // الاتصال بـ API المباشر المستقر والمجاني لضمان الحصول على سعر الصرف الدقيق
                $response = Http::timeout(5)->get("https://open.er-api.com/v6/latest/USD");

                if ($response->successful()) {
                    $rates = $response->json()['rates'];
                    return (float) ($rates['EGP'] ?? config('services.paymob.exchange_rate', 50.0));
                }
            } catch (Exception $e) {
                Log::error('Exchange Rate API Error (PaymentController): ' . $e->getMessage());
            }

            // القيمة الاحتياطية المأخوذة من ملف الإعدادات في حال فشل السيرفر الخارجي بالكامل
            return (float) config('services.paymob.exchange_rate', 50.0);
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
            'service_id'     => 'nullable|exists:services,id',
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

            // 4. تسجيل العملية بنظام الانتظار
            Transaction::create([
                'user_id'         => $user->id,
                'amount'          => (float) $validated['amount'],
                'currency'        => $validated['currency'],
                'type'            => 'deposit',
                'payment_id'      => (string) $orderId,
                'payment_method'  => $validated['payment_method'],
                'status'          => 'initialized',
                'source_id'       => $request->service_id,
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

    public function callback(Request $request)
    {
        if (!$this->verifySecureSignature($request)) {
            return redirect()->route('wallet.index')->with('error', 'فشل التحقق الأمني.');
        }

        $success = $request->query('success');
        $orderId = $request->query('order');

        if ($success === 'true') {
            // مسح كاش لوحة التحكم لإجبار الشاشة على قراءة التحديث الجديد فوراً
            if (auth()->check()) {
                Cache::forget("client_stats_summary_" . auth()->id());
            }
            return redirect()->route('client.dashboard')->with('success', 'تم استلام طلبك بنجاح، وتحديث محفظتك بالدولار حالاً!');
        }

        Transaction::where('payment_id', $orderId)
            ->where('status', 'initialized')
            ->update(['status' => 'canceled']);

        return redirect()->route('client.dashboard')->with('error', 'تم إلغاء عملية الدفع أو فشلت.');
    }

    /**
     * الـ Webhook الذكي والمصحح بالكامل للحسابات الدولية بالدولار
     */
    public function processedCallback(Request $request)
    {
        if (!$this->verifyWebhookSignature($request)) {
            Log::critical('SECURITY ALERT: Invalid Webhook HMAC');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->all();
        $obj = $data['obj'];
        $orderId = (string) $obj['order']['id'];
        $success = $obj['success'];

        // جلب القيمة الفعلية الصافية المبعوتة من سيرفر باي موب بالسنات (Amount cents الحقيقي المدفوع)
        $paymobAmountCents = (float) $obj['amount_cents'];
        $paymobAmountEgp = $paymobAmountCents / 100; // تحويلها لجنيه حقيقي

        return DB::transaction(function () use ($orderId, $success, $obj, $paymobAmountEgp) {
            $transaction = Transaction::where('payment_id', $orderId)
                ->where('status', 'initialized')
                ->lockForUpdate()
                ->first();

            if ($transaction && $success === true) {
                $exchangeRate = $this->getExchangeRate();

                // الحسبة الذهبية: تحويل المبلغ الفعلي المستلم لـ USD فوراً لمنع التداخل
                $amountInUsd = $paymobAmountEgp / $exchangeRate;

                // خصم الـ 9% الخاصة بعمولة منصة وركلي داي من القيمة الصافية بالدولار
                $finalNetUsd = $amountInUsd * (1 - $this->platformFee);

                // 1. تحديث حالة المعاملة في الداتابيز
                $transaction->update([
                    'status' => 'completed',
                    'converted_amount' => $finalNetUsd,
                    'details' => 'Paymob Live Transaction ID: ' . $obj['id']
                ]);

                // 2. تحديث محفظة المشتري وإجبار العملة تكون USD لحل مشكلة الـ ج.م
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $transaction->user_id],
                    ['balance' => 0, 'currency' => 'USD']
                );

                // تأكيد العملة دولار وزيادة الرصيد بالكسور الدقيقة للدولار
                $wallet->currency = 'USD';
                $wallet->increment('balance', $finalNetUsd);

                // 3. منطق شراء الخدمة
                if ($transaction->source_id) {
                    $service = Service::find($transaction->source_id);
                    if ($service) {
                        $isReady = ($service->type === 'ready');
                        $orderStatus = $isReady ? 'completed' : 'pending';

                        $order = Order::create([
                            'user_id'      => $transaction->user_id,
                            'seller_id'    => $service->user_id,
                            'service_id'   => $service->id,
                            'price'        => $service->price,
                            'status'       => $orderStatus,
                            'completed_at' => $isReady ? now() : null,
                        ]);

                        if ($isReady) {
                            $this->payoutToFreelancerFromPayment($order);
                        }
                    }
                }

                // تدمير وتطهير الكاش الخاص بالمستخدم فوراً لكي تظهر الـ السنتات على الشاشة لايف دون انتظار
                Cache::forget("client_stats_summary_{$transaction->user_id}");

                Log::info("Paymob Live Webhook Success: Wallet updated for User #{$transaction->user_id} with {$finalNetUsd} USD");
                return response()->json(['status' => 'success']);
            }

            if ($transaction && $success !== true) {
                $transaction->update(['status' => 'failed']);
                Cache::forget("client_stats_summary_{$transaction->user_id}");
            }

            return response()->json(['status' => 'processed']);
        });
    }

    /**
     * تحويل الأرباح للمستقل عند الشراء الفوري
     */
    private function payoutToFreelancerFromPayment(Order $order)
    {
        $sellerWallet = Wallet::firstOrCreate(
            ['user_id' => $order->seller_id],
            ['balance' => 0, 'pending_balance' => 0, 'currency' => 'USD']
        );

        $sellerWallet->increment('pending_balance', $order->price);

        Transaction::create([
            'user_id'        => $order->seller_id,
            'amount'         => $order->price,
            'currency'       => 'USD',
            'type'           => 'receive',
            'status'         => 'pending',
            'release_at'     => now()->addDays(7),
            'source_id'      => $order->id,
            'source_type'    => Order::class,
            'details'        => 'أرباح بيع خدمة جاهزة: ' . strip_tags($order->service->title ?? 'خدمة'),
        ]);
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
