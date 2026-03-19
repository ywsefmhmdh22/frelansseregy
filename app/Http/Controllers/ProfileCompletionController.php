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
            'id_number'     => 'required|string|unique:users,id_number,' . Auth::id(), // السماح بتحديث نفس الرقم للمستخدم الحالي
            'id_image'      => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'id_image_back' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();

        // 2. معالجة رفع صور الهوية
        $idFrontPath = $user->id_image;
        $idBackPath  = $user->id_image_back;

        if ($request->hasFile('id_image')) {
            if ($user->id_image) Storage::delete('public/' . $user->id_image);
            $idFrontPath = $request->file('id_image')->store('identities', 'public');
        }

        if ($request->hasFile('id_image_back')) {
            if ($user->id_image_back) Storage::delete('public/' . $user->id_image_back);
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
            'verification_status'  => 'pending', // الحالة ترجع انتظار حتى يراجعها الأدمن
        ]);

        // 4. التوجيه الذكي (إرسال رابط التوجيه كـ JSON ليتعامل معه Axios)
        $redirectUrl = route('home');

        if ($user->role === 'freelancer') {
            $redirectUrl = route('freelancer.dashboard');
        } elseif ($user->role === 'client') {
            $redirectUrl = route('client.dashboard');
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال بياناتك بنجاح، وهي قيد المراجعة الآن.',
            'redirect_to' => $redirectUrl
        ]);
    }

    /**
     * تحديث الصورة الشخصية
     */
    public function updateImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        if ($user->profile_image) {
            Storage::delete('public/' . $user->profile_image);
        }

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $user->update(['profile_image' => $path]);
        }

        return back()->with('success', 'تم تحديث الصورة الشخصية بنجاح!');
    }
}
