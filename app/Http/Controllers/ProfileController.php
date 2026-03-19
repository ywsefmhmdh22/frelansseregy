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
     */
    public function updatePersonal(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'bio'   => 'nullable|string|max:1000',
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'bio'   => $request->bio,
        ]);

        return back()->with('success', 'تم تحديث بيانات حسابك بنجاح!');
    }

    /**
     * تحديث كلمة المرور بأمان
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => ['required', 'confirmed', Password::min(8)],
        ]);

        // التحقق من أن كلمة المرور الحالية صحيحة
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية التي أدخلتها غير صحيحة.']);
        }

        // تحديث كلمة المرور في الداتابيز
        Auth::user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }

    /**
     * تحديث الصورة الشخصية (مع مسح القديمة لتوفير المساحة)
     */
    public function updateImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = Auth::user();

        // حذف الصورة القديمة من السيرفر إذا وجدت
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // رفع الصورة الجديدة وتخزين مسارها
        $path = $request->file('profile_image')->store('profiles', 'public');

        $user->update([
            'profile_image' => $path
        ]);

        return back()->with('success', 'تم تحديث صورتك الشخصية بنجاح!');
    }

    /**
     * عرض صفحة الدعم والأسئلة الشائعة
     */
    public function tickets()
    {
        return view('support.index');
    }

    /**
     * معالجة إرسال تذكرة دعم فني
     */
    public function sendTicket(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'type'    => 'required',
            'message' => 'required|string|min:10',
        ]);

        // يمكنك هنا إضافة Ticket::create(...) إذا كان لديك جدول للتذاكر

        return back()->with('success', 'تم إرسال تذكرتك بنجاح! سيقوم فريق الدعم بمراجعتها قريباً.');
    }
}
