<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. البيانات الأساسية (Basic Stats)
        $completedServicesCount = $user->orders()->where('status', 'completed')->count();
        $completedProjectsCount = $user->freelancerProjects()->where('status', 'completed')->count();
        $totalCompleted = $completedServicesCount + $completedProjectsCount;

        // التقييمات والرصيد
        $projRating = $user->receivedReviews()->avg('rating') ?? 0;
        $pendingBalance = $user->wallet->pending_balance ?? 0;

        // حساب إجمالي مبيعات المستقل (لحل مشكلة الخطأ في الـ Blade)
        $totalSalesInUsd = $user->wallet->balance ?? 0;

        // 2. حساب بيانات مركز القيادة (أرقام حقيقية)

        // نسبة المستوى: لنفترض أن "مستقل محترف" يحتاج 20 مشروع مكتمل
        $levelTarget = 20;
        $levelPercentage = min(($totalCompleted / $levelTarget) * 100, 100);

        // الموثوقية: تعتمد على (المشاريع المكتملة / إجمالي المشاريع التي استلمها ولم يلغها)
        $totalStarted = $user->orders()->where('status', '!=', 'cancelled')->count();
        $reliability = $totalStarted > 0 ? ($totalCompleted / $totalStarted) * 100 : 100;

        $proStatus = [
            'levelPercentage' => round($levelPercentage),
            'reliability' => round($reliability),
            'delivery' => $this->calculateDeliverySpeed($user),
            'response' => '5 د', // قيمة افتراضية حالياً
            'deliveryClass' => 'text-info',
            'responseClass' => 'text-warning'
        ];

        // 3. الأهداف السريعة (Quick Goals)
        $financialTarget = 5000; // هدف افتراضي بالدولار أو الجنيه
        $financialPercentage = $financialTarget > 0 ? min(($totalSalesInUsd / $financialTarget) * 100, 100) : 0;

        $quickGoals = [
            'financial' => [
                'percentage' => round($financialPercentage),
                'text' => round($financialPercentage) . '%'
            ],
            'reviews' => [
                'percentage' => ($projRating / 5) * 100,
                'text' => number_format($projRating, 1)
            ]
        ];

        // 4. جلب آخر الطلبات مع العلاقات اللازمة
        $recentOrders = $user->orders()->with(['service', 'buyer'])->latest()->take(5)->get();

        return view('dashboards.freelancer Dashboard', compact(
            'user',
            'completedServicesCount',
            'completedProjectsCount',
            'projRating',
            'pendingBalance',
            'recentOrders',
            'proStatus',
            'quickGoals',
            'totalSalesInUsd' // تم إضافته لضمان عدم حدوث الخطأ السابق
        ));
    }

    // دالة تقدير سرعة التسليم (بسيطة حالياً)
    private function calculateDeliverySpeed($user) {
        return 'سريع';
    }
}
