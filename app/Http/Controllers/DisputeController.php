<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dispute;
use App\Models\Project;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class DisputeController extends Controller
{
    public function store(Request $request)
    {
        // 1. التحقق من البيانات القادمة
        $request->validate([
            'item_id' => 'required',
            'type'    => 'required|in:project,service',
        ]);

        try {
            // 2. تحديد نوع العنصر وتغيير حالته
            if ($request->type === 'project') {
                $item = Project::findOrFail($request->item_id);
                if ($item->status === 'completed') {
                    return back()->with('error', 'عذراً، لا يمكن فتح نزاع على مشروع مكتمل بالفعل.');
                }
                $item->update(['status' => 'disputed']);
                $freelancerId = $item->freelancer_id; // جلب المستقل من المشروع
            } else {
                $item = Order::findOrFail($request->item_id);
                if ($item->status === 'completed') {
                    return back()->with('error', 'عذراً، لا يمكن فتح نزاع على خدمة مكتملة بالفعل.');
                }
                $item->update(['status' => 'disputed']);
                $freelancerId = $item->freelancer_id; // جلب المستقل من الطلب
            }

            // 3. تسجيل النزاع في جدول النزاعات مع إضافة freelancer_id
            Dispute::create([
                'user_id'         => auth()->id(), // العميل
                'freelancer_id'   => $freelancerId, // المستقل (تم إضافته هنا لمنع الخطأ في العرض)
                'disputable_id'   => $item->id,
                'disputable_type' => get_class($item),
                'status'          => 'pending',
            ]);

            // 4. إرسال إشعار للإدارة (Admin)
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                // يمكنك تفعيل الإشعارات هنا لاحقاً
            }

            return back()->with('success', 'تم إرسال طلب التحكيم للإدارة بنجاح. سيتم مراجعة الطلب والتواصل معكم قريباً.');

        } catch (\Exception $e) {
            // للتصحيح أثناء التطوير يمكنك إزالة التعليق أدناه لمعرفة سبب الخطأ
            // dd($e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء معالجة الطلب، يرجى المحاولة لاحقاً.');
        }
    }
}
