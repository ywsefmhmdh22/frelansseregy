<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Review; // تأكد أن هذا السطر موجود
use App\Observers\ReviewObserver; // تأكد أن هذا السطر موجود
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // حل مشكلة طول المفاتيح
        Schema::defaultStringLength(191);

        /**
         * 1. تعريف بوابة الدخول للوحة التحكم
         */
        \Illuminate\Support\Facades\Gate::define('admin-access', function (User $user) {
            return $user->role === 'admin';
        });

        /**
         * 2. إنشاء محفظة تلقائياً لكل مستخدم جديد
         */
        User::created(function ($user) {
            if (!$user->wallet) {
                $user->wallet()->create([
                    'balance' => 0.00,
                ]);
            }
        });

        /**
         * 3. ربط مراقب التقييمات لتحديث عدادات النخبة (هذا هو السطر الناقص)
         */
        Review::observe(ReviewObserver::class);
    }
}
