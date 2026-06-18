<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cookie;

class ProcessReferral
{
    /**
     * تتبع روابط الإحالة وحفظ الكود في الجلسة والكوكيز لضمان تخطي حظر المتصفح الخفي
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('ref')) {
            $refCode = $request->query('ref');

            // 1. تخزين الكود في الجلسة (Session) - أمان 100% للمتصفح الخفي لأنها تُحفظ في السيرفر
            $request->session()->put('referred_by', $refCode);

            $response = $next($request);

            // 2. تخزين الكود في الكوكيز المطور لمدة 30 يوم
            return $response->cookie('referred_by', $refCode, 43200);
        }

        return $next($request);
    }
}
