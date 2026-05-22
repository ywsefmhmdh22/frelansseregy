<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Project;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // نستخدم View Composer لضمان توفر المتغير في كل الصفحات
        View::composer('layouts.app', function ($view) {

            // تخزين عدد المشاريع في الكاش لمدة 60 ثانية لتقليل ضغط قاعدة البيانات
            $count = Cache::remember('new_projects_count', 60, function () {
                return Project::where('status', 'open')->count();
            });

            $view->with('newProjectsCount', $count);
        });
    }
}
