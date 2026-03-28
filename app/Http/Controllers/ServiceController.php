<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Order; // أضفنا استدعاء موديل الطلبات
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    /**
     * عرض صفحة إضافة خدمة جديدة
     */
    public function create()
    {
        return view('services.create');
    }

    /**
     * حفظ الخدمة في قاعدة البيانات
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'price' => 'required|numeric|min:1',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('uploads/services'), $imageName);

        Service::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'image' => 'uploads/services/' . $imageName,
        ]);

        return redirect()->route('freelancer.dashboard')->with('success', 'تم نشر خدمتك بنجاح!');
    }

    /**
     * عرض صفحة تأكيد الدفع لخدمة معينة
     */
    public function checkout($id)
    {
        $service = Service::with('user')->findOrFail($id);

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('services.checkout', compact('service'));
    }

    /**
     * ====== الدالة التي كانت تسبب الخطأ (الحل) ======
     * دالة تسليم العمل من قبل المستقل
     */
    public function requestDelivery(Order $order)
    {
        // التأكد أن المستخدم الحالي هو البائع (المستقل) صاحب الطلب
        if (Auth::id() !== $order->seller_id) {
            return back()->with('error', 'عذراً، لا تملك صلاحية تسليم هذا الطلب.');
        }

        // تحديث حالة الطلب إلى "تم التسليم" (delivered)
        $order->update([
            'status' => 'delivered'
        ]);

        return back()->with('success', 'تم إرسال طلب تسليم الخدمة للعميل بنجاح.');
    }
}
