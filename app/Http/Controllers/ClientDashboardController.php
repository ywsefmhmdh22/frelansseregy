<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Favorite;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ClientDashboardController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للوحة تحكم العميل - تم تحسين الاستعلامات واستخدام الكاش
     */
    public function index()
    {
        $userId = Auth::id();

        // 1. استخدام التخزين المؤقت للإحصائيات والمشاريع لمدة 5 دقائق لتقليل الضغط
        $dashboardData = Cache::remember("client_dashboard_cache_{$userId}", 300, function () use ($userId) {

            // جلب آخر 5 مشاريع مع تحميل عدد العروض مسبقاً
            $projects = Project::where('user_id', $userId)
                ->withCount('proposals')
                ->latest()
                ->limit(5)
                ->get();

            // حساب جميع الإحصائيات في استعلامات تجميعية سريعة
            $stats = [
                'total_projects'   => Project::where('user_id', $userId)->count(),
                'pending_projects' => Project::where('user_id', $userId)->where('admin_status', 'pending')->count(),
                'active_projects'  => Project::where('user_id', $userId)->where('admin_status', 'approved')->count(),
                'total_spent'      => Project::where('user_id', $userId)->where('status', 'completed')->sum('final_price') ?? 0,
            ];

            return compact('projects', 'stats');
        });

        // 2. المحفظة (يفضل عدم وضعها في الكاش لأنها تتغير باستمرار)
        $wallet = Wallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);
        $walletBalance = $wallet->balance;

        return view('dashboards.Client Dashboard', [
            'myProjects'    => $dashboardData['projects'],
            'stats'         => $dashboardData['stats'],
            'walletBalance' => $walletBalance
        ]);
    }

    /**
     * عرض قائمة بكل مشاريع العميل - تحسين باستخدام Pagination
     */
    public function myProjects()
    {
        // استخدام simplePaginate أسرع من paginate العادي في الجداول الضخمة
        $myProjects = Project::where('user_id', Auth::id())
            ->withCount('proposals')
            ->latest()
            ->paginate(10);

        return view('projects.index', compact('myProjects'));
    }

    /**
     * عرض العروض المقدمة لمشروع معين - حل مشكلة الـ N+1 في اليوزرز
     */
    public function projectOffers($id)
    {
        // تم استخدام Eager Loading لتحميل بيانات صاحب العرض (user) دفعة واحدة
        $project = Project::where('user_id', Auth::id())
            ->with(['proposals' => function($q) {
                $q->with('user')->latest();
            }])
            ->findOrFail($id);

        $offers = $project->proposals;

        return view('projects.offers', compact('project', 'offers'));
    }

    /**
     * عرض المحفظة والمعاملات
     */
    public function wallet()
    {
        $userId = Auth::id();
        $wallet = Wallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);
        $walletBalance = $wallet->balance;

        // جلب آخر المعاملات المالية مع تحديد الأعمدة المطلوبة فقط لتقليل استهلاك الذاكرة
        $transactions = Transaction::where('user_id', $userId)
            ->select('id', 'amount', 'type', 'status', 'payment_method', 'created_at')
            ->latest()
            ->limit(10)
            ->get();

        return view('wallet.index', compact('walletBalance', 'transactions'));
    }

    /**
     * عرض المستقلين المفضلين - تحسين تحميل بيانات الـ Freelancer
     */
    public function favorites()
    {
        $favorites = Favorite::where('user_id', Auth::id())
            ->with('freelancer:id,name,image_url,freelancer_rating') // جلب أعمدة محددة فقط
            ->latest()
            ->paginate(12);

        return view('freelancers.favorites', compact('favorites'));
    }

    /**
     * إضافة أو حذف مستقل من المفضلات
     */
    public function toggleFavorite(Request $request)
    {
        $request->validate(['freelancer_id' => 'required|exists:users,id']);

        $userId = Auth::id();
        $freelancerId = $request->freelancer_id;

        // تحديث أو حذف
        Favorite::updateOrCreate(
            ['user_id' => $userId, 'freelancer_id' => $freelancerId]
        )->wasRecentlyCreated ?: Favorite::where(['user_id' => $userId, 'freelancer_id' => $freelancerId])->delete();

        return back()->with('success', 'تم تحديث قائمة المفضلات');
    }

    /**
     * تنفيذ طلب السحب - مع تأمين البيانات والعمليات
     */
    public function processWithdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'method' => 'required|string|max:50',
            'account_info' => 'required|string|max:500',
        ]);

        $userId = Auth::id();

        try {
            return DB::transaction(function () use ($userId, $request) {
                // استخدام lockForUpdate لمنع حدوث Race Condition في الرصيد
                $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

                if (!$wallet || $wallet->balance < $request->amount) {
                    throw new \Exception('رصيدك غير كافٍ.');
                }

                // 1. الخصم
                $wallet->decrement('balance', $request->amount);

                // 2. تسجيل العملية
                Transaction::create([
                    'user_id' => $userId,
                    'amount' => $request->amount,
                    'type' => 'withdraw',
                    'status' => 'pending',
                    'payment_method' => strip_tags($request->method),
                    'details' => 'طلب سحب إلى: ' . strip_tags($request->account_info)
                ]);

                // مسح الكاش لتظهر الإحصائيات الجديدة
                Cache::forget("client_dashboard_cache_{$userId}");

                return redirect()->route('client.dashboard')
                    ->with('success', 'تم تقديم طلب السحب بنجاح.');
            });
        } catch (\Exception $e) {
            Log::error('Withdraw Process Error: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }
}
