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

class ClientDashboardController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للوحة تحكم العميل - تحسين الأداء (Performance Boost)
     */
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        // 1. جلب آخر 5 مشاريع فقط (لتحسين السرعة) بدل جلب كل التاريخ
        $myProjects = Project::withCount('proposals')
            ->where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get();

        // 2. حساب الإحصائيات بطريقة أسرع (Direct Aggregation)
        $stats = [
            'total_projects'   => Project::where('user_id', $userId)->count(),
            'pending_projects' => Project::where('user_id', $userId)->where('admin_status', 'pending')->count(),
            'active_projects'  => Project::where('user_id', $userId)->where('admin_status', 'approved')->count(),
            'total_spent'      => Project::where('user_id', $userId)->where('status', 'completed')->sum('final_price') ?? 0,
        ];

        // 3. تأمين جلب الرصيد
        $wallet = Wallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);
        $walletBalance = $wallet->balance;

        return view('dashboards.Client Dashboard', compact('myProjects', 'stats', 'walletBalance'));
    }

    /**
     * عرض قائمة بكل مشاريع العميل (مع استخدام الـ Pagination لحل مشكلة الأداء)
     */
    public function myProjects()
    {
        $myProjects = Project::where('user_id', Auth::id())
            ->withCount('proposals')
            ->latest()
            ->paginate(10); // عرض 10 مشاريع في كل صفحة

        return view('projects.index', compact('myProjects'));
    }

    /**
     * عرض العروض المقدمة لمشروع معين
     */
    public function projectOffers($id)
    {
        $project = Project::where('user_id', Auth::id())
            ->with(['proposals' => function($q) {
                $q->with('user')->latest();
            }])
            ->findOrFail($id);

        $offers = $project->proposals;

        return view('projects.offers', compact('project', 'offers'));
    }

    /**
     * عرض المحفظة
     */
    public function wallet()
    {
        $wallet = Wallet::firstOrCreate(['user_id' => Auth::id()], ['balance' => 0]);
        $walletBalance = $wallet->balance;

        // جلب آخر المعاملات المالية للشفافية
        $transactions = Transaction::where('user_id', Auth::id())->latest()->limit(10)->get();

        return view('wallet.index', compact('walletBalance', 'transactions'));
    }

    /**
     * عرض المستقلين المفضلين (Pagination)
     */
    public function favorites()
    {
        $favorites = Favorite::where('user_id', Auth::id())
            ->with('freelancer')
            ->latest()
            ->paginate(12); // تحسين الأداء لو القائمة كبيرة

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

        $favorite = Favorite::where('user_id', $userId)
            ->where('freelancer_id', $freelancerId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $msg = 'تمت الإزالة من المفضلات';
        } else {
            Favorite::create([
                'user_id' => $userId,
                'freelancer_id' => $freelancerId
            ]);
            $msg = 'تم الإضافة للمفضلات';
        }

        return back()->with('success', $msg);
    }

    /**
     * تنفيذ طلب السحب (مع حماية برمجية كاملة)
     */
    public function processWithdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'method' => 'required|string',
            'account_info' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

        if ($wallet->balance < $request->amount) {
            return back()->with('error', 'رصيدك غير كافٍ لإتمام عملية السحب.');
        }

        try {
            DB::transaction(function () use ($user, $wallet, $request) {
                // 1. الخصم
                $wallet->decrement('balance', $request->amount);

                // 2. تسجيل العملية
                Transaction::create([
                    'user_id' => $user->id,
                    'amount' => $request->amount,
                    'type' => 'withdraw',
                    'status' => 'pending',
                    'payment_method' => strip_tags($request->method),
                    'details' => 'طلب سحب إلى: ' . strip_tags($request->account_info)
                ]);
            });

            return redirect()->route('client.dashboard')
                ->with('success', 'تم تقديم طلب السحب بنجاح، سيتم المراجعة خلال 24 ساعة.');
        } catch (\Exception $e) {
            Log::error('Withdraw Process Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء معالجة الطلب.');
        }
    }
}
