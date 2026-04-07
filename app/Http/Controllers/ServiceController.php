<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Class ServiceController
 * مسؤول عن إدارة "الخدمات المصغرة" (Micro-services) التي يقدمها المستقلون.
 * تم تحصين الكود وتطبيق مبدأ DRY (Don't Repeat Yourself) لإزالة أي Code Smell.
 */
class ServiceController extends Controller
{
    /**
     * عرض صفحة إضافة خدمة جديدة.
     */
    public function create()
    {
        return view('services.create');
    }

    /**
     * حفظ الخدمة في قاعدة البيانات (Refactored لإزالة الـ Code Smell).
     * يعتمد على دوال المساعدة لضمان نظافة الكود وتسهيل اختباره.
     */
    public function store(Request $request)
    {
        // 1. استخدام دالة التحقق الموحدة (Single Responsibility)
        $validatedData = $this->validateServiceRequest($request);

        try {
            // 2. استخدام دالة رفع الصور الموحدة
            $imagePath = $this->uploadServiceImage($request);

            // 3. إنشاء السجل باستخدام البيانات المعالجة وتطهير النصوص (Sanitization)
            Service::create([
                'user_id'     => Auth::id(),
                'title'       => strip_tags($validatedData['title']),
                'description' => strip_tags($validatedData['description']),
                'price'       => (float) $validatedData['price'],
                'image'       => $imagePath,
            ]);

            return redirect()->route('freelancer.dashboard')
                             ->with('success', 'تم نشر خدمتك بنجاح!');

        } catch (Exception $processingError) {
            // تسجيل الخطأ التقني مع سياق كامل
            Log::error('Service Store Failure: ' . $processingError->getMessage());

            // تنظيف السيرفر: حذف الصورة التي رُفعت إذا فشلت عملية حفظ الداتابيز
            if (!empty($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return back()->with('error', 'عذراً، حدث خطأ تقني أثناء حفظ الخدمة.')
                         ->withInput();
        }
    }

    /**
     * عرض صفحة تأكيد الدفع (Checkout).
     */
    public function checkout($id)
    {
        // استخدام Eager Loading لتحسين الأداء
        $serviceInstance = Service::with('user')->findOrFail((int)$id);

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('services.checkout', ['service' => $serviceInstance]);
    }

    /**
     * معالجة طلب تسليم العمل وتغيير الحالة.
     */
    public function requestDelivery(Order $order)
    {
        // تحقق أمني من الصلاحية (Authorization Check)
        if (Auth::id() !== (int)$order->seller_id) {
            Log::warning("Unauthorized Delivery Attempt by User ID " . Auth::id() . " for Order ID: {$order->id}");
            return back()->with('error', 'عذراً، لا تملك صلاحية تسليم هذا الطلب.');
        }

        try {
            // تحديث الحالة بشكل آمن
            $order->update(['status' => 'delivered']);

            return back()->with('success', 'تم إرسال طلب تسليم الخدمة للعميل بنجاح.');
        } catch (Exception $updateError) {
            Log::error('Order Status Update Error: ' . $updateError->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحديث حالة الطلب.');
        }
    }

    // =========================================================================
    // Private/Protected Helpers (The Anti-Code Smell Architecture)
    // =========================================================================

    /**
     * توحيد منطق التحقق من البيانات (Centralized Validation).
     * @param Request $request
     * @return array
     */
    protected function validateServiceRequest(Request $request)
    {
        return $request->validate([
            'title'       => 'required|string|max:255|trim',
            'description' => 'required|string|min:10|max:5000',
            'price'       => 'required|numeric|min:1|max:99999',
            'image'       => 'required|image|mimes:jpeg,png,jpg,webp|max:3072', // زيادة الحجم لرفع الجودة
        ]);
    }

    /**
     * توحيد منطق معالجة ورفع الصور لضمان عدم التكرار.
     * @param Request $request
     * @return string|null
     */
    protected function uploadServiceImage(Request $request)
    {
        if ($request->hasFile('image')) {
            // تخزين في مجلد منظم حسب التاريخ لسهولة الأرشفة
            return $request->file('image')->store('services/' . date('Y/m'), 'public');
        }
        return null;
    }
}
