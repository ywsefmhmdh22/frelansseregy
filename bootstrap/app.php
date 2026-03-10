 <?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // بنسجل هنا الحارس بتاعنا ونديله اسم مستعار 'profile.completed'
        // عشان نستخدم الاسم ده بسهولة في ملف الروابط (Routes)
        $middleware->alias([
            'profile.completed' => \App\Http\Middleware\EnsureProfileIsCompleted::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
