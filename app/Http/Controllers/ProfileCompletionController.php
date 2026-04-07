<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Class ProfileCompletionController
 * مسؤول عن التحقق من بيانات الهوية والمعلومات المهنية للمستخدمين الجدد.
 * يضمن الكود سلامة المستندات المرفوعة وتوجيه المستخدم بناءً على صلاحياته (Role).
 */
class ProfileCompletionController extends Controller
{
    /**
     * عرض صفحة استكمال البيانات الشخصية والمهنية.
     */
    public function index()
    {
        return view('auth.complete-profile');
    }

    /**
     * معالجة وحفظ بيانات التحقق (Identity & Professional Bio).
     * يستخدم هذا التابع لتأمين الحسابات قبل السماح لها بالنشاط المالي.
     */
    public function store(Request $request)
    {
        // 1. التحقق الصارم مع رسائل خطأ واضحة وسياق محدد
        $validatedData = $request->validate([
            'phone'         => 'required|numeric|digits_between:8,15',
            'skills'        => 'required|string|max:500',
            'bio'           => 'required|string|min:30|max:1000',
            'country'       => 'required|string|max:100',
            'id_number'     => 'required|string|unique:users,id_number,' . Auth::id(),
            'id_image'      => 'required|image|mimes:jpeg,png,jpg|max:3072', // زيادة الحد لضمان وضوح الهوية
            'id_image_back' => 'required|image|mimes:jpeg,png,jpg|max:3072',
        ]);

        try {
            $authenticatedUser = Auth::user();

            // 2. إدارة مستندات الهوية (Identity Document Management)
            // نستخدم متغيرات بأسماء واضحة تفرق بين الوجه الأمامي والخلفي
            $frontIdentityPath = $authenticatedUser->id_image;
            $backIdentityPath  = $authenticatedUser->id_image_back;

            // تحديث وجه الهوية الأمامي
            if ($request->hasFile('id_image')) {
                if ($frontIdentityPath) Storage::disk('public')->delete($frontIdentityPath);
                $frontIdentityPath = $request->file('id_image')->store('identities/front', 'public');
            }

            // تحديث وجه الهوية الخلفي
            if ($request->hasFile('id_image_back')) {
                if ($backIdentityPath) Storage::disk('public')->delete($backIdentityPath);
                $backIdentityPath = $request->file('id_image_back')->store('identities/back', 'public');
            }

            // 3. تحديث السجل الموحد للمستخدم (Atomic Update)
            $authenticatedUser->update([
                'phone'                => $validatedData['phone'],
                'skills'               => strip_tags($validatedData['skills']),
                'bio'                  => strip_tags($validatedData['bio']),
                'country'              => $validatedData['country'],
                'id_number'            => $validatedData['id_number'],
                'id_image'             => $frontIdentityPath,
                'id_image_back'        => $backIdentityPath,
                'is_profile_completed' => true,
                'verification_status'  => 'pending', // إرسال الملف للمراجعة اليدوية من قبل الإدارة
            ]);

            // 4. منطق التوجيه الذكي بناءً على نوع الحساب (Dynamic Redirection)
            $targetDashboard = route('home');

            if ($authenticatedUser->role === 'freelancer') {
                $targetDashboard = route('freelancer.dashboard');
            } elseif ($authenticatedUser->role === 'client') {
                $targetDashboard = route('client.dashboard');
            }

            return response()->json([
                'success'     => true,
                'message'     => 'تم استلام بيانات الهوية بنجاح، ملفك الآن تحت المراجعة.',
                'redirect_to' => $targetDashboard
            ]);

        } catch (Exception $executionError) {
            // تسجيل الخطأ مع السياق لتسهيل تتبع المشاكل التقنية
            Log::error("Profile Completion Failure for User ID " . Auth::id() . ": " . $executionError->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ تقني أثناء حفظ البيانات، يرجى المحاولة لاحقاً.'
            ], 500);
        }
    }

    /**
     * تحديث الصورة الرمزية للمستخدم (Profile Avatar Update).
     */
    public function updateImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $currentUser = Auth::user();

            if ($currentUser->profile_image) {
                Storage::disk('public')->delete($currentUser->profile_image);
            }

            $newAvatarPath = $request->file('profile_image')->store('profile_images/avatars', 'public');
            $currentUser->update(['profile_image' => $newAvatarPath]);

            return back()->with('success', 'تم تحديث الصورة الشخصية بنجاح!');

        } catch (Exception $imageError) {
            Log::error("Avatar Upload Error: " . $imageError->getMessage());
            return back()->with('error', 'فشل تحميل الصورة.');
        }
    }
}
