<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WithdrawRequest;
use App\Models\Dispute;
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
        // 1. جلب المستخدمين (آخر 100 لتحسين الأداء)
        $users = User::with(['wallet'])->latest()->take(100)->get();

        // 2. طلبات التوثيق المعلقة
        $pendingUsers = User::where('verification_status', 'pending')->latest()->get();

        // 3. المشاريع المعلقة والنزاعات
        $pendingProjects = Project::where('admin_status', 'pending')->latest()->get();

        $allDisputes = Dispute::with(['user:id,name', 'disputable'])
                                ->latest()
                                ->get();

        // 4. العمليات المالية
        $deposits = Transaction::where('type', 'deposit')
                                ->with('user:id,name')
                                ->latest()
                                ->take(10)
                                ->get();

        $withdrawals = WithdrawRequest::with('user:id,name')
                                ->latest()
                                ->take(10)
                                ->get();

        $transactions = Transaction::with('user:id,name')->latest()->take(10)->get();

        // 5. إحصائيات الخزنة والمشاريع
        $totalBalance = Wallet::sum('balance');
        $totalWallets = Wallet::count();
        $activeWalletsCount = Wallet::where('balance', '>', 0)->count();

        $projectStats = [
            'total'       => Project::count(),
            'pending'     => Project::where('admin_status', 'pending')->count(),
            'in_progress' => Project::where('status', 'in_progress')->count(),
            'completed'   => Project::where('status', 'completed')->count(),
        ];

        // 6. حساب نسبة النمو الشهري
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
            'deposits'            => $deposits,
            'withdrawals'         => $withdrawals,
            'disputes'            => $allDisputes,
            'activeDisputesCount' => $allDisputes->where('status', 'pending')->count(),
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
     * تفعيل أو توثيق حساب المستخدم
     * تم التعديل ليتناسب مع اللوجيك الذكي (تفعيل بسيط / توثيق نهائي)
     */
    public function approveUser(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $type = $request->input('type'); // 'activation' or 'verification'

            if ($type === 'verification') {
                // توثيق نهائي (الحالة verified)
                $user->update([
                    'verification_status' => 'verified',
                    'is_profile_completed' => true
                ]);
                $msg = "تم توثيق حساب {$user->name} بنجاح";
            } else {
                // تفعيل حساب عادي (تغيير الحالة فقط)
                $user->update([
                    'verification_status' => 'verified'
                ]);
                $msg = "تم تفعيل حساب {$user->name} بنجاح";
            }

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            Log::error("Error approving user $id: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء التفعيل'], 500);
        }
    }

    /**
     * حظر المستخدم
     */
    public function banUser($id): JsonResponse
    {
        try {
            if (Auth::id() == $id) {
                return response()->json(['success' => false, 'message' => 'لا يمكنك حظر نفسك!'], 403);
            }

            $user = User::findOrFail($id);

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

    /**
     * حذف المستخدم نهائياً من قاعدة البيانات
     */
    public function destroyUser($id): JsonResponse
    {
        try {
            if (Auth::id() == $id) {
                return response()->json(['success' => false, 'message' => 'لا يمكنك حذف حسابك الشخصي!'], 403);
            }

            $user = User::findOrFail($id);

            // يمكنك هنا إضافة لوجيك لحذف الملفات (صور البطاقة) من الـ Storage قبل حذف السجل

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => "تم حذف المستخدم وكافة بياناته نهائياً"
            ]);
        } catch (\Exception $e) {
            Log::error("Error deleting user $id: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء الحذف'], 500);
        }
    }

    /**
     * معالجة طلب السحب
     */
    public function processWithdrawal(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'decision'     => 'required|in:approve,reject',
                'notification' => 'required|string|min:3'
            ]);

            $withdraw = WithdrawRequest::findOrFail($id);
            $newStatus = ($request->decision === 'approve') ? 'approved' : 'rejected';

            $withdraw->update(['status' => $newStatus]);

            if ($newStatus === 'rejected') {
                $userWallet = Wallet::where('user_id', $withdraw->user_id)->first();
                if ($userWallet) {
                    $userWallet->increment('balance', $withdraw->amount);
                }
            }

            return response()->json([
                'success' => true,
                'message' => $newStatus === 'approved' ? 'تمت الموافقة على السحب بنجاح' : 'تم رفض طلب السحب وإعادة المبلغ للمحفظة'
            ]);

        } catch (\Exception $e) {
            Log::error("Error processing withdrawal $id: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

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
