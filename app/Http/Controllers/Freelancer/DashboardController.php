<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class DashboardController extends Controller
{
    /**
     * عرض لوحة تحكم المستقل مع إحصائيات الأرباح ودعم الصور السحابية.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. حساب الرصيد وتحديث المحفظة
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'pending_balance' => 0]
        );

        $availableBalance = $wallet->balance;

        $pendingBalance = Transaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        $totalBalance = $availableBalance + $pendingBalance;

        // جلب أقرب موعد لفك الحجز
        $nextTransaction = Transaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereNotNull('unlock_at')
            ->where('unlock_at', '>', now())
            ->orderBy('unlock_at', 'asc')
            ->first();

        $nextUnlockDate = $nextTransaction ? $nextTransaction->unlock_at->toIso8601String() : null;

        // 2. البيانات الأساسية (Basic Stats)
        $completedServicesCount = $user->orders()->where('status', 'completed')->count();
        $completedProjectsCount = $user->freelancerProjects()->where('status', 'completed')->count();
        $totalCompleted = $completedServicesCount + $completedProjectsCount;

        $projRating = $user->receivedReviews()->avg('rating') ?? 0;

        // 3. المهارات
        $skills = $user->skills ? explode(',', $user->skills) : [];

        // 4. بيانات مركز القيادة (Pro Status)
        $levelTarget = 20;
        $levelPercentage = $levelTarget > 0 ? min(($totalCompleted / $levelTarget) * 100, 100) : 0;

        $totalStarted = $user->orders()->where('status', '!=', 'cancelled')->count();
        $reliability = $totalStarted > 0 ? ($totalCompleted / $totalStarted) * 100 : 100;

        $proStatus = [
            'levelPercentage' => round($levelPercentage),
            'reliability' => round($reliability),
            'delivery' => 'سريع',
            'response' => '5 د'
        ];

        // 5. الأهداف السريعة (Quick Goals)
        $financialTarget = 5000;
        $financialPercentage = $financialTarget > 0 ? min(($totalBalance / $financialTarget) * 100, 100) : 0;

        $quickGoals = [
            'income' => [
                'percentage' => round($financialPercentage),
                'text' => '$' . number_format($totalBalance, 0)
            ],
            'rating' => [
                'percentage' => ($projRating / 5) * 100,
                'text' => number_format($projRating, 1)
            ]
        ];

        // 6. جلب آخر الطلبات
        $recentOrders = $user->orders()
            ->with(['service', 'buyer'])
            ->latest()
            ->take(5)
            ->get();

        // 7. معالجة صورة الملف الشخصي من Laravel Cloud (S3)
        $profilePhoto = $user->profile_image
            ? Storage::disk('s3')->url($user->profile_image)
            : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=10b981&color=fff';

        return view('dashboards.freelancer Dashboard', compact(
            'user',
            'totalBalance',
            'availableBalance',
            'pendingBalance',
            'nextUnlockDate',
            'completedServicesCount',
            'completedProjectsCount',
            'projRating',
            'recentOrders',
            'proStatus',
            'quickGoals',
            'skills',
            'profilePhoto'
        ));
    }

    /**
     * تحديث صورة المستقل ورفعها سحابياً (S3).
     */
    public function updateImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $user = Auth::user();

            // حذف الصورة القديمة من S3 إذا وجدت
            if ($user->profile_image) {
                Storage::disk('s3')->delete($user->profile_image);
            }

            // رفع الصورة الجديدة إلى S3
            $path = $request->file('profile_image')->store('profile_images/freelancers', 's3');
            Storage::disk('s3')->setVisibility($path, 'public');

            // تحديث المسار في قاعدة البيانات
            $user->update(['profile_image' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الصورة الشخصية سحابياً بنجاح!',
                'url' => Storage::disk('s3')->url($path)
            ]);

        } catch (Exception $e) {
            Log::error("S3 Freelancer Image Upload Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الرفع للسحاب.'
            ], 500);
        }
    }
}
