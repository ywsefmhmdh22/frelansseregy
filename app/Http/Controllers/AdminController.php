<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ProjectStatusNotification; // استدعاء ملف الإشعار الذي قمنا بإنشائه

class AdminController extends Controller
{
    /**
     * لوحة التحكم الرئيسية: عرض المستخدمين والمشاريع المعلقة
     */
    public function index()
    {
        // جلب كل المستخدمين مع ترتيبهم (الأحدث أولاً)
        $users = User::latest()->get();

        // جلب المشاريع التي تنتظر مراجعتك فقط (admin_status = pending)
        $pendingProjects = Project::where('admin_status', 'pending')->latest()->get();

        return view('admin.dashboard', compact('users', 'pendingProjects'));
    }

    /**
     * عرض تفاصيل المستخدم (البروفايل الكامل للأدمن)
     * تم تحديث هذه الدالة لتمرير البيانات المطلوبة للواجهة الجديدة
     */
    public function show(User $user)
    {
        // 1. حساب عدد الرسائل غير المقروءة (يمكنك ربطها بجدول الرسائل لاحقاً)
        $unreadMessagesCount = 0;

        // 2. جلب المشاريع قيد التنفيذ لهذا المستقل حصراً لعرضها في الجدول
        $workingProjects = Project::where('freelancer_id', $user->id)
                                    ->whereIn('status', ['in_progress', 'pending_delivery'])
                                    ->get();

        // 3. حساب إجمالي الأرباح المعلقة (المشاريع التي ما زالت قيد التنفيذ)
        $pendingBalance = Project::where('freelancer_id', $user->id)
                                    ->where('status', 'in_progress')
                                    ->sum('final_price');

        // إرسال المستخدم مع المتغيرات الإضافية للـ View
        return view('dashboards.freelancer Dashboard', compact(
            'user',
            'unreadMessagesCount',
            'workingProjects',
            'pendingBalance'
        ));
    }

    /**
     * الموافقة على نشر مشروع + إرسال إشعار فوري للعميل
     */
    public function approveProject($id)
    {
        $project = Project::findOrFail($id);

        // تحديث الحالة ليصبح المشروع مرئياً للجميع
        $project->update(['admin_status' => 'approved']);

        // إرسال إشعار للعميل: "تمت الموافقة"
        $project->user->notify(new ProjectStatusNotification($project->title, 'approved'));

        return back()->with('success', 'تمت الموافقة على المشروع بنجاح وإبلاغ العميل.');
    }

    /**
     * حذف مشروع + إرسال سبب الرفض كإشعار للعميل
     */
    public function deleteProject(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $owner = $project->user;
        $projectTitle = $project->title;

        // استلام سبب الحذف من "الفورم" في الداشبورد
        $reason = $request->notification_message;

        // إرسال إشعار للعميل يوضح سبب الحذف قبل مسح البيانات
        $owner->notify(new ProjectStatusNotification($projectTitle, 'deleted', $reason));

        // حذف المشروع نهائياً من قاعدة البيانات
        $project->delete();

        return back()->with('info', 'تم حذف المشروع بنجاح وإرسال سبب الرفض في إشعار للعميل.');
    }

    /**
     * حظر أو إلغاء حظر مستخدم (Toggle Ban)
     */
    public function toggleBan(User $user)
    {
        $user->update([
            'is_banned' => !$user->is_banned
        ]);

        $status = $user->is_banned ? 'محظور' : 'نشط';
        return back()->with('warning', 'تم تغيير حالة حساب ' . $user->name . ' إلى ' . $status);
    }

    /**
     * توثيق حساب المستخدم (ID Verification)
     */
    public function verify(User $user)
    {
        $user->update(['verification_status' => 'verified']);

        // (اختياري) يمكنك أيضاً إرسال إشعار هنا للترحيب بالمستخدم الموثق

        return back()->with('success', 'تم توثيق حساب ' . $user->name . ' بنجاح!');
    }
}
