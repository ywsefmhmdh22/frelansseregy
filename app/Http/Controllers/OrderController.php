<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Order;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * عرض الخدمات التي اشتراها العميل
     */
    public function purchasedServices()
    {
        $user = Auth::user();

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
     */
    public function store(Request $request)
    {
        $service = Service::findOrFail($request->service_id);
        $buyer = Auth::user();

        if ($buyer->id === $service->user_id) {
            return back()->with('error', 'لا يمكنك شراء خدمتك الخاصة!');
        }

        if (!$buyer->wallet || $buyer->wallet->balance < $service->price) {
            return back()->with('error', 'رصيدك غير كافٍ لشراء هذه الخدمة.');
        }

        $order = DB::transaction(function () use ($buyer, $service) {
            $buyer->wallet->decrement('balance', $service->price);

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
    }

    /**
     * الوظيفة المسؤولة عن تسليم الطلب (المسمى المطلوب لحل الـ Route Error)
     */
    public function submitDelivery(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // التأكد أن الذي يقوم بالتسليم هو صاحب الخدمة (البائع)
        if (Auth::id() !== $order->seller_id) {
            return back()->with('error', 'غير مسموح لك بهذا الإجراء.');
        }

        // تحديث حالة الطلب إلى "تم التسليم"
        $order->update([
            'status' => 'delivered',
            'delivery_msg' => $request->delivery_msg ?? 'تم إنجاز العمل المطلوب وتسليمه.',
        ]);

        return back()->with('success', 'تم تسليم العمل بنجاح بانتظار مراجعة المشتري.');
    }

    /**
     * وظيفة إضافية لدعم المسمى القديم (snake_case) لتجنب أي تعارض
     */
    public function submit_delivery(Request $request, $id)
    {
        return $this->submitDelivery($request, $id);
    }

    /**
     * البائع يرفع الشغل
     */
    public function deliverOrder(Request $request, $id)
    {
        return $this->submitDelivery($request, $id);
    }

    /**
     * المشتري يؤكد الاستلام والتقييم (تحويل الفلوس للبائع)
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

        DB::transaction(function () use ($order, $request) {
            $order->update([
                'status' => 'completed',
                'rating' => $request->rating,
                'comment' => $request->comment,
                'completed_at' => now(),
            ]);

            if($order->seller && $order->seller->wallet) {
                $order->seller->wallet->increment('balance', $order->price);
            }
        });

        return redirect()->route('orders.show', $order->id)
                         ->with('success', 'تم إتمام العملية بنجاح وتحويل الأرباح.');
    }

    /**
     * إلغاء الطلب وإرجاع الفلوس
     */
    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'cancelled']);
            if($order->buyer && $order->buyer->wallet) {
                $order->buyer->wallet->increment('balance', $order->price);
            }
        });

        return back()->with('success', 'تم إلغاء الطلب وإعادة المبلغ.');
    }
}
