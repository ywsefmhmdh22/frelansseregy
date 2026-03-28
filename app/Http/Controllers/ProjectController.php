<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Review; // تأكد من استدعاء موديل التقييمات
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * 1. عرض صفحة إضافة مشروع جديد
     */
    public function create(Request $request)
    {
        $type = $request->query('type', 'normal');
        return view('projects.create', compact('type'));
    }

    /**
     * 2. حفظ المشروع (دعم الملفات المتعددة المخزنة كـ JSON)
     */
    public function store(Request $request)
    {
        // التحقق من البيانات
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'required|string|min:20', // بيانات CKEditor
            'price'         => 'required|numeric|min:1',
            'currency'      => 'required|string|max:10',
            'duration'      => 'required|string',
            'image_url'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // الصورة الرئيسية
            'attachments.*' => 'nullable|file|mimes:pdf,zip,rar,doc,docx,jpg,png|max:10240', // المرفقات
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // معالجة الصورة الرئيسية
                $imagePath = null;
                if ($request->hasFile('image_url')) {
                    $imagePath = $request->file('image_url')->store('projects/covers', 'public');
                }

                // معالجة الملفات المرفقة
                $attachmentsPaths = [];
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $attachmentsPaths[] = $file->store('projects/attachments', 'public');
                    }
                }

                // إنشاء المشروع
                $project = Project::create([
                    'user_id'      => Auth::id(),
                    'title'        => $request->title,
                    'description'  => $request->description,
                    'price'        => $request->price,
                    'currency'     => $request->currency,
                    'duration'     => $request->duration,
                    'image_url'    => $imagePath,
                    'attachments'  => $attachmentsPaths,
                    'type'         => $request->input('type', 'normal'),
                    'status'       => 'open',
                    'admin_status' => 'pending',
                ]);

                return redirect()->route('client.dashboard')->with('success', 'تم إرسال مشروعك للمراجعة بنجاح!');
            });
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حفظ المشروع: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * 3. عرض تفاصيل المشروع
     */
    public function show(Project $project)
    {
        $project->load(['proposals.user', 'user', 'freelancer']);
        return view('projects.show', compact('project'));
    }

    /**
     * 4. توظيف مستقل (اختيار المنفذ مع فحص الرصيد)
     */
    public function assignFreelancer(Project $project, $proposalId)
    {
        if (Auth::id() !== $project->user_id) {
            return back()->with('error', 'غير مسموح لك بهذا الإجراء.');
        }

        $proposal = Proposal::findOrFail($proposalId);
        $client = Auth::user();
        $requiredAmount = $proposal->amount ?? $proposal->price;

        if (!$client->wallet || $client->wallet->balance < $requiredAmount) {
            $shortage = $requiredAmount - ($client->wallet->balance ?? 0);
            return back()->with('error', 'رصيدك غير كافٍ. تحتاج لشحن ' . $shortage . ' ج.م إضافية لتوظيف هذا المستقل.');
        }

        DB::transaction(function () use ($project, $proposal, $client, $requiredAmount) {
            // خصم من محفظة العميل
            $client->wallet->decrement('balance', $requiredAmount);

            // تسجيل حركة الخصم
            Transaction::create([
                'user_id' => $client->id,
                'amount'  => $requiredAmount,
                'type'    => 'withdraw',
                'status'  => 'completed',
                'details' => 'خصم رصيد لتوظيف مستقل لمشروع: ' . $project->title,
            ]);

            // تحديث حالة المشروع
            $project->update([
                'freelancer_id' => $proposal->user_id,
                'status'        => 'in_progress',
                'final_price'   => $requiredAmount,
            ]);

            // تحديث حالة العرض
            $proposal->update(['status' => 'accepted']);
        });

        return back()->with('success', 'تم التوظيف بنجاح! تم تأمين المبلغ في ضمان الموقع حتى الاستلام.');
    }

    /**
     * 5. طلب تسليم المشروع (يقوم به المستقل)
     */
    public function requestDelivery(Project $project)
    {
        if (Auth::id() !== $project->freelancer_id) {
            return back()->with('error', 'أنت لست المنفذ لهذا المشروع.');
        }

        $project->update(['status' => 'pending_delivery']);
        return back()->with('success', 'تم إرسال طلب التسليم لصاحب المشروع.');
    }

    /**
     * 6. صفحة مراجعة وتقييم المشروع
     */
    public function reviewPage(Project $project)
    {
        if (auth()->id() != $project->user_id || $project->status != 'pending_delivery') {
            return redirect()->route('projects.show', $project->id)->with('error', 'لا يمكنك الوصول لهذه الصفحة حالياً.');
        }
        return view('projects.review', compact('project'));
    }

    /**
     * 7. استلام المشروع وتحويل الأموال للمستقل مع حفظ التقييم التفصيلي
     */
    public function completeProject(Request $request, Project $project)
    {
        // التأكد من الصلاحية
        if (Auth::id() !== $project->user_id) {
            return back()->with('error', 'غير مسموح لك بهذا الإجراء.');
        }

        // التحقق من مدخلات التقييم (المعايير الأربعة + التعليق)
        $request->validate([
            'rating_quality'       => 'required|integer|min:1|max:5',
            'rating_time'          => 'required|integer|min:1|max:5',
            'rating_behavior'      => 'required|integer|min:1|max:5',
            'rating_communication' => 'required|integer|min:1|max:5',
            'review_comment'       => 'required|string|min:10|max:1000',
        ]);

        DB::transaction(function () use ($project, $request) {
            $freelancer = User::findOrFail($project->freelancer_id);
            $amount = $project->final_price ?? $project->price;

            // حساب متوسط التقييم النهائي
            $avgRating = ($request->rating_quality + $request->rating_time +
                          $request->rating_behavior + $request->rating_communication) / 4;

            // أ- تحويل المبلغ لمحفظة المستقل
            if ($freelancer->wallet) {
                $freelancer->wallet->increment('balance', $amount);

                Transaction::create([
                    'user_id' => $freelancer->id,
                    'amount'  => $amount,
                    'type'    => 'deposit',
                    'status'  => 'completed',
                    'details' => 'أرباح استلام مشروع: ' . $project->title,
                ]);
            }

            // ب- إنشاء سجل التقييم في جدول الـ reviews
            Review::create([
                'project_id'           => $project->id,
                'freelancer_id'        => $project->freelancer_id,
                'user_id'              => Auth::id(),
                'rating_quality'       => $request->rating_quality,
                'rating_time'          => $request->rating_time,
                'rating_behavior'      => $request->rating_behavior,
                'rating_communication' => $request->rating_communication,
                'rating'               => $avgRating, // التقييم الكلي
                'comment'              => $request->review_comment,
            ]);

            // ج- تحديث حالة المشروع والتقييم العام في ملف المستقل
            $project->update(['status' => 'completed']);

            $freelancer->update([
                'freelancer_rating' => $avgRating,
            ]);

            // د- إضافة نقاط التميز (اختياري)
            // $freelancer->increment('points', 8);
        });

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'تم استلام المشروع بنجاح، وتحويل المستحقات، وتقييم المستقل.');
    }
}
