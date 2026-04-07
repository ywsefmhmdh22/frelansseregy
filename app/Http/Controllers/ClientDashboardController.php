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
 * مسؤول عن لوحة تحكم العميل مع تطبيق معايير الأداء العالي (High Performance)
 * وتجنب ثغرات الـ SQL Injection والـ Race Conditions في المعاملات المالية.
 */
class ClientDashboardController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للوحة تحكم العميل - محسنة للأداء العالي.
     * تم تطبيق نظام Caching لمدة 5 دقائق لتقليل الـ Overhead على السيرفر.
     */
    public function index()
    {
        $userId = (int) Auth::id();

        // 1. استخدام الكاش لتخزين البيانات التي لا تتغير كل ثانية (إحصائيات + مشاريع)
        $dashboardData = Cache::remember("client_dashboard_cache_{$userId}", 300, function () use ($userId) {

            // جلب بيانات محددة فقط (Select) وتقليل عدد النتائج (Limit)
            $projects = Project::where('user_id', $userId)
                ->select('id', 'title', 'status', 'admin_status', 'created_at')
                ->withCount('proposals') // حساب عدد العروض مباشرة من الداتابيز
                ->latest()
                ->limit(5)
                ->get();

            // تنفيذ استعلامات تجميعية (Aggregation) لتقليل زمن المعالجة في PHP
            $stats = [
                'total_projects'   => Project::where('user_id', $userId)->count(),
                'pending_projects' => Project::where('user_id', $userId)->where('admin_status', 'pending')->count(),
                'active_projects'  => Project::where('user_id', $userId)->where('admin_status', 'approved')->count(),
                'total_spent'      => (float) Project::where('user_id', $userId)->where('status', 'completed')->sum('final_price'),
            ];

            return compact('projects', 'stats');
        });

        // 2. المحفظة (بيانات لحظية - Real-time) لضمان الدقة المالية التامة
        $wallet = Wallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);

        return view('dashboards.Client Dashboard', [
            'myProjects'    => $dashboardData['projects'],
            'stats'         => $dashboardData['stats'],
            'walletBalance' => $wallet->balance
        ]);
    }

    /**
     * عرض قائمة المشاريع - تم حل مشكلة الأداء باستخدام Pagination.
     */
    public function myProjects()
    {
        // استخدام paginate لتقليل استهلاك الذاكرة وتجنب تحميل آلاف السجلات مرة واحدة
        $myProjects = Project::where('user_id', (int) Auth::id())
            ->select('id', 'title', 'status', 'admin_status', 'final_price', 'created_at')
            ->withCount('proposals')
            ->latest()
            ->paginate(10);

        return view('projects.index', compact('myProjects'));
    }

    /**
     * عرض عروض المشروع - حل مشكلة الـ N+1 Query عبر الـ Nested Eager Loading.
     */
    public function projectOffers($id)
    {
        $userId = (int) Auth::id();

        // تحميل المشروع وعروضه مع بيانات أصحاب العروض في استعلام واحد فقط (Optimized Query)
        $project = Project::where('user_id', $userId)
            ->with(['proposals' => function($query) {
                $query->select('id', 'project_id', 'user_id', 'amount', 'duration', 'description', 'created_at')
                      ->with('user:id,name,image_url') // جلب أعمدة محددة من جدول المستخدمين
                      ->latest();
            }])
            ->findOrFail((int) $id);

        $offers = $project->proposals;

        return view('projects.offers', compact('project', 'offers'));
    }

    /**
     * عرض المحفظة - تحسين جلب المعاملات المالية الضخمة.
     */
    public function wallet()
    {
        $userId = (int) Auth::id();
        $wallet = Wallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);

        // استخدام limit و Select لتجنب إرهاق المتصفح ببيانات تاريخية قديمة جداً
        $transactions = Transaction::where('user_id', $userId)
            ->select('id', 'amount', 'type', 'status', 'payment_method', 'created_at')
            ->latest()
            ->paginate(15); // تم التغيير من limit لـ paginate للأداء الأفضل

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
        $favorites = Favorite::where('user_id', (int) Auth::id())
            ->with('freelancer:id,name,image_url,freelancer_rating') // تحميل بيانات المستقل فوراً
            ->latest()
            ->paginate(12);

        return view('freelancers.favorites', compact('favorites'));
    }

    /**
     * إضافة/حذف مفضلة (Toggle Logic آمن).
     */
    public function toggleFavorite(Request $request)
    {
        $request->validate(['freelancer_id' => 'required|integer|exists:users,id']);

        $userId = (int) Auth::id();
        $freelancerId = (int) $request->freelancer_id;

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
     * عملية السحب - مؤمنة بنظام الـ Database Transactions والـ Row Level Locking.
     */
    public function processWithdraw(Request $request)
    {
        $request->validate([
            'amount'       => 'required|numeric|min:100|max:10000',
            'method'       => 'required|string|max:50',
            'account_info' => 'required|string|max:500',
        ]);

        $userId = (int) Auth::id();

        try {
            // استخدام Transaction لضمان ذرية العملية (Atomic Operation)
            return DB::transaction(function () use ($userId, $request) {

                // استخدام lockForUpdate لمنع هجمات الـ Race Condition (سحب الرصيد مرتين في نفس اللحظة)
                $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

                if (!$wallet || $wallet->balance < (float) $request->amount) {
                    throw new Exception('عذراً، رصيدك الحالي غير كافٍ لإتمام عملية السحب.');
                }

                // تنفيذ الخصم وإنشاء سجل المعاملة
                $wallet->decrement('balance', (float) $request->amount);

                Transaction::create([
                    'user_id'        => $userId,
                    'amount'         => (float) $request->amount,
                    'type'           => 'withdraw',
                    'status'         => 'pending',
                    'payment_method' => strip_tags($request->method),
                    'details'        => 'طلب سحب رصيد: ' . strip_tags($request->account_info)
                ]);

                // تطهير الكاش فوراً لتعكس الإحصائيات التغيير المالي الجديد
                Cache::forget("client_dashboard_cache_{$userId}");

                return redirect()->route('client.dashboard')->with('success', 'تم تسجيل طلب السحب بنجاح وهو قيد المراجعة.');
            });
        } catch (Exception $performanceError) {
            Log::error('Critical Withdraw Failure: ' . $performanceError->getMessage());
            return back()->with('error', $performanceError->getMessage());
        }
    }
}
