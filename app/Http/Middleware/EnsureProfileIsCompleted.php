<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProfileIsCompleted
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // 1. تحديث البيانات اللحظية من قاعدة البيانات (عشان لو الأدمن وافق في نفس اللحظة)
            $user->refresh();

            // 2. فحص: هل الحساب مفعل وموثق بالكامل؟
            if ($user->is_profile_completed == 1) {

                // لو هو موثق وبيحاول يدخل صفحة "التوثيق" يدوياً، اطرده للداشبورد بتاعته
                if ($request->routeIs('profile.complete')) {
                    return $user->role === 'freelancer'
                        ? redirect()->route('freelancer.dashboard')
                        : redirect()->route('client.dashboard');
                }

                // اسمح له يتصفح الموقع عادي
                return $next($request);
            }

            // 3. لو الحساب لسه (غير موثق بالكامل):
            // امنع دخول أي صفحة ما عدا صفحات التوثيق والخروج
            $allowedRoutes = [
                'profile.complete',
                'profile.store',
                'logout'
            ];

            if ($request->routeIs($allowedRoutes)) {
                return $next($request);
            }

            // لو حاول يدخل أي صفحة تانية وهو لسه is_profile_completed = 0
            // ارسله فوراً لصفحة استكمال البيانات والانتظار
            return redirect()->route('profile.complete');
        }

        return $next($request);
    }
}
