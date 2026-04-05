<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // منع فتح الموقع داخل iframe (حماية من الـ Clickjacking)
        $response->headers->set('X-Frame-Options', 'DENY');

        // منع المتصفح من تخمين نوع الملف (حماية من الـ MIME Sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // تفعيل فلتر الـ XSS في المتصفحات القديمة
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // إجبار المتصفح على استخدام HTTPS فقط (لو عندك SSL)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}
