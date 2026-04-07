<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Class ServiceController
 * * هذا الكنترولر مسؤول عن إدارة "الخدمات المصغرة" (Micro-services) التي يقدمها المستقلون،
 * وتشمل عمليات الإنشاء، العرض، وعمليات الدفع وتسليم الطلبات المرتبطة بها.
 */
class ServiceController extends Controller
{
    /**
     * عرض صفحة إضافة خدمة جديدة.
     * * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('services.create');
    }

    /**
     * التحقق من صحة بيانات الخدمة وحفظها في قاعدة البيانات مع تأمين رفع الصورة.
     * * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. تحقق صارم من المدخلات (حل مشكلة أمنية محتملة)
        $validated = $request->validate([
            'title'       => 'required|string|max:255|trim',
            'description' => 'required|string|min:10',
            'price'       => 'required|numeric|min:1',
            'image'       => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            // 2. رفع الصورة باستخدام Storage (أكثر أماناً من الـ public_path المباشر)
            $imagePath = $request->file('image')->store('services', 'public');

            // 3. إنشاء السجل برمجياً مع حماية XSS
            Service::create([
                'user_id'     => Auth::id(),
                'title'       => strip_tags($validated['title']),
                'description' => strip_tags($validated['description']),
                'price'       => $validated['price'],
                'image'       => $imagePath,
            ]);

            return redirect()->route('freelancer.dashboard')->with('success', 'تم نشر خدمتك بنجاح!');

        } catch (\Exception $e) {
            Log::error('Service Store Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء حفظ الخدمة.')->withInput();
        }
    }

    /**
     * عرض صفحة تأكيد الدفع لخدمة معينة (Checkout).
     * * @param  int  $id معرف الخدمة
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function checkout($id)
    {
        // تحميل الخدمة مع بيانات صاحبها (Eager Loading) لتحسين الأداء
        $service = Service::with('user')->findOrFail($id);

        // التحقق من تسجيل الدخول (يمكن نقل هذا لـ Middleware)
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('services.checkout', compact('service'));
    }

    /**
     * معالجة طلب تسليم العمل من قبل المستقل (البائع).
     * * @param  \App\Models\Order  $order كائن الطلب المراد تسليمه
     * @return \Illuminate\Http\RedirectResponse
     */
    public function requestDelivery(Order $order)
    {
        // التأكد أن المستخدم الحالي هو البائع (المستقل) صاحب الطلب الفعلي
        if (Auth::id() !== $order->seller_id) {
            Log::warning("محاولة وصول غير مصرح بها لتسليم طلب رقم: {$order->id} من قبل مستخدم رقم: " . Auth::id());
            return back()->with('error', 'عذراً، لا تملك صلاحية تسليم هذا الطلب.');
        }

        try {
            // تحديث حالة الطلب إلى "تم التسليم"
            $order->update([
                'status' => 'delivered'
            ]);

            return back()->with('success', 'تم إرسال طلب تسليم الخدمة للعميل بنجاح.');

        } catch (\Exception $e) {
            Log::error('Delivery Request Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ تقني أثناء تحديث حالة الطلب.');
        }
    }
}
