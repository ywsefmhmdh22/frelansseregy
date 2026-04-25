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
     * 2. حفظ المشروع مع نظام معالجة أخطاء شامل
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'required|string|min:20',
            'price'         => 'required|numeric|min:1',
            'currency'      => 'required|string|max:10',
            'duration'      => 'required|string',
            'image_url'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'attachments.*' => 'nullable|file|mimes:pdf,zip,rar,doc,docx,jpg,png|max:10240',
        ]);

        $imagePath = null;
        $attachmentsPaths = [];

        try {
            return DB::transaction(function () use ($request, &$imagePath, &$attachmentsPaths) {
                $imagePath = $this->uploadProjectCover($request);
                $attachmentsPaths = $this->uploadProjectAttachments($request);

                $project = Project::create([
                    'user_id'      => Auth::id(),
                    'title'        => strip_tags($request->title),
                    'description'  => strip_tags($request->description),
                    'price'        => $request->price,
                    'currency'     => $request->currency,
                    'duration'     => $request->duration,
                    'image_url'    => $imagePath,
                    'attachments'  => json_encode($attachmentsPaths),
                    'type'         => $request->input('type', 'normal'),
                    'status'       => 'open',
                    'admin_status' => 'pending',
                ]);

                Log::info("Security Audit: New Project Created by User ID " . Auth::id());

                return redirect()->route('client.dashboard')->with('success', 'تم إرسال مشروعك للمراجعة بنجاح!');
            });
        } catch (Exception $e) {
            Log::error('Project Store Failure: ' . $e->getMessage());

            if ($imagePath) Storage::disk('public')->delete($imagePath);
            foreach ($attachmentsPaths as $path) Storage::disk('public')->delete($path);

            return back()->with('error', 'حدث خطأ أثناء حفظ المشروع.')->withInput();
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
                $wallet = Wallet::where('user_id', Auth::id())->lockForUpdate()->first();

                if (!$wallet || $wallet->balance < $requiredAmount) {
                    throw new Exception('رصيدك غير كافٍ لإتمام التوظيف.');
                }

                $this->processEmploymentPayment($wallet, $requiredAmount, $project->title);

                $project->update([
                    'freelancer_id' => $proposal->user_id,
                    'status'         => 'in_progress',
                    'final_price'   => $requiredAmount,
                ]);

                $proposal->update(['status' => 'accepted']);

                Log::info("Payment Secure: Project ID {$project->id} funded.");

                return back()->with('success', 'تم التوظيف بنجاح! المبلغ في أمان الآن.');
            });
        } catch (Exception $e) {
            Log::error('Assign Freelancer Error: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 4. عرض صفحة مراجعة وتقييم المشروع
     */
    public function reviewPage($id)
    {
        $project = Project::with('freelancer')->findOrFail($id);

        if (Auth::id() !== $project->user_id) {
            abort(403, 'غير مصرح لك بالدخول لهذه الصفحة.');
        }

        return view('projects.review', compact('project'));
    }

    /**
     * 5. استلام المشروع وتقييمه مع تحويل الأرباح لحالة "معلق"
     */
    public function completeProject(Request $request, Project $project)
    {
        // التحقق من الملكية
        if (Auth::id() !== $project->user_id) return back()->with('error', 'غير مسموح لك.');

        // التحقق من البيانات
        $request->validate([
            'rating_quality'       => 'required|integer|min:1|max:5',
            'rating_time'          => 'required|integer|min:1|max:5',
            'rating_behavior'      => 'required|integer|min:1|max:5',
            'rating_communication' => 'required|integer|min:1|max:5',
            'review_comment'       => 'required|string|min:10|max:1000',
        ]);

        try {
            DB::transaction(function () use ($project, $request) {
                $amount = $project->final_price ?? $project->price;
                $avgRating = $this->calculateAverageRating($request);

                // 1. تسجيل الأرباح كـ "معلقة" للمستقل (تأكد من وجود ID المستقل)
                if (!$project->freelancer_id) {
                    throw new Exception("لا يوجد مستقل مرتبط بهذا المشروع لإرسال الأرباح إليه.");
                }

                $this->payoutToFreelancer($project, $amount);

                // 2. تسجيل التقييم
                Review::create([
                    'project_id'           => $project->id,
                    'freelancer_id'        => $project->freelancer_id,
                    'user_id'              => Auth::id(),
                    'rating_quality'       => $request->rating_quality,
                    'rating_time'          => $request->rating_time,
                    'rating_behavior'      => $request->rating_behavior,
                    'rating_communication' => $request->rating_communication,
                    'rating'               => $avgRating,
                    'comment'              => strip_tags($request->review_comment),
                ]);

                // 3. تحديث حالة المشروع
                $project->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                // 4. تحديث التقييم العام للمستقل
                $freelancer = User::find($project->freelancer_id);
                if ($freelancer) {
                    $newAvg = Review::where('freelancer_id', $freelancer->id)->avg('rating');
                    $freelancer->update(['freelancer_rating' => $newAvg]);
                }
            });

            return redirect()->route('projects.show', $project->id)->with('success', 'تم الاستلام بنجاح، الأرباح الآن في مرحلة التعليق لضمان جودة العمل.');

        } catch (Exception $e) {
            // التعديل الجوهري: إظهار الخطأ فوراً وعدم الرجوع للخلف
            Log::error('Complete Project Error: ' . $e->getMessage());
            dd([
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'freelancer_id' => $project->freelancer_id,
                'project_id' => $project->id
            ]);
        }
    }

    // --- Private Helper Methods ---

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
            'details' => 'خصم رصيد لتوظيف مستقل لمشروع: ' . strip_tags($projectTitle),
        ]);
    }

    private function payoutToFreelancer(Project $project, $amount)
    {
        // استخدام updateOrCreate لضمان وجود المحفظة وسهولة التعامل
        $fWallet = Wallet::firstOrCreate(
            ['user_id' => $project->freelancer_id],
            ['balance' => 0, 'pending_balance' => 0]
        );

        $fWallet->increment('pending_balance', $amount);

        Transaction::create([
            'user_id'         => $project->freelancer_id,
            'amount'          => $amount,
            'currency'        => $project->currency ?? 'USD',
            'type'            => 'receive',
            'status'          => 'pending',
            'unlock_at'       => now()->addDays(7),
            'source_id'       => $project->id,
            'source_type'     => Project::class,
            'details'         => 'أرباح معلقة لمشروع: ' . strip_tags($project->title),
        ]);
    }

    private function calculateAverageRating(Request $request)
    {
        $ratings = [
            (int)$request->rating_quality,
            (int)$request->rating_time,
            (int)$request->rating_behavior,
            (int)$request->rating_communication
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
