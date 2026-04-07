<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Review;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * Class ProjectController
 * المسؤول عن إدارة دورة حياة المشاريع من الإضافة والتوظيف حتى الاستلام والتقييم.
 * تم تحسين معالجة الأخطاء لضمان عدم وجود ملفات يتيمة أو عمليات مالية معلقة في حالة الفشل.
 */
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
     * 2. حفظ المشروع مع نظام معالجة أخطاء شامل (Fixing the BUG)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255|trim',
            'description'   => 'required|string|min:20',
            'price'         => 'required|numeric|min:1',
            'currency'      => 'required|string|max:10',
            'duration'      => 'required|string',
            'image_url'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'attachments.*' => 'nullable|file|mimes:pdf,zip,rar,doc,docx,jpg,png|max:10240',
        ]);

        // متغيرات لتتبع الملفات المرفوعة لحذفها في حالة فشل قاعدة البيانات
        $imagePath = null;
        $attachmentsPaths = [];

        try {
            return DB::transaction(function () use ($request, &$imagePath, &$attachmentsPaths) {
                // 1. رفع الملفات أولاً
                $imagePath = $this->uploadProjectCover($request);
                $attachmentsPaths = $this->uploadProjectAttachments($request);

                // 2. إنشاء سجل المشروع
                $project = Project::create([
                    'user_id'      => Auth::id(),
                    'title'        => strip_tags($request->title),
                    'description'  => strip_tags($request->description), // تأمين الوصف ضد XSS
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
        } catch (Exception $e) {
            // معالجة الخطأ وحذف الملفات التي رُفعت لمنع الـ Orphaned Files
            Log::error('Project Store Error (Bug Prevention): ' . $e->getMessage());

            if ($imagePath) Storage::disk('public')->delete($imagePath);
            foreach ($attachmentsPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            return back()->with('error', 'حدث خطأ أثناء حفظ المشروع، يرجى المحاولة مرة أخرى.')->withInput();
        }
    }

    /**
     * 3. توظيف مستقل مع ضمان سلامة المحفظة المالية
     */
    public function assignFreelancer(Project $project, $proposalId)
    {
        if (Auth::id() !== $project->user_id) {
            return back()->with('error', 'غير مسموح لك بهذا الإجراء.');
        }

        try {
            $proposal = Proposal::findOrFail($proposalId);
            $requiredAmount = $proposal->amount ?? $proposal->price;

            return DB::transaction(function () use ($project, $proposal, $requiredAmount) {
                // قفل سجل المحفظة لمنع الـ Race Condition
                $wallet = Wallet::where('user_id', Auth::id())->lockForUpdate()->first();

                if (!$wallet || $wallet->balance < $requiredAmount) {
                    throw new Exception('رصيدك غير كافٍ لإتمام التوظيف.');
                }

                // تنفيذ العملية المالية
                $this->processEmploymentPayment($wallet, $requiredAmount, $project->title);

                // تحديث حالة المشروع والطلب
                $project->update([
                    'freelancer_id' => $proposal->user_id,
                    'status'        => 'in_progress',
                    'final_price'   => $requiredAmount,
                ]);

                $proposal->update(['status' => 'accepted']);

                return back()->with('success', 'تم التوظيف بنجاح! المبلغ في أمان الآن.');
            });
        } catch (Exception $e) {
            Log::error('Assign Freelancer Error: ' . $e->getMessage());
            return back()->with('error', $e->getMessage() ?: 'حدث خطأ تقني أثناء التوظيف.');
        }
    }

    /**
     * 4. استلام المشروع وتقييمه مع توزيع الأرباح
     */
    public function completeProject(Request $request, Project $project)
    {
        if (Auth::id() !== $project->user_id) return back()->with('error', 'غير مسموح لك.');

        $request->validate([
            'rating_quality' => 'required|integer|min:1|max:5',
            'rating_time'    => 'required|integer|min:1|max:5',
            'review_comment' => 'required|string|min:10|max:1000',
        ]);

        try {
            DB::transaction(function () use ($project, $request) {
                $amount = $project->final_price ?? $project->price;
                $avgRating = $this->calculateAverageRating($request);

                // 1. تحويل الأرباح للمستقل
                $this->payoutToFreelancer($project->freelancer_id, $amount, $project->title);

                // 2. تسجيل التقييم (بشكل آمن)
                Review::create([
                    'project_id'    => $project->id,
                    'freelancer_id' => $project->freelancer_id,
                    'user_id'       => Auth::id(),
                    'rating'        => $avgRating,
                    'comment'       => strip_tags($request->review_comment),
                ]);

                // 3. تحديث حالة المشروع والمستقل
                $project->update(['status' => 'completed']);
                User::where('id', $project->freelancer_id)->update(['freelancer_rating' => $avgRating]);
            });

            return redirect()->route('projects.show', $project->id)->with('success', 'تم الاستلام والتقييم بنجاح.');
        } catch (Exception $e) {
            Log::error('Complete Project Error: ' . $e->getMessage());
            return back()->with('error', 'فشل في إتمام عملية الاستلام.');
        }
    }

    // --- Private Helper Methods (SRP & Clean Code) ---

    private function uploadProjectCover(Request $request)
    {
        return $request->hasFile('image_url')
            ? $request->file('image_url')->store('projects/covers', 'public')
            : null;
    }

    private function uploadProjectAttachments(Request $request)
    {
        $paths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $paths[] = $file->store('projects/attachments', 'public');
            }
        }
        return $paths;
    }

    private function processEmploymentPayment($wallet, $amount, $projectTitle)
    {
        $wallet->decrement('balance', $amount);
        Transaction::create([
            'user_id' => Auth::id(),
            'amount'  => $amount,
            'type'    => 'withdraw',
            'status'  => 'completed',
            'details' => 'خصم رصيد لتوظيف مستقل لمشروع: ' . $projectTitle,
        ]);
    }

    private function payoutToFreelancer($freelancerId, $amount, $projectTitle)
    {
        $fWallet = Wallet::firstOrCreate(['user_id' => $freelancerId], ['balance' => 0]);
        $fWallet->increment('balance', $amount);

        Transaction::create([
            'user_id' => $freelancerId,
            'amount'  => $amount,
            'type'    => 'deposit',
            'status'  => 'completed',
            'details' => 'أرباح مشروع: ' . $projectTitle,
        ]);
    }

    private function calculateAverageRating(Request $request)
    {
        $ratings = [
            $request->rating_quality,
            $request->rating_time,
            $request->rating_behavior ?? 5,
            $request->rating_communication ?? 5
        ];
        return array_sum($ratings) / count($ratings);
    }

    public function show(Project $project)
    {
        $project->load(['proposals.user', 'user', 'freelancer']);
        return view('projects.show', compact('project'));
    }

    public function requestDelivery(Project $project)
    {
        if (Auth::id() !== $project->freelancer_id) return back()->with('error', 'أنت لست المنفذ.');
        $project->update(['status' => 'pending_delivery']);
        return back()->with('success', 'تم إرسال طلب التسليم.');
    }
}
