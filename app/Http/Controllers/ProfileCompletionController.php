<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileCompletionController extends Controller
{
    /**
     * عرض صفحة استكمال البيانات
     */
    public function index()
    {
        return view('auth.complete-profile');
    }

    /**
     * حفظ البيانات وتوجيه المستخدم للداشبورد المناسبة
     */
    public function store(Request $request)
    {
        // 1. التحقق من صحة البيانات المدخلة
        $request->validate([
            'phone'         => 'required|numeric',
            'skills'        => 'required|string|max:255',
            'bio'           => 'required|string|min:30',
            'country'       => 'required|string',
            'id_number'     => 'required|string|unique:users,id_number',
            'id_image'      => 'required|image|mimes:jpeg,png,jpg|max:2048', // الحد الأقصى 2 ميجا
            'id_image_back' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();

        // 2. معالجة رفع صور الهوية
        $idFrontPath = null;
        $idBackPath  = null;

        if ($request->hasFile('id_image')) {
            // تخزين في: storage/app/public/identities
            $idFrontPath = $request->file('id_image')->store('identities', 'public');
        }

        if ($request->hasFile('id_image_back')) {
            $idBackPath = $request->file('id_image_back')->store('identities', 'public');
        }

        // 3. تحديث بيانات المستخدم في قاعدة البيانات
        $user->update([
            'phone'                => $request->phone,
            'skills'               => $request->skills,
            'bio'                  => $request->bio,
            'country'              => $request->country,
            'id_number'            => $request->id_number,
            'id_image'             => $idFrontPath,
            'id_image_back'        => $idBackPath,
            'is_profile_completed' => true,
            'verification_status'  => 'pending', // الحالة الافتراضية حتى يراجعها الأدمن
        ]);

        // 4. التوجيه الذكي بناءً على رتبة المستخدم (Role)
        if ($user->role === 'freelancer') {
            return redirect()->route('freelancer.dashboard')->with('success', 'تم إرسال بياناتك بنجاح، وهي قيد المراجعة الآن.');
        }

        if ($user->role === 'client') {
            return redirect()->route('client.dashboard')->with('success', 'تم إرسال بياناتك بنجاح، وهي قيد المراجعة الآن.');
        }

        // في حال عدم وجود رتبة محددة، يرجع للهوم
        return redirect()->route('home')->with('success', 'تم حفظ البيانات بنجاح.');
    }

    /**
     * تحديث الصورة الشخصية (الإضافة المطلوبة لزر الكاميرا)
     */
    public function updateImage(Request $request)
    {
        // 1. التحقق من الصورة الشخصية
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // 2. حذف الصورة القديمة إذا وجدت لتوفير المساحة
        if ($user->profile_image) {
            Storage::delete('public/' . $user->profile_image);
        }

        // 3. رفع الصورة الجديدة في مجلد profile_images
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');

            // 4. تحديث قاعدة البيانات
            $user->update([
                'profile_image' => $path
            ]);
        }

        return back()->with('success', 'تم تحديث الصورة الشخصية بنجاح!');
    }
}
