<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Favorite;
use App\Models\Wallet; // إضافة موديل المحفظة
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientDashboardController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للوحة تحكم العميل بالإحصائيات الحقيقية
     */
    public function index()
    {
        $userId = Auth::id();
        $user = Auth::user();

        // جلب المشاريع مع عدد العروض لكل مشروع
        $myProjects = Project::withCount('proposals') // عشان رقم العروض
            ->with('proposals.user') // عشان تفاصيل العروض وأصحابها
            ->where('user_id', auth()->id())
            ->get();

        // حساب الإحصائيات
        $stats = [
            'total_projects'   => Project::where('user_id', $userId)->count(),
            'pending_projects' => Project::where('user_id', $userId)->where('admin_status', 'pending')->count(),
            'active_projects'  => Project::where('user_id', $userId)->where('admin_status', 'approved')->count(),
            'total_spent'      => Project::where('user_id', $userId)->sum('price') ?? 0,
        ];

        // جلب الرصيد من جدول المحفظة المرتبط باليوزر
        $walletBalance = $user->wallet->balance ?? 0;

        return view('dashboards.Client Dashboard', compact('myProjects', 'stats', 'walletBalance'));
    }

    /**
     * عرض قائمة بكل مشاريع العميل
     */
    public function myProjects()
    {
        $myProjects = Project::where('user_id', Auth::id())
            ->withCount('proposals as proposals_count')
            ->latest()
            ->paginate(10);

        return view('projects.index', compact('myProjects'));
    }

    /**
     * عرض العروض المقدمة لمشروع معين
     */
    public function projectOffers($id)
    {
        $project = Project::where('user_id', Auth::id())
            ->with('proposals.user')
            ->findOrFail($id);

        $offers = $project->proposals;

        return view('projects.offers', compact('project', 'offers'));
    }

    /**
     * عرض المحفظة
     */
    public function wallet()
    {
        // جلب الرصيد من علاقة المحفظة
        $walletBalance = Auth::user()->wallet->balance ?? 0;

        return view('wallet.index', compact('walletBalance'));
    }

    /**
     * عرض المستقلين المفضلين
     */
    public function favorites()
    {
        $favorites = Favorite::where('user_id', Auth::id())
            ->with('freelancer')
            ->latest()
            ->get();

        return view('freelancers.favorites', compact('favorites'));
    }

    /**
     * إضافة أو حذف مستقل من المفضلات
     */
    public function toggleFavorite(Request $request)
    {
        $freelancerId = $request->freelancer_id;
        $userId = Auth::id();

        $favorite = Favorite::where('user_id', $userId)
            ->where('freelancer_id', $freelancerId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return back()->with('success', 'تمت الإزالة من المفضلات');
        } else {
            Favorite::create([
                'user_id' => $userId,
                'freelancer_id' => $freelancerId
            ]);
            return back()->with('success', 'تم الإضافة للمفضلات');
        }
    }

    /**
     * التذاكر والدعم الفني
     */
    public function tickets()
    {
        return view('support.index');
    }

    /**
     * شحن الرصيد (المرحلة الثالثة)
     * عرض صفحة اختيار مبلغ الشحن
     */
    public function deposit()
    {
        // تأكد أن ملف الـ view موجود في resources/views/wallet/deposit.blade.php
        return view('wallet.deposit');
    }

    /**
     * عرض صفحة سحب الرصيد
     */
    public function withdraw()
    {
        return view('wallet.withdraw');
    }

    /**
     * تنفيذ طلب السحب
     */
    public function processWithdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'method' => 'required',
            'account_info' => 'required|string',
        ]);

        $user = auth()->user();

        if ($user->wallet->balance < $request->amount) {
            return back()->with('error', 'رصيدك غير كافٍ.');
        }

        DB::transaction(function () use ($user, $request) {

            // 1. خصم من المحفظة فوراً
            $user->wallet->decrement('balance', $request->amount);

            // 2. تسجيل العملية كـ "سحب معلق"
            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'type' => 'withdraw',
                'status' => 'pending', // هيفضل معلق لحد ما الأدمن يوافق
                'payment_method' => $request->method,
                'details' => 'طلب سحب إلى: ' . $request->account_info
            ]);
        });

        return redirect()->route('client.dashboard')
            ->with('success', 'تم تقديم طلب السحب بنجاح، سيتم التحويل خلال 24 ساعة.');
    }
}
