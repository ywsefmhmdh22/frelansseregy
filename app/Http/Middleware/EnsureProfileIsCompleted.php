<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProfileIsCompleted
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // إذا كان النظام يعتقد أن الملف غير مكتمل، نحدث البيانات من القاعدة للتأكد
            if (!$user->is_profile_completed) {
                $user->refresh();
            }

            // الفحص النهائي
            if (!$user->is_profile_completed) {
                // استثناء المسارات الضرورية
                if ($request->routeIs('profile.complete') || $request->routeIs('profile.store') || $request->routeIs('logout')) {
                    return $next($request);
                }

                return redirect()->route('profile.complete');
            }
        }

        return $next($request);
    }
}
