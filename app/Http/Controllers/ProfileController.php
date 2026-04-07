<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * عرض صفحة الإعدادات
     */
    public function settings()
    {
        $user = Auth::user();
        return view('profile.settings', compact('user'));
    }

    /**
     * تحديث البيانات الشخصية (الاسم، الإيميل، النبذة)
     * تم حل مشكلة التحقق المفقود وإضافة حماية XSS
     */
    public function updatePersonal(Request $request)
    {
        $user = Auth::user();

        // 1. التحقق الصارم من المدخلات لمنع التلاعب
        $validated = $request->validate([
            'name'  => 'required|string|max:255|trim',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'bio'   => 'nullable|string|max:1000',
        ]);

        // 2. التحديث مع تنظيف النصوص من أي أكواد Script خبيثة
        $user->update([
            'name'  => strip_tags($validated['name']),
            'email' => $validated['email'], // الإيميل يتم التحقق منه كـ email فلا حاجة لـ strip_tags
            'bio'   => strip_tags($validated['bio'] ?? ''),
        ]);

        return back()->with('success', 'تم تحديث بيانات حسابك بنجاح!');
    }

    /**
     * تحديث كلمة المرور بأمان (استخدام التحقق المدمج)
     */
    public function updatePassword(Request $request)
    {
        // استخدام current_password المدمجة في لارافيل للتحقق من كلمة المرور الحالية تلقائياً
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password'     => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()    // يجب أن تحتوي على أحرف
                    ->mixedCase()  // أحرف كبيرة وصغيرة
                    ->numbers()    // أرقام
                    ->symbols()    // رموز
            ],
        ]);

        // تحديث كلمة المرور مشفرة
        Auth::user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }

    /**
     * تحديث الصورة الشخصية (تحسين الأمان والتحقق)
     */
    public function updateImage(Request $request)
    {
        // التحقق من نوع الملف وحجمه (أقصى حجم 2 ميجا)
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = Auth::user();

        try {
            // حذف الصورة القديمة من السيرفر إذا وجدت لتوفير المساحة
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // رفع الصورة الجديدة باسم عشوائي (آمن) وتخزين مسارها
            $path = $request->file('profile_image')->store('profiles', 'public');

            $user->update([
                'profile_image' => $path
            ]);

            return back()->with('success', 'تم تحديث صورتك الشخصية بنجاح!');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء رفع الصورة.');
        }
    }

    /**
     * عرض صفحة الدعم والأسئلة الشائعة
     */
    public function tickets()
    {
        return view('support.index');
    }

    /**
     * معالجة إرسال تذكرة دعم فني (إضافة التحقق المفقود)
     */
    public function sendTicket(Request $request)
    {
        // إضافة التحقق لضمان عدم إرسال بيانات فارغة أو خبيثة
        $validated = $request->validate([
            'subject' => 'required|string|max:255|trim',
            'type'    => 'required|string|in:technical,billing,general', // التحقق من النوع
            'message' => 'required|string|min:10|max:5000',
        ]);

        // تنظيف الرسالة والموضوع قبل الحفظ أو الإرسال
        $finalData = [
            'subject' => strip_tags($validated['subject']),
            'type'    => $validated['type'],
            'message' => strip_tags($validated['message']),
            'user_id' => Auth::id(),
        ];

        // مثال: Ticket::create($finalData);

        return back()->with('success', 'تم إرسال تذكرتك بنجاح! سيقوم فريق الدعم بمراجعتها قريباً.');
    }
}
