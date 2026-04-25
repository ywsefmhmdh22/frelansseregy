<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * عرض لوحة تحكم المستقل مع إحصائيات الأرباح المعلقة والعداد التنازلي.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. حساب الرصيد وتحديث المحفظة
        // نستخدم firstOrCreate للتأكد من وجود محفظة للمستخدم دائماً
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'pending_balance' => 0]
        );

        // الرصيد المتاح: يتم جلبه من المحفظة مباشرة
        $availableBalance = $wallet->balance;

        // الرصيد المعلق: يتم حسابه ديناميكياً من جدول العمليات لضمان الدقة اللحظية
        $pendingBalance = Transaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        // إجمالي الرصيد (المتاح + المعلق)
        $totalBalance = $availableBalance + $pendingBalance;

        // --- التعديل: جلب أقرب موعد لفك الحجز عن رصيد معلق (تم تصحيح اسم العمود إلى unlock_at) ---
        $nextTransaction = Transaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereNotNull('unlock_at')
            ->where('unlock_at', '>', now())
            ->orderBy('unlock_at', 'asc')
            ->first();

        // تمرير التاريخ بصيغة ISO لضمان توافقه مع JavaScript في جميع المتصفحات
        $nextUnlockDate = $nextTransaction ? $nextTransaction->unlock_at->toIso8601String() : null;

        // 2. البيانات الأساسية (Basic Stats)
        // عدد الخدمات المكتملة
        $completedServicesCount = $user->orders()->where('status', 'completed')->count();

        // عدد المشاريع المكتملة
        $completedProjectsCount = $user->freelancerProjects()->where('status', 'completed')->count();

        $totalCompleted = $completedServicesCount + $completedProjectsCount;

        // متوسط التقييمات
        $projRating = $user->receivedReviews()->avg('rating') ?? 0;

        // 3. المهارات
        $skills = $user->skills ? explode(',', $user->skills) : [];

        // 4. بيانات مركز القيادة (Pro Status)
        $levelTarget = 20;
        $levelPercentage = $levelTarget > 0 ? min(($totalCompleted / $levelTarget) * 100, 100) : 0;

        // حساب الموثوقية
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

        // إرسال كافة المتغيرات المطلوبة للـ Blade
        return view('dashboards.freelancer Dashboard', compact(
            'user',
            'totalBalance',
            'availableBalance',
            'pendingBalance',
            'nextUnlockDate', // المتغير الجديد للعداد التنازلي
            'completedServicesCount',
            'completedProjectsCount',
            'projRating',
            'recentOrders',
            'proStatus',
            'quickGoals',
            'skills'
        ));
    }
}
