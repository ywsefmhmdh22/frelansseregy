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
     * 2. حفظ المشروع (مع تنظيف البيانات وتأمين الملفات)
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

        try {
            return DB::transaction(function () use ($request) {
                $imagePath = $request->hasFile('image_url')
                    ? $request->file('image_url')->store('projects/covers', 'public')
                    : null;

                $attachmentsPaths = [];
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $attachmentsPaths[] = $file->store('projects/attachments', 'public');
                    }
                }

                $project = Project::create([
                    'user_id'      => Auth::id(),
                    'title'        => strip_tags($request->title),
                    'description'  => $request->description, // سليم لأنه من CKEditor غالباً
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
            Log::error('Project Store Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء حفظ المشروع.')->withInput();
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
     * 4. توظيف مستقل (تم تحسين الأمان وفحص المحفظة)
     */
    public function assignFreelancer(Project $project, $proposalId)
    {
        if (Auth::id() !== $project->user_id) {
            return back()->with('error', 'غير مسموح لك بهذا الإجراء.');
        }

        $proposal = Proposal::findOrFail($proposalId);
        $client = Auth::user();
        $requiredAmount = $proposal->amount ?? $proposal->price;

        // حماية المحفظة: التأكد من وجودها أو إنشائها
        $wallet = Wallet::firstOrCreate(['user_id' => $client->id], ['balance' => 0]);

        if ($wallet->balance < $requiredAmount) {
            $shortage = $requiredAmount - $wallet->balance;
            return back()->with('error', 'رصيدك غير كافٍ. تحتاج لشحن ' . $shortage . ' إضافية.');
        }

        try {
            DB::transaction(function () use ($project, $proposal, $client, $wallet, $requiredAmount) {
                $wallet->decrement('balance', $requiredAmount);

                Transaction::create([
                    'user_id' => $client->id,
                    'amount'  => $requiredAmount,
                    'type'    => 'withdraw',
                    'status'  => 'completed',
                    'details' => 'خصم رصيد لتوظيف مستقل لمشروع: ' . $project->title,
                ]);

                $project->update([
                    'freelancer_id' => $proposal->user_id,
                    'status'        => 'in_progress',
                    'final_price'   => $requiredAmount,
                ]);

                $proposal->update(['status' => 'accepted']);
            });

            return back()->with('success', 'تم التوظيف بنجاح! المبلغ في أمان الآن.');
        } catch (\Exception $e) {
            Log::error('Assign Freelancer Error: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ تقني أثناء التوظيف.');
        }
    }

    /**
     * 5. طلب تسليم المشروع (المستقل)
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
     * 6. استلام المشروع وتقييمه (تم ضغط العمليات لتقليل الحجم)
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
                $freelancer = User::findOrFail($project->freelancer_id);
                $amount = $project->final_price ?? $project->price;

                $avgRating = ($request->rating_quality + $request->rating_time +
                              ($request->rating_behavior ?? 5) + ($request->rating_communication ?? 5)) / 4;

                // تحويل الأرباح بأمان
                $fWallet = Wallet::firstOrCreate(['user_id' => $freelancer->id], ['balance' => 0]);
                $fWallet->increment('balance', $amount);

                Transaction::create([
                    'user_id' => $freelancer->id,
                    'amount'  => $amount,
                    'type'    => 'deposit',
                    'status'  => 'completed',
                    'details' => 'أرباح مشروع: ' . $project->title,
                ]);

                Review::create([
                    'project_id'    => $project->id,
                    'freelancer_id' => $project->freelancer_id,
                    'user_id'       => Auth::id(),
                    'rating'        => $avgRating,
                    'comment'       => strip_tags($request->review_comment),
                ]);

                $project->update(['status' => 'completed']);
                $freelancer->update(['freelancer_rating' => $avgRating]);
            });

            return redirect()->route('projects.show', $project->id)->with('success', 'تم الاستلام والتقييم بنجاح.');
        } catch (\Exception $e) {
            Log::error('Complete Project Error: ' . $e->getMessage());
            return back()->with('error', 'فشل في إتمام عملية الاستلام.');
        }
    }
}
