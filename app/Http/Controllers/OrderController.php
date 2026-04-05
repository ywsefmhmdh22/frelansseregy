<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Order;
use App\Models\User;
use App\Models\Project;
use App\Models\Wallet; // إضافة الموديل لضمان التعامل السليم
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * عرض الخدمات التي اشتراها العميل
     * تم حل مشكلة الـ Performance والـ Null User
     */
    public function purchasedServices()
    {
        $user = Auth::user();

        // حماية: إذا لم يكن هناك مستخدم مسجل دخول (رغم وجود Middleware)
        if (!$user) {
            return redirect()->route('login');
        }

        // جلب الطلبات مع حماية الـ Relations
        $orders = Order::with(['service', 'seller'])
            ->where('buyer_id', $user->id)
            ->latest()
            ->get();

        $myProjects = Project::where('user_id', $user->id)
            ->withCount('proposals')
            ->get();

        return view('dashboards.Client Dashboard', compact('orders', 'myProjects'));
    }

    /**
     * عرض تفاصيل طلب معين
     */
    public function show($id)
    {
        // findOrFail بتحل جزء كبير من الـ Bug لو الـ ID مش موجود
        $order = Order::with(['service', 'seller', 'buyer'])->findOrFail($id);

        if (Auth::id() !== $order->buyer_id && Auth::id() !== $order->seller_id) {
            abort(403, 'غير مسموح لك بعرض تفاصيل هذا الطلب.');
        }

        return view('orders.show', compact('order'));
    }

    /**
     * عرض صفحة التقييم المنفصلة (GET)
     */
    public function showCompletePage($id)
    {
        $order = Order::where('id', $id)
                    ->where('buyer_id', Auth::id())
                    ->firstOrFail();

        if ($order->status !== 'delivered') {
            return redirect()->route('orders.show', $order->id)
                             ->with('error', 'لا يمكنك تقييم طلب لم يتم تسليمه بعد.');
        }

        return view('orders.complete', compact('order'));
    }

    /**
     * عملية شراء الخدمة
     * تم إضافة حل الـ BUG الخاص بالمحفظة والمستخدم
     */
    public function store(Request $request)
    {
        $service = Service::findOrFail($request->service_id);
        $buyer = Auth::user();

        // 1. التأكد أن البائع لسه موجود في الداتابيز (حل الـ BUG)
        if (!$service->user) {
            return back()->with('error', 'عفواً، صاحب هذه الخدمة غير متاح حالياً.');
        }

        if ($buyer->id === $service->user_id) {
            return back()->with('error', 'لا يمكنك شراء خدمتك الخاصة!');
        }

        // 2. التأكد من وجود محفظة للمشتري أو إنشائها (حماية من الـ Exception)
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $buyer->id],
            ['balance' => 0]
        );

        if ($wallet->balance < $service->price) {
            return back()->with('error', 'رصيدك غير كافٍ لشراء هذه الخدمة.');
        }

        try {
            $order = DB::transaction(function () use ($buyer, $service, $wallet) {
                // خصم الرصيد
                $wallet->decrement('balance', $service->price);

                return Order::create([
                    'service_id' => $service->id,
                    'buyer_id'   => $buyer->id,
                    'seller_id'  => $service->user_id,
                    'price'      => $service->price,
                    'status'     => 'processing',
                ]);
            });

            return redirect()->route('messages.chat', ['user' => $order->seller_id])
                             ->with('success', 'تم شراء الخدمة بنجاح! يمكنك الآن التواصل مع المستقل.');

        } catch (\Exception $e) {
            Log::error('Order Store Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء إتمام العملية، يرجى المحاولة لاحقاً.');
        }
    }

    /**
     * الوظيفة المسؤولة عن تسليم الطلب
     */
    public function submitDelivery(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if (Auth::id() !== $order->seller_id) {
            return back()->with('error', 'غير مسموح لك بهذا الإجراء.');
        }

        $order->update([
            'status' => 'delivered',
            'delivery_msg' => strip_tags($request->delivery_msg ?? 'تم إنجاز العمل المطلوب وتسليمه.'),
        ]);

        return back()->with('success', 'تم تسليم العمل بنجاح بانتظار مراجعة المشتري.');
    }

    public function submit_delivery(Request $request, $id)
    {
        return $this->submitDelivery($request, $id);
    }

    public function deliverOrder(Request $request, $id)
    {
        return $this->submitDelivery($request, $id);
    }

    /**
     * المشتري يؤكد الاستلام والتقييم
     */
    public function completeAndRate(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'rating'   => 'required|integer|min:1|max:5',
            'comment'  => 'nullable|string|max:500',
        ]);

        $order = Order::findOrFail($request->order_id);

        if (Auth::id() !== $order->buyer_id) {
            return back()->with('error', 'عفواً، لا تملك صلاحية الوصول لهذا الطلب.');
        }

        if ($order->status !== 'delivered') {
            return back()->with('error', 'لا يمكنك تأكيد الاستلام قبل تسليم العمل.');
        }

        try {
            DB::transaction(function () use ($order, $request) {
                $order->update([
                    'status' => 'completed',
                    'rating' => $request->rating,
                    'comment' => strip_tags($request->comment),
                    'completed_at' => now(),
                ]);

                // حماية: التأكد من وجود البائع ومحفظته قبل التحويل
                if($order->seller) {
                    $sellerWallet = Wallet::firstOrCreate(
                        ['user_id' => $order->seller_id],
                        ['balance' => 0]
                    );
                    $sellerWallet->increment('balance', $order->price);
                }
            });

            return redirect()->route('orders.show', $order->id)
                             ->with('success', 'تم إتمام العملية بنجاح وتحويل الأرباح.');
        } catch (\Exception $e) {
            Log::error('Complete Order Error: ' . $e->getMessage());
            return back()->with('error', 'فشل في إتمام الطلب برمجياً.');
        }
    }

    /**
     * إلغاء الطلب وإرجاع الفلوس
     */
    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status === 'completed' || $order->status === 'cancelled') {
            return back()->with('error', 'لا يمكن إلغاء طلب مكتمل أو ملغي بالفعل.');
        }

        try {
            DB::transaction(function () use ($order) {
                $order->update(['status' => 'cancelled']);

                // التأكد من إرجاع الفلوس لمحفظة المشتري
                $buyerWallet = Wallet::firstOrCreate(
                    ['user_id' => $order->buyer_id],
                    ['balance' => 0]
                );
                $buyerWallet->increment('balance', $order->price);
            });

            return back()->with('success', 'تم إلغاء الطلب وإعادة المبلغ لمحفظتك.');
        } catch (\Exception $e) {
            Log::error('Cancel Order Error: ' . $e->getMessage());
            return back()->with('error', 'فشل إلغاء الطلب.');
        }
    }
}
