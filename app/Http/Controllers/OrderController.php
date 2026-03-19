<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * عملية شراء الخدمة (خصم من المشتري وحجز المبلغ)
     */
    public function store(Request $request)
    {
        $service = Service::findOrFail($request->service_id);
        $buyer = Auth::user();

        // 1. التأكد أن المشتري ليس هو نفسه صاحب الخدمة
        if ($buyer->id === $service->user_id) {
            return back()->with('error', 'لا يمكنك شراء خدمتك الخاصة!');
        }

        // 2. التأكد من وجود رصيد كافٍ في المحفظة
        if (!$buyer->wallet || $buyer->wallet->balance < $service->price) {
            return back()->with('error', 'رصيدك غير كافٍ لشراء هذه الخدمة.');
        }

        // 3. البدء في عملية الخصم والإنشاء
        DB::transaction(function () use ($buyer, $service) {
            // خصم المبلغ من محفظة المشتري (حجز المبلغ عند المنصة)
            $buyer->wallet->decrement('balance', $service->price);

            // إنشاء الطلب بحالة 'processing' (قيد التنفيذ)
            Order::create([
                'service_id' => $service->id,
                'buyer_id'   => $buyer->id,
                'seller_id'  => $service->user_id,
                'price'      => $service->price,
                'status'     => 'processing',
            ]);
        });

        return back()->with('success', 'تم شراء الخدمة بنجاح! بدأ المستقل العمل الآن.');
    }

    /**
     * البائع يرفع الشغل ويغير الحالة إلى تم التسليم
     */
    public function deliverOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // التأكد أن الذي يقوم بالتسليم هو البائع صاحب الخدمة
        if (Auth::id() !== $order->seller_id) {
            return back()->with('error', 'غير مسموح لك بهذا الإجراء.');
        }

        $order->update([
            'status' => 'delivered',
            'delivery_msg' => $request->delivery_msg, // اختياري لو أضفته للميجريشن
        ]);

        return back()->with('success', 'تم إرسال العمل للمشتري، بانتظار تأكيده لاستلام الأرباح.');
    }

    /**
     * المشتري يؤكد الاستلام (تحويل الفلوس فعلياً للبائع)
     */
    public function completeOrder($id)
    {
        $order = Order::findOrFail($id);

        // التأكد أن المشتري هو صاحب الطلب
        if (Auth::id() !== $order->buyer_id) {
            return back()->with('error', 'عفواً، لا تملك صلاحية الوصول لهذا الطلب.');
        }

        // التأكد أن الحالة "تم التسليم" قبل التأكيد
        if ($order->status !== 'delivered') {
            return back()->with('error', 'لا يمكنك تأكيد الاستلام قبل أن يقوم البائع بتسليم العمل.');
        }

        DB::transaction(function () use ($order) {
            // 1. تحديث حالة الطلب إلى مكتمل
            $order->update(['status' => 'completed']);

            // 2. إضافة المبلغ لمحفظة البائع (تحويل الأرباح)
            // تأكد من وجود علاقة seller في موديل Order
            $order->seller->wallet->increment('balance', $order->price);
        });

        return back()->with('success', 'تم إتمام العملية بنجاح وتحويل المبلغ لمحفظة المستقل.');
    }

    /**
     * إلغاء الطلب وإرجاع الفلوس (للأدمن أو في حالات خاصة)
     */
    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);

        // يفضل أن تكون للأدمن فقط أو إذا تأخر المستقل عن الـ due_date
        DB::transaction(function () use ($order) {
            $order->update(['status' => 'cancelled']);

            // إرجاع الفلوس للمشتري
            $order->buyer->wallet->increment('balance', $order->price);
        });

        return back()->with('success', 'تم إلغاء الطلب وإعادة المبلغ للمشتري.');
    }
}
