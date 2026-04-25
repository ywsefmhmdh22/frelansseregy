<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Exception;

/**
 * Class ProfileController
 * يدير إعدادات الحساب، الهوية الرقمية، ونظام تذاكر الدعم الفني.
 */
class ProfileController extends Controller
{
    /**
     * عرض صفحة إعدادات الحساب الشخصية.
     * تخدم الـ Route: profile.settings
     */
    public function settings()
    {
        $user = Auth::user();
        return view('profile.settings', compact('user'));
    }

    /**
     * تحديث البيانات الشخصية (الاسم، الإيميل، النبذة).
     * تم تعديل الاسم من (updateAccountBasicDiscovery) إلى (updatePersonal) ليتوافق مع الـ Route.
     */
    public function updatePersonal(Request $request)
    {
        $user = Auth::user();

        // 1. التحقق الصارم (Strict Validation)
        $validatedData = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'bio'   => 'nullable|string|max:1000',
        ]);

        try {
            // 2. التحديث مع التطهير (Data Sanitization) لمنع هجمات XSS
            $user->update([
                'name'  => strip_tags($validatedData['name']),
                'email' => $validatedData['email'],
                'bio'   => strip_tags($validatedData['bio'] ?? ''),
            ]);

            return back()->with('success', 'تم تحديث بيانات حسابك بنجاح!');
        } catch (Exception $e) {
            Log::error("Profile Update Failure [User ID: {$user->id}]: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحديث البيانات.');
        }
    }

    /**
     * تغيير كلمة المرور بنظام التشفير الآمن.
     * تم تعديل الاسم من (secureUpdateAccountPassword) إلى (updatePassword) ليتوافق مع الـ Route.
     */
    public function updatePassword(Request $request)
    {
        // استخدام المعايير العالمية لقوة كلمة المرور
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()    // أحرف
                    ->mixedCase()  // حالة أحرف مختلطة
                    ->numbers()    // أرقام
                    ->symbols()    // رموز
            ],
        ]);

        try {
            Auth::user()->update([
                'password' => Hash::make($request->password)
            ]);

            Log::info("Security Event: Password changed for User ID " . Auth::id());
            return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
        } catch (Exception $e) {
            return back()->with('error', 'فشل تغيير كلمة المرور، حاول لاحقاً.');
        }
    }

    /**
     * تحديث الصورة الرمزية للملف الشخصي (Avatar).
     */
    public function updateImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = Auth::user();

        try {
            // حذف الصورة القديمة لمنع تراكم الملفات
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // رفع الصورة بمسار منظم
            $path = $request->file('profile_image')->store('profiles/' . date('Y/m'), 'public');

            // تحديث سجل المستخدم
            $user->update([
                'profile_image' => $path
            ]);

            return back()->with('success', 'تم تحديث صورتك الشخصية بنجاح!');
        } catch (Exception $e) {
            Log::error("Image Upload Error [User ID: {$user->id}]: " . $e->getMessage());
            return back()->with('error', 'حدث خطأ فني أثناء رفع الصورة.');
        }
    }

    /**
     * عرض قائمة تذاكر الدعم الفني.
     * تم تعديل الاسم من (showSupportTicketsHistory) إلى (tickets) ليتوافق مع الـ Route.
     */
    public function tickets()
    {
        return view('support.index');
    }

    /**
     * إنشاء وإرسال تذكرة دعم فني جديدة.
     * تم تعديل الاسم من (dispatchNewSupportTicket) إلى (sendTicket) ليتوافق مع الـ Route.
     */
    public function sendTicket(Request $request)
    {
        $validatedData = $request->validate([
            'subject' => 'required|string|max:255',
            'type'    => 'required|string|in:technical,billing,general',
            'message' => 'required|string|min:10|max:5000',
        ]);

        try {
            // هنا تضع منطق حفظ التذكرة في قاعدة البيانات (Ticket::create...)

            Log::channel('support')->info("New Ticket Created by User ID: " . Auth::id());

            return back()->with('success', 'تم إرسال تذكرتك بنجاح! سيتم الرد عليك قريباً.');
        } catch (Exception $e) {
            Log::error("Ticket Dispatch Failure: " . $e->getMessage());
            return back()->with('error', 'فشل إرسال التذكرة، يرجى المحاولة لاحقاً.');
        }
    }
}
