<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Wallet; // تأكد من وجود الموديل ده عندك
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. جلب البيانات الأساسية للمستخدمين مع علاقة المحفظة (Eager Loading)
        // ده بيمنع مشكلة الـ N+1 ويخلي الصفحة تحمل بسرعة
        $users = User::with('wallet')->latest()->get();

        // جلب المستخدمين الذين ينتظرون التوثيق
        $pendingUsers = User::where('verification_status', 'pending')
                            ->latest()
                            ->get();

        // 2. جلب المشاريع والنزاعات
        $pendingProjects = Project::where('admin_status', 'pending')->latest()->get();

        // جلب النزاعات مع بيانات أطراف النزاع
        $disputes = Project::where('status', 'disputed')->with(['user', 'freelancer'])->get();

        // عدد النزاعات النشطة
        $activeDisputesCount = $disputes->count();

        // جلب آخر 10 عمليات مالية
        $transactions = Transaction::with('user')->latest()->take(10)->get();

        // 3. إحصائيات الخزنة المركزية (تعديل للسحب من جدول المحافظ)
        $totalBalance = Wallet::sum('balance');
        $totalWallets = Wallet::count();
        $activeWalletsCount = Wallet::where('balance', '>', 0)->count();

        // 4. إحصائيات المشاريع
        $projectStats = [
            'total' => Project::count(),
            'pending' => Project::where('admin_status', 'pending')->count(),
            'in_progress' => Project::where('status', 'in_progress')->count(),
            'completed' => Project::where('status', 'completed')->count(),
        ];

        // 5. حساب نسبة النمو
        $lastMonthCount = User::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();
        $currentMonthCount = User::whereMonth('created_at', Carbon::now()->month)->count();
        $growthRate = $lastMonthCount > 0 ? (($currentMonthCount - $lastMonthCount) / $lastMonthCount) * 100 : ($currentMonthCount > 0 ? 100 : 0);

        // 6. توزيع الأدوار
        $adminsCount = User::where('role', 'admin')->count();
        $freelancersCount = User::where('role', 'freelancer')->count();
        $clientsCount = User::where('role', 'client')->count();

        return view('admin.dashboard', compact(
            'users',
            'pendingUsers',
            'pendingProjects',
            'transactions',
            'disputes',
            'activeDisputesCount',
            'totalBalance',
            'totalWallets',
            'activeWalletsCount',
            'projectStats',
            'growthRate',
            'adminsCount',
            'freelancersCount',
            'clientsCount'
        ));
    }

    /**
     * دالة رادار الإحصائيات المتقدمة
     */
    public function financeRadar()
    {
        return view('admin.finance.index');
    }

    /**
     * تفعيل حساب المستخدم (Approve)
     * ملاحظة: تم التأكد من توافق الـ ID مع مسار الـ JS في البليد
     */
    public function approveUser($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'verification_status' => 'approved'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم قبول المستخدم ' . $user->name . ' وتفعيل حسابه بنجاح'
        ]);
    }

    /**
     * حظر المستخدم
     */
    public function banUser($id)
    {
        $user = User::findOrFail($id);

        $user->update(['verification_status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'تم استبعاد المستخدم بنجاح'
        ]);
    }

    /**
     * تصفير المحفظة (تعديل الربط مع جدول Wallets)
     */
    public function resetWallet($id)
    {
        $user = User::findOrFail($id);

        // إذا كان هناك محفظة مرتبطة، نقوم بتصفيرها
        if ($user->wallet) {
            $user->wallet->update(['balance' => 0]);
        } else {
            // في حالة كان الرصيد لا يزال في جدول الـ users كخانة قديمة
            $user->update(['balance' => 0]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تصفير الخزنة بنجاح'
        ]);
    }
}
