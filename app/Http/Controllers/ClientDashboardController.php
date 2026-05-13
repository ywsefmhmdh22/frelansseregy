<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Favorite;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * Class ClientDashboardController
 * مسؤول عن لوحة تحكم العميل مع دعم Laravel Cloud لتخزين الصور.
 */
class ClientDashboardController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للوحة تحكم العميل.
     */
    public function index()
    {
        $userId = (int) Auth::id();

        // جلب المشاريع مع تنسيق السعر
        $projects = Project::where('user_id', $userId)
            ->select('id', 'title', 'status', 'admin_status', 'created_at', 'price', 'currency', 'final_price')
            ->withCount('proposals')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($project) {
                $project->formatted_price = $this->formatCurrency($project->final_price ?? $project->price, $project->currency);
                return $project;
            });

        // الإحصائيات مع الكاش
        $stats = Cache::remember("client_stats_summary_{$userId}", 600, function () use ($userId) {
            $rawStats = Project::where('user_id', $userId)
                ->selectRaw("
                    COUNT(*) as total_projects,
                    SUM(CASE WHEN admin_status = 'pending' THEN 1 ELSE 0 END) as pending_projects,
                    SUM(CASE WHEN admin_status = 'approved' THEN 1 ELSE 0 END) as active_projects,
                    SUM(CASE WHEN status = 'completed' THEN final_price ELSE 0 END) as total_spent
                ")
                ->first();

            return [
                'total_projects'   => (int) $rawStats->total_projects,
                'pending_projects' => (int) $rawStats->pending_projects,
                'active_projects'  => (int) $rawStats->active_projects,
                'total_spent'      => (float) $rawStats->total_spent,
            ];
        });

        $wallet = Wallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);
        $walletCurrency = $wallet->currency ?? 'EGP';
        $formattedWalletBalance = $this->formatCurrency($wallet->balance, $walletCurrency);

        return view('dashboards.Client Dashboard', [
            'myProjects'       => $projects,
            'stats'            => $stats,
            'walletBalance'    => $wallet->balance,
            'formattedBalance' => $formattedWalletBalance
        ]);
    }

    /**
     * تحديث صورة البروفايل ورفعها على Laravel Cloud (S3) حصرياً.
     * تم التعديل ليدعم استجابة JSON المتوافقة مع Axios في الـ Blade.
     */
    public function updateImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // تم رفع الحد لـ 5 ميجا ليطابق صفحة الإكمال
        ]);

        try {
            $user = Auth::user();
            $disk = 's3';

            // 1. حذف الصورة القديمة من السحاب إذا كانت موجودة
            if ($user->profile_image) {
                Storage::disk($disk)->delete($user->profile_image);
            }

            // 2. الرفع الجديد على لارفل كلاود (S3) في نفس مجلد صفحة الإكمال
            $newAvatarPath = $request->file('profile_image')->store('profile_images/avatars', [
                'disk' => $disk,
                'visibility' => 'public'
            ]);

            // 3. تحديث مسار الصورة في قاعدة البيانات
            $user->update(['profile_image' => $newAvatarPath]);

            // 4. الاستجابة: إذا كان الطلب أياكس (Axios) نرسل JSON، وإلا نرجع للخلف
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث الصورة الشخصية على السحاب بنجاح!',
                    'path' => Storage::disk($disk)->url($newAvatarPath)
                ]);
            }

            return back()->with('success', 'تم تحديث الصورة الشخصية بنجاح!');

        } catch (Exception $e) {
            Log::error('Laravel Cloud Avatar Upload Error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء الرفع للسحابة.'
                ], 500);
            }

            return back()->with('error', 'حدث خطأ أثناء الرفع إلى Laravel Cloud.');
        }
    }

    /**
     * عرض المشاريع مع دعم الترقيم
     */
    public function myProjects()
    {
        $myProjects = Project::where('user_id', (int) Auth::id())
            ->select('id', 'title', 'status', 'admin_status', 'final_price', 'price', 'currency', 'created_at')
            ->withCount('proposals')
            ->latest()
            ->paginate(10);

        $myProjects->getCollection()->transform(function ($project) {
            $project->formatted_price = $this->formatCurrency($project->final_price ?? $project->price, $project->currency);
            return $project;
        });

        return view('projects.index', compact('myProjects'));
    }

    /**
     * تنسيق العملات
     */
    private function formatCurrency($amount, $currency)
    {
        $amount = number_format((float)$amount, 2);
        $currency = strtoupper(trim($currency ?? 'EGP'));

        switch ($currency) {
            case 'USD': return "$" . $amount;
            case 'EGP': return $amount . " ج.م";
            default: return $amount . " " . $currency;
        }
    }

    /**
     * عرض عروض المشاريع
     */
    public function projectOffers($id)
    {
        $userId = (int) Auth::id();

        $project = Project::where('user_id', $userId)
            ->with(['proposals' => function($query) {
                $query->select('id', 'project_id', 'user_id', 'amount', 'duration', 'description', 'created_at')
                      ->with('user:id,name,profile_image')
                      ->latest();
            }])
            ->findOrFail((int) $id);

        return view('projects.offers', compact('project'));
    }

    /**
     * عرض المحفظة
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
     * المفضلات
     */
    public function favorites()
    {
        $favorites = Favorite::where('user_id', (int) Auth::id())
            ->with('freelancer:id,name,profile_image,freelancer_rating')
            ->latest()
            ->paginate(12);

        return view('freelancers.favorites', compact('favorites'));
    }

    /**
     * إضافة/حذف المفضلات
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
     * عملية سحب الرصيد
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

                Cache::forget("client_stats_summary_{$userId}");

                return redirect()->route('client.dashboard')->with('success', 'تم تسجيل طلب السحب بنجاح وهو قيد المراجعة.');
            });
        } catch (Exception $e) {
            Log::error('Critical Withdraw Failure for User ' . $userId . ': ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }
}
