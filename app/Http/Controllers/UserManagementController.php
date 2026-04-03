<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function show(User $user)
    {
        $workingProjects = Project::where('freelancer_id', $user->id)
                                    ->whereIn('status', ['in_progress', 'pending_delivery'])
                                    ->get();
        $balance = $user->balance;
        return view('admin.user-details', compact('user', 'workingProjects', 'balance'));
    }

    public function approveUser(User $user)
    {
        $user->update(['verification_status' => 'pending']);
        return back()->with('success', 'تم قبول انضمام ' . $user->name . ' للمنصة.');
    }

    public function verify(User $user)
    {
        $user->update([
            'is_profile_completed' => 1,
            'verification_status'  => 'verified'
        ]);
        return back()->with('success', 'تم توثيق هوية ' . $user->name . ' بنجاح.');
    }

    public function rejectVerification(User $user)
    {
        $user->update(['verification_status' => 'rejected']);
        return back()->with('danger', 'تم رفض أوراق التوثيق لـ ' . $user->name . '.');
    }

    public function ban(User $user)
    {
        $user->update(['is_banned' => !$user->is_banned]);
        $status = $user->is_banned ? 'محظور نهائياً' : 'نشط الآن';
        return back()->with('warning', 'حالة حساب ' . $user->name . ' أصبحت: ' . $status);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'balance' => 'required|numeric',
        ]);

        $user->update($request->only('name', 'email', 'balance', 'role'));
        return redirect()->route('admin.dashboard')->with('success', 'تم تحديث بيانات العميل بنجاح.');
    }

    public function impersonate(User $user)
    {
        session()->put('admin_id', Auth::id());
        Auth::login($user);
        return redirect('/')->with('info', 'أنت الآن تتصفح المنصة بهوية: ' . $user->name);
    }
}
