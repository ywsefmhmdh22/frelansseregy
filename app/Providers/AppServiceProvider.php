<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Wallet;
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
        // حل مشكلة طول المفاتيح في قواعد البيانات القديمة (احتياطي)
        Schema::defaultStringLength(191);

        /**
         * 1. إنشاء محفظة تلقائياً لكل مستخدم جديد
         * بمجرد ما أي حد يسجل في الموقع (فري لانسر أو عميل)
         * السيستم هيفتحله حساب في جدول wallets ورصيده 0.00
         */
        User::created(function ($user) {
            $user->wallet()->create([
                'balance' => 0.00,
            ]);
        });
    }
}
