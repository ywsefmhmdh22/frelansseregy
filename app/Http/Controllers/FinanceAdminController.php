<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Transaction;

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
}
