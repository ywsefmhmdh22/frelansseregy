<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    /**
     * عرض لوحة التحكم الرئيسية مع كافة الإحصائيات
     */
    public function index()
    {
        // 1. جلب المستخدمين (أخذ آخر 100 فقط للجدول لتحسين سرعة التحميل)
        $users = User::with(['wallet'])->latest()->take(100)->get();

        // 2. طلبات التوثيق المعلقة (استعلام منفصل لضمان دقة التنبيهات في الـ Navbar والجدول)
        $pendingUsers = User::where('verification_status', 'pending')->latest()->get();

        // 3. المشاريع المعلقة والنزاعات
        $pendingProjects = Project::where('admin_status', 'pending')->latest()->get();

        $disputes = Project::where('status', 'disputed')
                            ->with(['user:id,name', 'freelancer:id,name'])
                            ->get();

        // 4. آخر العمليات المالية
        $transactions = Transaction::with('user:id,name')->latest()->take(10)->get();

        // 5. إحصائيات الخزنة والمشاريع (استعلامات مجمعة سريعة)
        $totalBalance = Wallet::sum('balance');
        $totalWallets = Wallet::count();
        $activeWalletsCount = Wallet::where('balance', '>', 0)->count();

        $projectStats = [
            'total'       => Project::count(),
            'pending'     => Project::where('admin_status', 'pending')->count(),
            'in_progress' => Project::where('status', 'in_progress')->count(),
            'completed'   => Project::where('status', 'completed')->count(),
        ];

        // 6. حساب نسبة النمو الشهري (Optimized)
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();

        $lastMonthCount = User::whereMonth('created_at', $lastMonth->month)
                              ->whereYear('created_at', $lastMonth->year)
                              ->count();

        $currentMonthCount = User::whereMonth('created_at', $now->month)
                                 ->whereYear('created_at', $now->year)
                                 ->count();

        $growthRate = $lastMonthCount > 0
            ? (($currentMonthCount - $lastMonthCount) / $lastMonthCount) * 100
            : ($currentMonthCount > 0 ? 100 : 0);

        // 7. توزيع الأدوار
        $rolesCount = User::select('role', DB::raw('count(*) as total'))
                          ->groupBy('role')
                          ->pluck('total', 'role')
                          ->toArray();

        return view('admin.dashboard', [
            'users'               => $users,
            'pendingUsers'        => $pendingUsers,
            'pendingProjects'     => $pendingProjects,
            'transactions'        => $transactions,
            'disputes'            => $disputes,
            'activeDisputesCount' => $disputes->count(),
            'totalBalance'        => $totalBalance,
            'totalWallets'        => $totalWallets,
            'activeWalletsCount'  => $activeWalletsCount,
            'projectStats'        => $projectStats,
            'growthRate'          => $growthRate,
            'adminsCount'         => $rolesCount['admin'] ?? 0,
            'freelancersCount'    => $rolesCount['freelancer'] ?? 0,
            'clientsCount'        => $rolesCount['client'] ?? 0,
        ]);
    }

    /**
     * تفعيل حساب المستخدم (Verified)
     */
    public function approveUser($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // تحديث الحالة وتأكيد إكمال الملف الشخصي لضمان تخطي الـ Middleware
            $user->update([
                'verification_status' => 'verified',
                'is_profile_completed' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => "تم توثيق حساب {$user->name} بنجاح"
            ]);
        } catch (\Exception $e) {
            Log::error("Error approving user $id: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء التفعيل'], 500);
        }
    }

    /**
     * حظر المستخدم أو إلغاء التوثيق
     */
    public function banUser($id): JsonResponse
    {
        try {
            if (Auth::id() == $id) {
                return response()->json(['success' => false, 'message' => 'لا يمكنك حظر نفسك!'], 403);
            }

            $user = User::findOrFail($id);

            // نستخدم is_banned للحظر الفعلي و unverified لتغيير الشكل في اللوحة
            $user->update([
                'verification_status' => 'unverified',
                'is_banned' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => "تم حظر المستخدم {$user->name} وإلغاء توثيقه"
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'تعذر إتمام العملية'], 500);
        }
    }

    // بقية الدوال (editUser, resetWallet, financeRadar) تظل كما هي لأنها سليمة
    public function editUser($id) {
        $user = User::with('wallet')->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function resetWallet($id): JsonResponse {
        return DB::transaction(function () use ($id) {
            try {
                $user = User::with('wallet')->findOrFail($id);
                if ($user->wallet) {
                    $user->wallet->update(['balance' => 0]);
                    return response()->json(['success' => true, 'message' => 'تم تصفير الرصيد']);
                }
                return response()->json(['success' => false, 'message' => 'المحفظة غير موجودة'], 404);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'فشل العملية'], 500);
            }
        });
    }

    public function financeRadar() {
        return view('admin.finance.index');
    }
}
