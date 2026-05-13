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
 * تم تحديثه ليدعم التخزين السحابي (S3) لضمان التوافق مع Laravel Cloud.
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
     */
    public function updatePersonal(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'bio'   => 'nullable|string|max:1000',
        ]);

        try {
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
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
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
     * تحديث الصورة الرمزية للملف الشخصي (Avatar) سحابياً.
     * تم التعديل لدعم Laravel Cloud (S3) واستجابات AJAX/Axios.
     */
    public function updateImage(Request $request)
    {
        // 1. التحقق من الصورة (رفع الحد لـ 5 ميجا لراحة المستخدم)
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $user = Auth::user();

        try {
            // 2. حذف الصورة القديمة من السحاب (S3) إذا وجدت
            // نستخدم s3 هنا لضمان حذف الملفات من Laravel Cloud
            if ($user->profile_image) {
                Storage::disk('s3')->delete($user->profile_image);
            }

            // 3. رفع الصورة الجديدة لمجلد 'profile_images/avatars' على S3
            $path = $request->file('profile_image')->store('profile_images/avatars', 's3');

            // 4. ضبط الرؤية لتكون عامة (Public) لضمان ظهور الرابط للمستخدمين
            Storage::disk('s3')->setVisibility($path, 'public');

            // 5. تحديث سجل المستخدم بالمسار الجديد
            $user->update([
                'profile_image' => $path
            ]);

            // 6. التحقق إذا كان الطلب قادم عبر Axios (AJAX)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث صورتك الشخصية سحابياً بنجاح!',
                    'image_url' => Storage::disk('s3')->url($path)
                ]);
            }

            return back()->with('success', 'تم تحديث صورتك الشخصية بنجاح!');

        } catch (Exception $e) {
            Log::error("Cloud Image Upload Error [User ID: {$user->id}]: " . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ فني أثناء الرفع للسحاب.'
                ], 500);
            }

            return back()->with('error', 'حدث خطأ فني أثناء رفع الصورة.');
        }
    }

    /**
     * عرض قائمة تذاكر الدعم الفني.
     */
    public function tickets()
    {
        return view('support.index');
    }

    /**
     * إنشاء وإرسال تذكرة دعم فني جديدة.
     */
    public function sendTicket(Request $request)
    {
        $validatedData = $request->validate([
            'subject' => 'required|string|max:255',
            'type'    => 'required|string|in:technical,billing,general',
            'message' => 'required|string|min:10|max:5000',
        ]);

        try {
            // منطق حفظ التذكرة (يمكنك تفعيله لاحقاً)
            Log::channel('support')->info("New Ticket Created by User ID: " . Auth::id());
            return back()->with('success', 'تم إرسال تذكرتك بنجاح! سيتم الرد عليك قريباً.');
        } catch (Exception $e) {
            Log::error("Ticket Dispatch Failure: " . $e->getMessage());
            return back()->with('error', 'فشل إرسال التذكرة، يرجى المحاولة لاحقاً.');
        }
    }
}
