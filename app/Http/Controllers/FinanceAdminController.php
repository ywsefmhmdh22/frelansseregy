<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceAdminController extends Controller
{
    public function userTransactions(User $user)
    {
        $transactions = Transaction::where('user_id', $user->id)->latest()->paginate(20);
        return view('admin.finance.user_report', compact('user', 'transactions'));
    }

    public function financeRadar()
    {
        $allTransactions = Transaction::with('user')->latest()->paginate(50);
        return view('admin.finance.index', compact('allTransactions'));
    }

    public function disputesIndex()
    {
        $disputes = Project::where('status', 'disputed')->with(['user', 'freelancer'])->get();
        return view('admin.disputes.index', compact('disputes'));
    }

    public function showDispute($id)
    {
        $dispute = Project::where('status', 'disputed')
                          ->with(['user', 'freelancer', 'proposals'])
                          ->findOrFail($id);

        return view('admin.disputes.show', compact('dispute'));
    }

    /**
     * حسم النزاع لصالح العميل وإعادة المبلغ لمحفظته
     */
    public function refundToClient($id)
    {
        $project = Project::findOrFail($id);

        DB::transaction(function () use ($project) {
            // 1. إعادة المبلغ لمحفظة العميل
            $project->user->wallet->increment('balance', $project->price);

            // 2. تحديث حالة المشروع (إلغاء)
            $project->update([
                'status' => 'cancelled',
                'admin_status' => 'rejected'
            ]);

            // 3. تسجيل العملية المالية في جدول المعاملات
            Transaction::create([
                'user_id' => $project->user_id,
                'amount' => $project->price,
                'type' => 'deposit',
                'description' => "استرداد مبلغ مشروع بعد نزاع: " . $project->title,
                'status' => 'completed',
                'currency' => 'EGP' // أو العملة التي تعتمدها
            ]);
        });

        return redirect()->route('admin.disputes.index')->with('success', 'تم إنهاء النزاع وإعادة المبلغ للعميل بنجاح.');
    }

    /**
     * حسم النزاع لصالح المستقل وتحويل المستحقات له
     */
    public function releaseToFreelancer($id)
    {
        $project = Project::findOrFail($id);

        DB::transaction(function () use ($project) {
            // 1. تحويل المبلغ لمحفظة المستقل
            $project->freelancer->wallet->increment('balance', $project->price);

            // 2. تحديث حالة المشروع (مكتمل)
            $project->update([
                'status' => 'completed',
                'admin_status' => 'approved'
            ]);

            // 3. تسجيل العملية المالية للمستقل
            Transaction::create([
                'user_id' => $project->freelancer_id,
                'amount' => $project->price,
                'type' => 'deposit',
                'description' => "استلام مستحقات مشروع بعد نزاع: " . $project->title,
                'status' => 'completed',
                'currency' => 'EGP'
            ]);
        });

        return redirect()->route('admin.disputes.index')->with('success', 'تم إنهاء النزاع وتحويل المبلغ للمستقل بنجاح.');
    }
}
