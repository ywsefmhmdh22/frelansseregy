<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ProjectStatusNotification;

class AdminController extends Controller
{
    /**
     * لوحة التحكم الرئيسية: عرض شامل لكل شيء
     */
    public function index()
    {
        // جلب كل المستخدمين
        $users = User::latest()->get();

        // المشاريع التي تنتظر موافقتك لنشرها
        $pendingProjects = Project::where('admin_status', 'pending')->latest()->get();

        // جلب النزاعات
        $disputes = Project::where('status', 'disputed')->with(['user', 'freelancer'])->get();

        // الرادار المالي: آخر 10 عمليات
        $transactions = Transaction::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact('users', 'pendingProjects', 'transactions', 'disputes'));
    }

    /**
     * 1. قبول التسجيل المبدئي
     */
    public function approveUser(User $user)
    {
        $user->update([
            'verification_status' => 'pending',
        ]);

        return back()->with('success', 'تم قبول انضمام ' . $user->name . ' للمنصة. يمكنه الآن البدء بإجراءات التوثيق.');
    }

    /**
     * 2. التوثيق النهائي (مراجعة الهوية)
     */
    public function verify(User $user)
    {
        $user->update([
            'is_profile_completed' => 1,
            'verification_status'  => 'verified'
        ]);

        return back()->with('success', 'تم توثيق هوية ' . $user->name . ' بنجاح وأصبح حسابه موثقاً بالكامل.');
    }

    /**
     * 3. رفض التوثيق
     */
    public function rejectVerification(User $user)
    {
        $user->update([
            'verification_status' => 'rejected'
        ]);

        return back()->with('danger', 'تم رفض أوراق التوثيق لـ ' . $user->name . '.');
    }

    /**
     * 4. نظام "عين الصقر" (التقمص)
     */
    public function impersonate(User $user)
    {
        session()->put('admin_id', Auth::id());
        Auth::login($user);
        return redirect('/')->with('info', 'أنت الآن تتصفح المنصة بهوية: ' . $user->name);
    }

    /**
     * 5. حظر نهائي (المتوافق مع عداد الـ Blade)
     */
    public function ban(User $user)
    {
        $user->update([
            'is_banned' => !$user->is_banned
        ]);

        $status = $user->is_banned ? 'محظور نهائياً' : 'نشط الآن';
        return back()->with('warning', 'حالة حساب ' . $user->name . ' أصبحت: ' . $status);
    }

    /**
     * 6. فتح صفحة التعديل
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * 7. تحديث بيانات المستخدم
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'balance' => 'required|numeric',
        ]);

        $user->update($request->only('name', 'email', 'balance', 'role'));

        return redirect()->route('admin.dashboard')->with('success', 'تم تحديث بيانات العميل بنجاح.');
    }

    /**
     * 8. سجل عمليات مستخدم محدد (لزر الـ 3 نقاط)
     */
    public function userTransactions(User $user)
    {
        $transactions = Transaction::where('user_id', $user->id)->latest()->paginate(20);
        return view('admin.finance.user_report', compact('user', 'transactions'));
    }

    /**
     * نظام التحكيم المالي
     */
    public function disputesIndex()
    {
        $disputes = Project::where('status', 'disputed')->with(['user', 'freelancer'])->get();
        return view('admin.disputes.index', compact('disputes'));
    }

    /**
     * الرادار المالي التفصيلي
     */
    public function financeRadar()
    {
        $allTransactions = Transaction::with('user')->latest()->paginate(50);
        return view('admin.finance.index', compact('allTransactions'));
    }

    /**
     * الموافقة على نشر مشروع
     */
    public function approveProject($id)
    {
        $project = Project::findOrFail($id);
        $project->update(['admin_status' => 'approved']);

        if ($project->user) {
            $project->user->notify(new ProjectStatusNotification($project->title, 'approved'));
        }

        return back()->with('success', 'تمت الموافقة على المشروع بنجاح.');
    }

    /**
     * حذف مشروع
     */
    public function deleteProject(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $owner = $project->user;
        $projectTitle = $project->title;
        $reason = $request->notification_message;

        if ($owner) {
            $owner->notify(new ProjectStatusNotification($projectTitle, 'deleted', $reason));
        }

        $project->delete();

        return back()->with('info', 'تم حذف المشروع وإخطار العميل بالسبب.');
    }

    /**
     * عرض تفاصيل المستخدم
     */
    public function show(User $user)
    {
        $workingProjects = Project::where('freelancer_id', $user->id)
                                    ->whereIn('status', ['in_progress', 'pending_delivery'])
                                    ->get();

        $balance = $user->balance;

        return view('admin.user-details', compact('user', 'workingProjects', 'balance'));
    }
}
