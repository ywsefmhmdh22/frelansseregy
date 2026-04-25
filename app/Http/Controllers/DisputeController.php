<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dispute;
use App\Models\Project;
use App\Models\Order; // لو اسم موديل الخدمات المشتراة عندك مختلف غيره هنا
use App\Models\User;
use Illuminate\Support\Facades\Notification;
// تأكد من استدعاء موديل الـ Notification لو هتستخدمه
// use App\Notifications\NewDisputeNotification;

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
                // تأكد إن المشروع مش مكتمل أصلاً
                if ($item->status === 'completed') {
                    return back()->with('error', 'عذراً، لا يمكن فتح نزاع على مشروع مكتمل بالفعل.');
                }
                $item->update(['status' => 'disputed']);
            } else {
                $item = Order::findOrFail($request->item_id);
                // تأكد إن الخدمة مش مكتملة أصلاً
                if ($item->status === 'completed') {
                    return back()->with('error', 'عذراً، لا يمكن فتح نزاع على خدمة مكتملة بالفعل.');
                }
                $item->update(['status' => 'disputed']);
            }

            // 3. تسجيل النزاع في جدول النزاعات (الجدول اللي لسه عاملينه)
            Dispute::create([
                'user_id'         => auth()->id(),
                'disputable_id'   => $item->id,
                'disputable_type' => get_class($item),
                'status'          => 'pending', // بانتظار مراجعة الإدارة
            ]);

            // 4. إرسال إشعار للإدارة (Admin)
            // دي فرضية إن عندك مستخدم بدوره admin، لو عندك نظام مختلف للإشعارات ممكن نعدله
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                // سنقوم بإنشاء الـ Notification في الخطوة القادمة
                // Notification::send($admin, new NewDisputeNotification($item, auth()->user()));
            }

            return back()->with('success', 'تم إرسال طلب التحكيم للإدارة بنجاح. سيتم مراجعة الطلب والتواصل معكم قريباً.');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء معالجة الطلب، يرجى المحاولة لاحقاً.');
        }
    }
}
