<?php

namespace App\Http\Controllers;

use App\Notifications\ServicePurchasedNotification;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * Class ServiceController
 * مسؤول عن إدارة "الخدمات المصغرة" وعمليات الدفع بالعملات المختلفة ونظام الإشعارات اللحظي.
 */
class ServiceController extends Controller
{
    /**
     * Helper: جلب سعر صرف الدولار مقابل الجنيه المصري لحظياً.
     */
    private function getUsdToEgpRate()
    {
        try {
            $response = Http::timeout(5)->get("https://open.er-api.com/v6/latest/USD");
            if ($response->successful()) {
                $rates = $response->json()['rates'];
                return $rates['EGP'] ?? 50.0;
            }
        } catch (Exception $e) {
            Log::error("Exchange Rate Fetch Error: " . $e->getMessage());
        }
        return 50.0;
    }

    /**
     * عرض صفحة إضافة خدمة جديدة.
     */
    public function create()
    {
        return view('services.create');
    }

    /**
     * عرض تفاصيل الخدمة (تمت إضافتها لحل المشكلة).
     */
    public function show($id)
    {
        $service = Service::with('user')->findOrFail((int)$id);

        $currentRate = $this->getUsdToEgpRate();
        $priceInUsd = round($service->price / $currentRate, 2);

        return view('services.create', [
            'service' => $service,
            'priceInUsd' => $priceInUsd,
            'rate' => $currentRate
        ]);
    }

    /**
     * حفظ الخدمة في قاعدة البيانات.
     */
    public function store(Request $request)
    {
        $validatedData = $this->validateServiceRequest($request);
        $imagePath = null;
        $readyFilePath = null;
        $disk = 's3';

        try {
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('services/covers/' . date('Y/m'), [
                    'disk' => $disk,
                    'visibility' => 'public'
                ]);
            }

            if ($request->type === 'ready' && $request->hasFile('ready_file')) {
                $readyFilePath = $request->file('ready_file')->store('services/files/' . date('Y/m'), [
                    'disk' => $disk
                ]);
            }

            Service::create([
                'user_id'     => Auth::id(),
                'title'       => strip_tags($validatedData['title']),
                'description' => strip_tags($validatedData['description']),
                'price'       => (float) $validatedData['price'],
                'image'       => $imagePath,
                'type'        => $validatedData['type'],
                'ready_file'  => $readyFilePath,
                'status'      => 'active',
            ]);

            return redirect()->route('freelancer.dashboard')->with('success', 'تم نشر خدمتك الاحترافية بنجاح!');

        } catch (Exception $processingError) {
            Log::error('Service Store Failure: ' . $processingError->getMessage());
            if ($imagePath) { Storage::disk($disk)->delete($imagePath); }
            if ($readyFilePath) { Storage::disk($disk)->delete($readyFilePath); }
            return back()->with('error', 'عذراً، حدث خطأ تقني أثناء حفظ الخدمة.')->withInput();
        }
    }

    /**
     * عرض صفحة تأكيد الدفع (Checkout).
     */
    public function checkout($id)
    {
        $service = Service::with('user')->findOrFail((int)$id);

        $currentRate = $this->getUsdToEgpRate();
        $priceInUsd = round($service->price / $currentRate, 2);

        return view('services.checkout', [
            'service' => $service,
            'priceInUsd' => $priceInUsd,
            'rate' => $currentRate
        ]);
    }

    /**
     * تنفيذ عملية الدفع من المحفظة.
     */
    public function payFromWallet(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'يرجى تسجيل الدخول أولاً لإتمام عملية الشراء.');
        }

        if (Auth::user()->is_profile_completed == 0) {
            return redirect()->route('profile.complete')->with('warning', 'يرجى إكمال بيانات ملفك الشخصي أولاً.');
        }

        $service = Service::findOrFail($id);
        $user = Auth::user();
        $wallet = $user->wallet;

        $currentRate = $this->getUsdToEgpRate();
        $priceInUsd = round($service->price / $currentRate, 2);

        if (!$wallet || $wallet->balance < $priceInUsd) {
            return back()->with('error', "رصيدك غير كافٍ. المطلوب: {$priceInUsd} $");
        }

        try {
            DB::transaction(function () use ($user, $wallet, $service, $priceInUsd, $currentRate) {
                $wallet->decrement('balance', $priceInUsd);

                $order = Order::create([
                    'user_id' => $user->id,
                    'seller_id' => $service->user_id,
                    'service_id' => $service->id,
                    'price' => $service->price,
                    'status' => 'pending',
                    'payment_method' => 'wallet'
                ]);

                Transaction::create([
                    'user_id'     => $user->id,
                    'amount'      => $priceInUsd,
                    'currency'    => 'USD',
                    'type'        => 'pay',
                    'status'      => 'completed',
                    'source_id'   => $order->id,
                    'source_type' => Order::class,
                    'details'     => "شراء خدمة: {$service->title} | سعر الصرف: 1$ = {$currentRate} EGP"
                ]);

                $user->notify(new ServicePurchasedNotification($service, $service->user->name));
            });

            return redirect()->route('client.dashboard')->with('success', 'تمت عملية الشراء بنجاح.');

        } catch (Exception $e) {
            Log::error('Wallet Payment Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء معالجة الدفع.');
        }
    }

    /**
     * معالجة طلب تسليم العمل.
     */
    public function requestDelivery(Order $order)
    {
        if (Auth::id() !== (int)$order->seller_id) {
            return back()->with('error', 'عذراً، لا تملك صلاحية تسليم هذا الطلب.');
        }

        try {
            $order->update(['status' => 'delivered']);
            return back()->with('success', 'تم إرسال طلب تسليم الخدمة للعميل بنجاح.');
        } catch (Exception $updateError) {
            Log::error('Order Status Update Error: ' . $updateError->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحديث حالة الطلب.');
        }
    }

    /**
     * إتمام العميل للطلب وتأكيد الاستلام.
     */
    public function completeOrder(Request $request, Order $order)
    {
        if (Auth::id() !== (int)$order->user_id) {
            return back()->with('error', 'غير مسموح لك بهذا الإجراء.');
        }

        try {
            DB::transaction(function () use ($order) {
                $order->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);

                $this->payoutToFreelancer($order);
            });

            return back()->with('success', 'تم استلام الخدمة بنجاح، وتحويل الأرباح للمستقل.');
        } catch (Exception $e) {
            Log::error('Service Completion Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء إتمام العملية.');
        }
    }

    private function payoutToFreelancer(Order $order)
    {
        $amount = $order->price;

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $order->seller_id],
            ['balance' => 0, 'pending_balance' => 0]
        );

        $wallet->increment('pending_balance', $amount);

        Transaction::create([
            'user_id'         => $order->seller_id,
            'amount'          => $amount,
            'currency'        => 'USD',
            'type'            => 'receive',
            'status'          => 'pending',
            'release_at'      => now()->addDays(7),
            'source_id'       => $order->id,
            'source_type'     => Order::class,
            'details'         => 'أرباح معلقة عن بيع خدمة: ' . strip_tags($order->service->title ?? 'خدمة مصغرة'),
        ]);
    }

    protected function validateServiceRequest(Request $request)
    {
        return $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|min:10|max:5000',
            'price'       => 'required|numeric|min:1|max:99999',
            'image'       => 'required|image|mimes:jpeg,png,jpg,webp|max:3072',
            'type'        => 'required|in:normal,ready',
            'ready_file'  => 'required_if:type,ready|file|max:20480',
        ]);
    }
}
