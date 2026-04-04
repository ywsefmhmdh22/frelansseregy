<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * عرض المشاريع التي تنتظر مراجعة الإدارة (Freelancerig Admin)
     */
    public function pendingProjects()
    {
        // تم التعديل لاستخدام admin_status بناءً على قاعدة البيانات الخاصة بك
        $projects = Project::where('admin_status', 'pending')->latest()->get();

        return view('admin.projects.pending', compact('projects'));
    }

    /**
     * الموافقة على مشروع ونشره
     */
    public function approve($id)
    {
        try {
            $project = Project::findOrFail($id);

            // تحديث حالة الإدارة وحالة المشروع العامة
            $project->update([
                'admin_status' => 'approved',
                'status' => 'open' // لكي يظهر المشروع في قائمة المشاريع المتاحة
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تمت الموافقة على المشروع بنجاح وهو الآن متاح للمستقلين.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الطلب.'
            ], 500);
        }
    }

    /**
     * رفض المشروع (اختياري)
     */
    public function reject($id)
    {
        $project = Project::findOrFail($id);

        $project->update([
            'admin_status' => 'rejected'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم رفض المشروع بنجاح.'
        ]);
    }
}
