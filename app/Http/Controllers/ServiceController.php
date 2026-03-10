<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
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
        // 1. فحص البيانات
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'price' => 'required|numeric|min:1',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // 2. معالجة ورفع الصورة
        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('uploads/services'), $imageName);

        // 3. الحفظ في الداتابيز
        Service::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'image' => 'uploads/services/' . $imageName,
        ]);

        // التوجيه إلى الداشبورد مع رسالة نجاح
        return redirect()->route('freelancer.dashboard')->with('success', 'تم نشر خدمتك بنجاح!');
    }

    /**
     * دالة الـ Checkout (اللي كانت ناقصة ومسببة الخطأ)
     * عرض صفحة تأكيد الدفع لخدمة معينة
     */
    public function checkout($id)
    {
        // جلب بيانات الخدمة مع بيانات صاحب الخدمة (المستقل)
        $service = Service::with('user')->findOrFail($id);

        // التأكد من أن المستخدم مسجل دخول (زيادة أمان)
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // عرض صفحة الدفع (resources/views/services/checkout.blade.php)
        return view('services.checkout', compact('service'));
    }
}
