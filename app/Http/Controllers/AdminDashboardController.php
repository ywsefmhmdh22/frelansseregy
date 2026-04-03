<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Notifications\ProjectStatusNotification;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        $pendingProjects = Project::where('admin_status', 'pending')->latest()->get();
        $disputes = Project::where('status', 'disputed')->with(['user', 'freelancer'])->get();
        $transactions = Transaction::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact('users', 'pendingProjects', 'transactions', 'disputes'));
    }

    public function approveProject($id)
    {
        $project = Project::findOrFail($id);
        $project->update(['admin_status' => 'approved']);

        if ($project->user) {
            $project->user->notify(new ProjectStatusNotification($project->title, 'approved'));
        }

        return back()->with('success', 'تمت الموافقة على المشروع بنجاح.');
    }

    public function deleteProject(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $owner = $project->user;
        $projectTitle = $project->title;
        $reason = $request->notification_message;

        if ($owner) {
            $owner->notify(new ProjectStatusNotification($projectTitle, 'deleted', $reason));
        }

        $project->delete();

        return back()->with('info', 'تم حذف المشروع وإخطار العميل بالسبب.');
    }
}
