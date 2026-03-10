<?php

use Illuminate\Support\Facades\Route;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileCompletionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ClientDashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\WithdrawController;
use App\Events\NewMessageEvent;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\ServiceController;
use App\Models\Service;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| 1. الصفحات العامة
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $allData = Project::where('admin_status', 'approved')->latest()->get();
    return view('welcome', compact('allData'));
})->name('home');

Route::get('/about', function () { return view('about'); })->name('about');

Route::get('/Projects', function () {
    $allData = Project::where('admin_status', 'approved')->latest()->get();
    return view('Projects', compact('allData'));
})->name('projects.index');

Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

 Route::get('/Services', function () {
    // هنجيب الخدمات اللي حالتها active ومضافة حديثاً
    $allData = Service::where('status', 'active')->latest()->get();
    return view('Services', compact('allData'));
})->name('services.index');

Route::get('/top-rated', function () {
    $freelancers = User::where('role', 'freelancer')
                    ->where('is_profile_completed', 1)
                    ->orderBy('freelancer_rating', 'desc')
                    ->take(20)
                    ->get();
    return view('top-rated', compact('freelancers'));
})->name('top_rated');

Route::get('/fix-wallets', function () {
    $users = User::all();
    foreach ($users as $user) {
        $user->wallet()->firstOrCreate([], ["balance" => 0]);
    }
    return "مبروك يا هاني.. كل المحافظ جاهزة!";
});

/*
|--------------------------------------------------------------------------
| 2. مسارات الزوار (Guest)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/forgot-password', function() { return "صفحة استعادة كلمة المرور قيد الإنشاء"; })->name('password.request');
});

/*
|--------------------------------------------------------------------------
| 3. مسارات الأعضاء (Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/complete-profile', [ProfileCompletionController::class, 'index'])->name('profile.complete');
    Route::post('/complete-profile', [ProfileCompletionController::class, 'store'])->name('profile.store');

    // --- نظام المراسلة ---
    Route::get('/chat/{user?}', [ChatController::class, 'chat'])->name('messages.chat');
    Route::post('/chat/{user}/send', [ChatController::class, 'sendMessage'])->name('messages.send');
    Route::get('/chat-list', [ChatController::class, 'index'])->name('chat.index');

    // تفعيل مسارات الـ Broadcasting
    Broadcast::routes();

    // --- مسارات السحب (Withdraw) ---
    Route::get('/withdraw', [WithdrawController::class, 'create'])->name('withdraw.create');
    Route::post('/withdraw/process', [WithdrawController::class, 'store'])->name('withdraw.request');

    // --- مسارات تتطلب بروفايل مكتمل ---
    Route::middleware(['profile.completed'])->group(function () {

        // --- تعديل مسار لوحة تحكم المستقل لحل مشكلة المتغيرات ---
        Route::get('/freelancer/dashboard', function() {
            $user = auth()->user();

            if($user->role !== 'freelancer') return redirect()->route('client.dashboard');

            // تجهيز البيانات اللازمة للواجهة
            $unreadMessagesCount = 0; // يمكنك ربطها لاحقاً بنظام الشات

            $workingProjects = Project::where('freelancer_id', $user->id)
                                        ->whereIn('status', ['in_progress', 'pending_delivery'])
                                        ->get();

            $pendingBalance = Project::where('freelancer_id', $user->id)
                                        ->where('status', 'in_progress')
                                        ->sum('final_price');

            return view('dashboards.freelancer Dashboard', compact(
                'user',
                'unreadMessagesCount',
                'workingProjects',
                'pendingBalance'
            ));
        })->name('freelancer.dashboard');

        Route::get('/client/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');

        Route::post('/profile/update-image', [ProfileCompletionController::class, 'updateImage'])->name('profile.update_image');
        Route::get('/profile/edit', [ProfileCompletionController::class, 'index'])->name('profile.edit');

        // إدارة المشاريع
        Route::prefix('client/projects')->group(function () {
            Route::get('/', [ClientDashboardController::class, 'myProjects'])->name('projects.my_projects');
            Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
            Route::post('/store', [ProjectController::class, 'store'])->name('projects.store');
            Route::get('/{id}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
            Route::get('/{id}/offers', [ClientDashboardController::class, 'projectOffers'])->name('projects.offers');
            Route::post('/{project}/assign/{proposal}', [ProjectController::class, 'assignFreelancer'])->name('projects.assign');
            Route::get('/{project}/review', [ProjectController::class, 'reviewPage'])->name('projects.review');
            Route::post('/{project}/complete', [ProjectController::class, 'completeProject'])->name('projects.complete');
        });

        Route::post('/projects/{project}/request-delivery', [ProjectController::class, 'requestDelivery'])->name('projects.requestDelivery');

        // المحفظة
        Route::prefix('wallet')->group(function () {
            Route::get('/', [ClientDashboardController::class, 'wallet'])->name('wallet.index');
            Route::get('/deposit', [ClientDashboardController::class, 'deposit'])->name('wallet.deposit');
            Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment'])->name('pay.initiate');
            Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('pay.callback');
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/', function () { return view('notifications.index'); })->name('notifications.index');
            Route::get('/mark-all-read', function () {
                auth()->user()->unreadNotifications->markAsRead();
                return back()->with('success', 'تم التحديد كمقروء');
            })->name('notifications.markAllRead');
        });

        Route::get('/freelancers/favorites', [ClientDashboardController::class, 'favorites'])->name('freelancers.favorites');
        Route::get('/tickets', [ClientDashboardController::class, 'tickets'])->name('tickets.index');
        Route::post('/projects/{project}/proposals', [ProposalController::class, 'store'])->name('proposals.store');

        // --- مسارات الخدمات والطلبات ---
        Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
        Route::post('/services/store', [ServiceController::class, 'store'])->name('services.store');

        // المسار الذي كان يسبب المشكلة (تأكد من وجود الدالة في الكنترولر)
        Route::get('/services/checkout/{id}', [ServiceController::class, 'checkout'])->name('services.checkout');

        Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');
    });

    /*
    |--------------------------------------------------------------------------
    | 4. لوحة تحكم الأدمن
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/user/{user}', [AdminController::class, 'show'])->name('admin.user.details');
        Route::post('/user/{user}/verify', [AdminController::class, 'verify'])->name('admin.verify');
        Route::post('/user/{user}/ban', [AdminController::class, 'toggleBan'])->name('admin.user.ban');
        Route::post('/projects/{id}/approve', [AdminController::class, 'approveProject'])->name('admin.projects.approve');
        Route::delete('/projects/{id}/delete', [AdminController::class, 'deleteProject'])->name('admin.projects.delete');
    });
});
