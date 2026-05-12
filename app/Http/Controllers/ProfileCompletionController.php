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
 * تم تعديله ليدعم التخزين السحابي (S3) لضمان التوافق مع Laravel Cloud.
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
     */
    public function store(Request $request)
    {
        // 1. التحقق الصارم
        $validatedData = $request->validate([
            'phone'         => 'required|numeric|digits_between:8,15',
            'skills'        => 'required|array|min:1',
            'bio'           => 'required|string|min:30|max:1000',
            'country'       => 'required|string|max:100',
            'id_number'     => 'required|string|unique:users,id_number,' . Auth::id(),
            'id_image'      => 'required|image|mimes:jpeg,png,jpg|max:5120', // رفع الحد لـ 5 ميجا لراحة المستخدم
            'id_image_back' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            $authenticatedUser = Auth::user();

            // 2. إدارة مستندات الهوية سحابياً (S3)
            $frontIdentityPath = $authenticatedUser->id_image;
            $backIdentityPath  = $authenticatedUser->id_image_back;

            // تحديث وجه الهوية الأمامي على S3
            if ($request->hasFile('id_image')) {
                if ($frontIdentityPath) {
                    Storage::disk('s3')->delete($frontIdentityPath);
                }
                $frontIdentityPath = $request->file('id_image')->store('identities/front', 's3');
                Storage::disk('s3')->setVisibility($frontIdentityPath, 'public');
            }

            // تحديث وجه الهوية الخلفي على S3
            if ($request->hasFile('id_image_back')) {
                if ($backIdentityPath) {
                    Storage::disk('s3')->delete($backIdentityPath);
                }
                $backIdentityPath = $request->file('id_image_back')->store('identities/back', 's3');
                Storage::disk('s3')->setVisibility($backIdentityPath, 'public');
            }

            // 3. تحويل مصفوفة التخصصات إلى نص مفصول بفاصلة
            $skillsString = implode(', ', $request->skills);

            // 4. تحديث سجل المستخدم
            $authenticatedUser->update([
                'phone'                => $validatedData['phone'],
                'skills'               => $skillsString,
                'bio'                  => strip_tags($validatedData['bio']),
                'country'              => $validatedData['country'],
                'id_number'            => $validatedData['id_number'],
                'id_image'             => $frontIdentityPath,
                'id_image_back'        => $backIdentityPath,
                'is_profile_completed' => true,
                'verification_status'  => 'pending',
            ]);

            // 5. منطق التوجيه الذكي
            $targetDashboard = route('home');
            if ($authenticatedUser->role === 'freelancer') {
                $targetDashboard = route('freelancer.dashboard');
            } elseif ($authenticatedUser->role === 'client') {
                $targetDashboard = route('client.dashboard');
            }

            return response()->json([
                'success'     => true,
                'message'     => 'تم رفع وثائقك بنجاح إلى الخادم السحابي، ملفك الآن قيد المراجعة.',
                'redirect_to' => $targetDashboard
            ]);

        } catch (Exception $executionError) {
            Log::error("Cloud Upload Failure for User ID " . Auth::id() . ": " . $executionError->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الرفع السحابي، يرجى المحاولة لاحقاً.'
            ], 500);
        }
    }

    /**
     * تحديث الصورة الرمزية للمستخدم سحابياً (S3).
     */
    public function updateImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $currentUser = Auth::user();

            // حذف القديم من S3
            if ($currentUser->profile_image) {
                Storage::disk('s3')->delete($currentUser->profile_image);
            }

            // رفع الجديد لـ S3
            $newAvatarPath = $request->file('profile_image')->store('profile_images/avatars', 's3');
            Storage::disk('s3')->setVisibility($newAvatarPath, 'public');

            $currentUser->update(['profile_image' => $newAvatarPath]);

            return back()->with('success', 'تم تحديث الصورة الشخصية سحابياً بنجاح!');

        } catch (Exception $imageError) {
            Log::error("S3 Avatar Upload Error: " . $imageError->getMessage());
            return back()->with('error', 'فشل تحميل الصورة للسحابة.');
        }
    }
}
