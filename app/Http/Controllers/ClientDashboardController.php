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
 * مسؤول عن لوحة تحكم العميل مع دعم تعدد العملات (EGP/USD)
 * تم الإصلاح: جعل العملة ديناميكية بالكامل بناءً على بيانات قاعدة البيانات.
 */
class ClientDashboardController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للوحة تحكم العميل.
     */
    public function index()
    {
        $userId = (int) Auth::id();

        // 1. استخدام الكاش لتقليل الضغط على قاعدة البيانات (أداء عالٍ)
        $dashboardData = Cache::remember("client_dashboard_cache_{$userId}", 300, function () use ($userId) {

            $projects = Project::where('user_id', $userId)
                ->select('id', 'title', 'status', 'admin_status', 'created_at', 'price', 'currency', 'final_price')
                ->withCount('proposals')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($project) {
                    // الإصلاح: تمرير العملة المخزنة في قاعدة البيانات بدلاً من فرض EGP
                    $project->formatted_price = $this->formatCurrency($project->final_price ?? $project->price, $project->currency);
                    return $project;
                });

            $stats = [
                'total_projects'   => Project::where('user_id', $userId)->count(),
                'pending_projects' => Project::where('user_id', $userId)->where('admin_status', 'pending')->count(),
                'active_projects'  => Project::where('user_id', $userId)->where('admin_status', 'approved')->count(),
                // تنبيه: Sum هنا يجمع أرقام مجردة، يفضل مستقبلاً توحيد العملة في جدول منفصل للإحصائيات
                'total_spent'      => (float) Project::where('user_id', $userId)->where('status', 'completed')->sum('final_price'),
            ];

            return compact('projects', 'stats');
        });

        // 2. المحفظة (Real-time) لضمان الدقة المالية
        $wallet = Wallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);

        // الإصلاح الجذري: سحب العملة من إعدادات المحفظة أو المشروع، وعدم تثبيت 'EGP'
        // إذا كان جدول المحفظة لا يحتوي على عمود currency، سنعتمد على العملة الافتراضية للنظام
        $walletCurrency = $wallet->currency ?? 'EGP';
        $formattedWalletBalance = $this->formatCurrency($wallet->balance, $walletCurrency);

        return view('dashboards.Client Dashboard', [
            'myProjects'       => $dashboardData['projects'],
            'stats'            => $dashboardData['stats'],
            'walletBalance'    => $wallet->balance,
            'formattedBalance' => $formattedWalletBalance
        ]);
    }

    /**
     * عرض قائمة المشاريع مع دعم Pagination وتنسيق العملات.
     */
    public function myProjects()
    {
        $myProjects = Project::where('user_id', (int) Auth::id())
            ->select('id', 'title', 'status', 'admin_status', 'final_price', 'price', 'currency', 'created_at')
            ->withCount('proposals')
            ->latest()
            ->paginate(10);

        // تحويل البيانات لإضافة التنسيق المالي ديناميكياً
        $myProjects->getCollection()->transform(function ($project) {
            $project->formatted_price = $this->formatCurrency($project->final_price ?? $project->price, $project->currency);
            return $project;
        });

        return view('projects.index', compact('myProjects'));
    }

    /**
     * ميثود مساعدة (Private Helper) لتنسيق العملة.
     * الإصلاح: معالجة العملة المكتوبة بحروف صغيرة أو القيم الفارغة.
     */
    private function formatCurrency($amount, $currency)
    {
        $amount = number_format((float)$amount, 2);
        // تأمين: تحويل العملة لحروف كبيرة وحذف أي مسافات زائدة
        $currency = strtoupper(trim($currency ?? 'EGP'));

        switch ($currency) {
            case 'USD':
                return "$" . $amount;
            case 'EGP':
                return $amount . " ج.م";
            default:
                // في حال وجود عملة غير معروفة، نعرض الكود الخاص بها بجانب المبلغ
                return $amount . " " . $currency;
        }
    }

    /**
     * عرض عروض المشروع - Eager Loading لرفع الأداء.
     */
    public function projectOffers($id)
    {
        $userId = (int) Auth::id();

        $project = Project::where('user_id', $userId)
            ->with(['proposals' => function($query) {
                $query->select('id', 'project_id', 'user_id', 'amount', 'duration', 'description', 'created_at')
                      ->with('user:id,name,image_url')
                      ->latest();
            }])
            ->findOrFail((int) $id);

        return view('projects.offers', compact('project'));
    }

    /**
     * عرض المحفظة والمعاملات المالية.
     */
    public function wallet()
    {
        $userId = (int) Auth::id();
        $wallet = Wallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);

        $transactions = Transaction::where('user_id', $userId)
            ->select('id', 'amount', 'type', 'status', 'payment_method', 'created_at')
            ->latest()
            ->paginate(15);

        return view('wallet.index', [
            'walletBalance' => $wallet->balance,
            'transactions'  => $transactions
        ]);
    }

    /**
     * عرض المفضلات.
     */
    public function favorites()
    {
        $favorites = Favorite::where('user_id', (int) Auth::id())
            ->with('freelancer:id,name,image_url,freelancer_rating')
            ->latest()
            ->paginate(12);

        return view('freelancers.favorites', compact('favorites'));
    }

    /**
     * إضافة/حذف من المفضلات.
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
     * عملية السحب مؤمنة بـ Database Transactions و Row Level Locking.
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
            return DB::transaction(function () use ($userId, $request) {
                // استخدام lockForUpdate لمنع الـ Double Spending
                $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

                if (!$wallet || $wallet->balance < (float) $request->amount) {
                    throw new Exception('عذراً، رصيدك الحالي غير كافٍ لإتمام عملية السحب.');
                }

                $wallet->decrement('balance', (float) $request->amount);

                Transaction::create([
                    'user_id'         => $userId,
                    'amount'          => (float) $request->amount,
                    'type'            => 'withdraw',
                    'status'          => 'pending',
                    'payment_method'  => strip_tags($request->method),
                    'details'         => 'طلب سحب رصيد: ' . strip_tags($request->account_info)
                ]);

                // مسح الكاش لتحديث البيانات فوراً للعميل
                Cache::forget("client_dashboard_cache_{$userId}");

                return redirect()->route('client.dashboard')->with('success', 'تم تسجيل طلب السحب بنجاح وهو قيد المراجعة.');
            });
        } catch (Exception $e) {
            Log::error('Critical Withdraw Failure for User ' . $userId . ': ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }
}
