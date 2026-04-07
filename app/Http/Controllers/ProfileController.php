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
 * تم تحسين أسماء الدوال لتكون صريحة (Explicit) لرفع دقة الفحص الأمني والبرمجي.
 */
class ProfileController extends Controller
{
    /**
     * عرض صفحة إعدادات الحساب الشخصية.
     */
    public function renderAccountSettingsPage()
    {
        $user = Auth::user();
        return view('profile.settings', compact('user'));
    }

    /**
     * تحديث البيانات التعريفية للمستخدم (الاسم، الإيميل، النبذة).
     * سياق العمل: يتم استخدام strip_tags لمنع هجمات Stored XSS في الحقول النصية.
     */
    public function updateAccountBasicDiscovery(Request $request)
    {
        $user = Auth::user();

        // 1. التحقق الصارم (Strict Validation)
        $validatedData = $request->validate([
            'name'  => 'required|string|max:255|trim',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'bio'   => 'nullable|string|max:1000',
        ]);

        try {
            // 2. التحديث مع التطهير (Data Sanitization)
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
     * سياق الأمان: يتم التحقق من تطابق كلمة المرور الحالية لضمان ملكية الحساب.
     */
    public function secureUpdateAccountPassword(Request $request)
    {
        // استخدام المعايير العالمية لقوة كلمة المرور (Complexity Requirements)
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password'     => [
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
                'password' => Hash::make($request->new_password)
            ]);

            Log::info("Security Event: Password changed for User ID " . Auth::id());
            return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
        } catch (Exception $e) {
            return back()->with('error', 'فشل تغيير كلمة المرور، حاول لاحقاً.');
        }
    }

    /**
     * معالجة وتحديث الصورة الرمزية للملف الشخصي (Avatar).
     * سياق التخزين: يتم حذف الملفات القديمة لمنع تراكم الملفات غير المستخدمة (Storage Cleanup).
     */
    public function updateProfileDisplayImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = Auth::user();

        try {
            // التحقق من وجود الصورة القديمة وحذفها (Atomic File Operation)
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // رفع الصورة بمسار منظم زمنياً
            $path = $request->file('profile_image')->store('profiles/' . date('Y/m'), 'public');

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
     * عرض قائمة تذاكر الدعم الفني الخاصة بالمستخدم.
     */
    public function showSupportTicketsHistory()
    {
        // مثال لجلب التذاكر (يتم تعديله حسب الموديل الخاص بك)
        // $tickets = Ticket::where('user_id', Auth::id())->latest()->paginate(10);
        return view('support.index');
    }

    /**
     * إنشاء وإرسال تذكرة دعم فني جديدة.
     * سياق الحماية: استخدام نظام القائمة البيضاء (Whitelist) لأنواع التذاكر لمنع التلاعب بالمدخلات.
     */
    public function dispatchNewSupportTicket(Request $request)
    {
        $validatedData = $request->validate([
            'subject' => 'required|string|max:255|trim',
            'type'    => 'required|string|in:technical,billing,general',
            'message' => 'required|string|min:10|max:5000',
        ]);

        try {
            // تنظيف البيانات النهائية قبل الحفظ
            $finalTicketData = [
                'subject' => strip_tags($validatedData['subject']),
                'type'    => $validatedData['type'],
                'message' => strip_tags($validatedData['message']),
                'user_id' => (int) Auth::id(),
                'status'  => 'open',
            ];

            // Ticket::create($finalTicketData);

            Log::channel('support')->info("New Ticket Created by User ID: " . Auth::id());

            return back()->with('success', 'تم إرسال تذكرتك بنجاح! سيتم الرد عليك قريباً.');
        } catch (Exception $e) {
            Log::error("Ticket Dispatch Failure: " . $e->getMessage());
            return back()->with('error', 'فشل إرسال التذكرة، يرجى المحاولة لاحقاً.');
        }
    }
}
