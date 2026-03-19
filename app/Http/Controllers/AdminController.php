<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ProjectStatusNotification;

class AdminController extends Controller
{
    /**
     * لوحة التحكم الرئيسية: عرض المستخدمين والمشاريع المعلقة
     */
    public function index()
    {
        $users = User::latest()->get();
        $pendingProjects = Project::where('admin_status', 'pending')->latest()->get();

        return view('admin.dashboard', compact('users', 'pendingProjects'));
    }

    /**
     * 1. السماح للمستخدم ببدء التوثيق (الموافقة المبدئية)
     * تستخدم لمنح المستخدم إذن برؤية "الفورم" ورفع بياناته.
     */
    public function allowVerification(User $user)
    {
        $user->update([
            'verification_status' => 'approved_to_verify'
        ]);

        return back()->with('success', 'تم السماح للمستخدم ' . $user->name . ' ببدء عملية التوثيق.');
    }

    /**
     * 2. التوثيق النهائي وتفعيل الحساب (فتح الموقع بالكامل)
     * تستخدم بعد مراجعة صور الهوية والتأكد منها.
     */
    public function verify(User $user)
    {
        $user->update([
            'is_profile_completed' => 1,      // فك قفل الميدل وير (الحارس)
            'verification_status'  => 'verified' // تحديث الحالة للعرض في البروفايل
        ]);

        return back()->with('success', 'تم توثيق وتفعيل حساب ' . $user->name . ' بنجاح! يمكنه الآن دخول الموقع.');
    }

    /**
     * 3. رفض بيانات التوثيق
     * تستخدم إذا كانت الصور غير واضحة أو البيانات خاطئة.
     */
    public function rejectVerification(User $user)
    {
        $user->update([
            'verification_status' => 'rejected'
        ]);

        return back()->with('danger', 'تم رفض طلب توثيق ' . $user->name . ' (سيظهر له خيار إعادة الرفع).');
    }

    /**
     * عرض تفاصيل المستخدم
     */
    public function show(User $user)
    {
        $unreadMessagesCount = 0;
        $workingProjects = Project::where('freelancer_id', $user->id)
                                    ->whereIn('status', ['in_progress', 'pending_delivery'])
                                    ->get();

        $pendingBalance = Project::where('freelancer_id', $user->id)
                                    ->where('status', 'in_progress')
                                    ->sum('final_price');

         // جوه دالة show في AdminController.php
return view('dashboards.freelancer Dashboard', compact(
    'user',
    'unreadMessagesCount',
    'workingProjects',
    'pendingBalance'
));
    }

    /**
     * الموافقة على نشر مشروع
     */
    public function approveProject($id)
    {
        $project = Project::findOrFail($id);
        $project->update(['admin_status' => 'approved']);
        $project->user->notify(new ProjectStatusNotification($project->title, 'approved'));

        return back()->with('success', 'تمت الموافقة على المشروع بنجاح وإبلاغ العميل.');
    }

    /**
     * حذف مشروع مع إرسال سبب الرفض
     */
    public function deleteProject(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $owner = $project->user;
        $projectTitle = $project->title;
        $reason = $request->notification_message;

        $owner->notify(new ProjectStatusNotification($projectTitle, 'deleted', $reason));
        $project->delete();

        return back()->with('info', 'تم حذف المشروع بنجاح وإرسال سبب الرفض.');
    }

    /**
     * حظر أو إلغاء حظر مستخدم
     */
    public function toggleBan(User $user)
    {
        $user->update([
            'is_banned' => !$user->is_banned
        ]);

        $status = $user->is_banned ? 'محظور' : 'نشط';
        return back()->with('warning', 'تم تغيير حالة حساب ' . $user->name . ' إلى ' . $status);
    }
}
