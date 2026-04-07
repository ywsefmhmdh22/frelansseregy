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
 * تم تحديثه لإزالة التكرار البرمجي (Code Smell) وتحسين قابلية الصيانة.
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
     */
    public function store(Request $request)
    {
        // 1. استخدام دالة التحقق الموحدة
        $validated = $this->validateServiceRequest($request);

        try {
            // 2. استخدام دالة رفع الصور الموحدة
            $imagePath = $this->uploadServiceImage($request);

            // 3. إنشاء السجل باستخدام البيانات المعالجة
            Service::create([
                'user_id'     => Auth::id(),
                'title'       => strip_tags($validated['title']),
                'description' => strip_tags($validated['description']),
                'price'       => $validated['price'],
                'image'       => $imagePath,
            ]);

            return redirect()->route('freelancer.dashboard')->with('success', 'تم نشر خدمتك بنجاح!');

        } catch (Exception $e) {
            Log::error('Service Store Error: ' . $e->getMessage());

            // تنظيف السيرفر في حالة فشل قاعدة البيانات (Good Practice)
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return back()->with('error', 'حدث خطأ أثناء حفظ الخدمة.')->withInput();
        }
    }

    /**
     * عرض صفحة تأكيد الدفع (Checkout).
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
     * معالجة طلب تسليم العمل.
     */
    public function requestDelivery(Order $order)
    {
        if (Auth::id() !== $order->seller_id) {
            Log::warning("Unauthorized delivery attempt for Order ID: {$order->id}");
            return back()->with('error', 'عذراً، لا تملك صلاحية تسليم هذا الطلب.');
        }

        try {
            $order->update(['status' => 'delivered']);
            return back()->with('success', 'تم إرسال طلب تسليم الخدمة للعميل بنجاح.');
        } catch (Exception $e) {
            Log::error('Delivery Request Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ تقني أثناء تحديث حالة الطلب.');
        }
    }

    // --- Private/Protected Helpers لإزالة التكرار (The Anti-Code Smell Solution) ---

    /**
     * توحيد منطق التحقق من البيانات (Validation).
     */
    protected function validateServiceRequest(Request $request)
    {
        return $request->validate([
            'title'       => 'required|string|max:255|trim',
            'description' => 'required|string|min:10',
            'price'       => 'required|numeric|min:1',
            'image'       => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
    }

    /**
     * توحيد منطق معالجة ورفع الصور.
     */
    protected function uploadServiceImage(Request $request)
    {
        if ($request->hasFile('image')) {
            return $request->file('image')->store('services', 'public');
        }
        return null;
    }
}
