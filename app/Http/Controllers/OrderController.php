<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Order;
use App\Models\User;
use App\Models\Project;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * عرض الخدمات التي اشتراها العميل
     */
    public function purchasedServices()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 👇 التعديل الجوهري لحل مشكلة الـ Undefined variable $formattedBalance 👇
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );
        $formattedBalance = number_format($wallet->balance, 2) . ' $';

        $orders = Order::with(['service', 'seller'])
            ->where('buyer_id', $user->id)
            ->latest()
            ->get();

        $myProjects = Project::where('user_id', $user->id)
            ->withCount('proposals')
            ->get();

        // تمرير المتغيرات كاملة وبدون نقصان للـ View لتعمل لوحة التحكم بشكل سليم
        return view('dashboards.Client Dashboard', compact('orders', 'myProjects', 'formattedBalance', 'user'));
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
     * جديد: عرض صفحة تسليم الطلب للفريلانسر (GET)
     */
    public function showDeliverPage($id)
    {
        $order = Order::findOrFail($id);

        // التأكد أن المستخدم هو الفريلانسر (البائع)
        if (Auth::id() !== $order->seller_id) {
            abort(403, 'هذه الصفحة مخصصة لمنفذ الخدمة فقط.');
        }

        // منع التسليم إذا كان الطلب مكتمل أو ملغي
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return redirect()->route('orders.show', $order->id)->with('error', 'لا يمكن تعديل حالة طلب مكتمل أو ملغي.');
        }

        return view('orders.complete', compact('order'));
    }

    /**
     * عرض صفحة التقييم للعميل (GET)
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
     * شراء خدمة (معدلة لدعم الخدمات الجاهزة والعادية)
     */
    public function store(Request $request)
    {
        $service = Service::findOrFail($request->service_id);
        $buyer = Auth::user();

        if (!$service->user) {
            return back()->with('error', 'عفواً، صاحب هذه الخدمة غير متاح حالياً.');
        }

        if ($buyer->id === $service->user_id) {
            return back()->with('error', 'لا يمكنك شراء خدمتك الخاصة!');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $buyer->id],
            ['balance' => 0]
        );

        if ($wallet->balance < $service->price) {
            return back()->with('error', 'رصيدك غير كافٍ لشراء هذه الخدمة.');
        }

        try {
            $order = DB::transaction(function () use ($buyer, $service, $wallet) {
                // 1. خصم الرصيد من المشتري
                $wallet->decrement('balance', $service->price);

                // 2. تحديد الحالة والبيانات بناءً على نوع الخدمة
                $isReady = ($service->type === 'ready');
                $status = $isReady ? 'completed' : 'processing';

                $newOrder = Order::create([
                    'service_id'   => $service->id,
                    'buyer_id'    => $buyer->id,
                    'seller_id'   => $service->user_id,
                    'price'       => $service->price,
                    'status'      => $status,
                    'completed_at' => $isReady ? now() : null,
                ]);

                // 3. إذا كانت خدمة جاهزة، يتم تحويل الرصيد للبائع فوراً
                if ($isReady) {
                    $sellerWallet = Wallet::firstOrCreate(
                        ['user_id' => $service->user_id],
                        ['balance' => 0]
                    );
                    $sellerWallet->increment('balance', $service->price);
                }

                return $newOrder;
            });

            // 4. التوجيه (Redirect) بناءً على نوع الخدمة
            if ($service->type === 'ready') {
                return back()->with([
                    'success' => 'تم شراء الخدمة الجاهزة بنجاح! يمكنك الآن تحميل الملف من زر التحميل.',
                    'ready_file_path' => $service->ready_file
                ]);
            }

            return redirect()->route('messages.chat', ['user' => $order->seller_id])
                             ->with('success', 'تم شراء الخدمة بنجاح! يمكنك الآن التواصل مع المستقل.');

        } catch (\Exception $e) {
            Log::error('Order Store Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء إتمام العملية.');
        }
    }

    /**
     * تنفيذ عملية التسليم الفعلية (POST)
     */
    public function submitDelivery(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if (Auth::id() !== $order->seller_id) {
            return back()->with('error', 'غير مسموح لك بهذا الإجراء.');
        }

        $request->validate([
            'delivery_msg' => 'required|string|min:10|max:2000',
        ]);

        $order->update([
            'status' => 'delivered',
            'delivery_msg' => strip_tags($request->delivery_msg),
        ]);

        return redirect()->route('orders.show', $order->id)->with('success', 'تم تسليم العمل بنجاح بانتظار مراجعة المشتري.');
    }

    /**
     * اعتماد الاستلام والتقييم (POST)
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

                if($order->seller) {
                    $sellerWallet = Wallet::firstOrCreate(
                        ['user_id' => $order->seller_id],
                        ['balance' => 0]
                    );
                    $sellerWallet->increment('balance', $order->price);
                }
            });

            return redirect()->route('orders.show', $order->id)
                             ->with('success', 'تم إتمام العملية بنجاح وتحويل الأرباح للمستقل.');

        } catch (\Exception $e) {
            Log::error('Complete Order Error: ' . $e->getMessage());
            return back()->with('error', 'فشل في إتمام الطلب برمجياً.');
        }
    }

    /**
     * إلغاء الطلب
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
