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
use Exception;

/**
 * Class ClientDashboardController
 * مسؤول عن لوحة تحكم العميل مع التركيز على أداء الاستعلامات وسرعة التحميل.
 */
class ClientDashboardController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للوحة تحكم العميل - محسنة للأداء العالي.
     */
    public function index()
    {
        $userId = Auth::id();

        // 1. استخدام الكاش لتخزين البيانات الثقيلة (الإحصائيات والمشاريع)
        // تقليل الضغط بجعل الاستعلامات تتم مرة واحدة كل 5 دقائق
        $dashboardData = Cache::remember("client_dashboard_cache_{$userId}", 300, function () use ($userId) {

            // جلب بيانات محددة فقط (Select) لتقليل استهلاك الذاكرة (RAM)
            $projects = Project::where('user_id', $userId)
                ->select('id', 'title', 'status', 'admin_status', 'created_at')
                ->withCount('proposals')
                ->latest()
                ->limit(5)
                ->get();

            // استخدام استعلامات تجميعية (Aggregated Queries) بدلاً من جلب السجلات وحسابها برمجياً
            $stats = [
                'total_projects'   => Project::where('user_id', $userId)->count(),
                'pending_projects' => Project::where('user_id', $userId)->where('admin_status', 'pending')->count(),
                'active_projects'  => Project::where('user_id', $userId)->where('admin_status', 'approved')->count(),
                'total_spent'      => Project::where('user_id', $userId)->where('status', 'completed')->sum('final_price') ?? 0,
            ];

            return compact('projects', 'stats');
        });

        // 2. المحفظة (بيانات لحظية لا تخزن في الكاش لضمان الدقة المالية)
        $wallet = Wallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);

        return view('dashboards.Client Dashboard', [
            'myProjects'    => $dashboardData['projects'],
            'stats'         => $dashboardData['stats'],
            'walletBalance' => $wallet->balance
        ]);
    }

    /**
     * عرض قائمة المشاريع - تحسين باستخدام Pagination لتقليل حجم البيانات المرسلة.
     */
    public function myProjects()
    {
        $myProjects = Project::where('user_id', Auth::id())
            ->select('id', 'title', 'status', 'admin_status', 'final_price', 'created_at') // جلب ما يظهر في الجدول فقط
            ->withCount('proposals')
            ->latest()
            ->paginate(10); // تقسيم النتائج لسرعة استجابة المتصفح

        return view('projects.index', compact('myProjects'));
    }

    /**
     * عرض العروض - حل مشكلة الـ N+1 Query عبر الـ Eager Loading.
     */
    public function projectOffers($id)
    {
        // استخدام Eager Loading لتحميل بيانات المستخدم مع العروض في استعلام واحد فقط
        $project = Project::where('user_id', Auth::id())
            ->with(['proposals' => function($q) {
                $q->select('id', 'project_id', 'user_id', 'amount', 'duration', 'description', 'created_at')
                  ->with('user:id,name,image_url') // جلب أعمدة محددة من جدول المستخدمين
                  ->latest();
            }])
            ->findOrFail($id);

        $offers = $project->proposals;

        return view('projects.offers', compact('project', 'offers'));
    }

    /**
     * عرض المحفظة - تحسين جلب المعاملات المالية.
     */
    public function wallet()
    {
        $userId = Auth::id();
        $wallet = Wallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);

        // تحديد الأعمدة فقط (Select) يسرع العملية جداً في جداول المعاملات الكبيرة
        $transactions = Transaction::where('user_id', $userId)
            ->select('id', 'amount', 'type', 'status', 'payment_method', 'created_at')
            ->latest()
            ->limit(15)
            ->get();

        return view('wallet.index', [
            'walletBalance' => $wallet->balance,
            'transactions'  => $transactions
        ]);
    }

    /**
     * عرض المفضلات - Eager Loading لتجنب بطء تحميل صور المستقلين.
     */
    public function favorites()
    {
        $favorites = Favorite::where('user_id', Auth::id())
            ->with('freelancer:id,name,image_url,freelancer_rating')
            ->latest()
            ->paginate(12);

        return view('freelancers.favorites', compact('favorites'));
    }

    /**
     * إضافة/حذف مفضلة.
     */
    public function toggleFavorite(Request $request)
    {
        $request->validate(['freelancer_id' => 'required|exists:users,id']);

        $userId = Auth::id();
        $freelancerId = $request->freelancer_id;

        $favorite = Favorite::where('user_id', $userId)->where('freelancer_id', $freelancerId)->first();

        if ($favorite) {
            $favorite->delete();
            $message = 'تم الإزالة من المفضلات';
        } else {
            Favorite::create(['user_id' => $userId, 'freelancer_id' => $freelancerId]);
            $message = 'تم الإضافة للمفضلات';
        }

        return back()->with('success', $message);
    }

    /**
     * عملية السحب - مؤمنة بالكامل ومحسنة تقنياً.
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
                // استخدام lockForUpdate لمنع Race Condition
                $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

                if (!$wallet || $wallet->balance < $request->amount) {
                    throw new Exception('رصيدك غير كافٍ لإتمام عملية السحب.');
                }

                $wallet->decrement('balance', $request->amount);

                Transaction::create([
                    'user_id' => $userId,
                    'amount'  => $request->amount,
                    'type'    => 'withdraw',
                    'status'  => 'pending',
                    'payment_method' => strip_tags($request->method),
                    'details' => 'طلب سحب رصيد: ' . strip_tags($request->account_info)
                ]);

                // مسح الكاش فوراً لتحديث الإحصائيات بعد العملية
                Cache::forget("client_dashboard_cache_{$userId}");

                return redirect()->route('client.dashboard')->with('success', 'تم تقديم طلب السحب بنجاح.');
            });
        } catch (Exception $e) {
            Log::error('Withdraw Performance/Security Error: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }
}
