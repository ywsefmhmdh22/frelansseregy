<?php

use Illuminate\Support\Facades\Route;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use App\Models\Service;
use App\Http\Controllers\{
    AuthController,
    ProfileCompletionController,
    AdminDashboardController,
    UserManagementController,
    FinanceAdminController,
    ProjectController,
    ProposalController,
    ClientDashboardController,
    PaymentController,
    ChatController,
    WithdrawController,
    ServiceController,
    OrderController,
    ProfileController,
    UserController,
    PortfolioController,
    NotificationController,
    DisputeController // أضفنا الكنترولر هنا
};
use App\Http\Controllers\Freelancer\DashboardController;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| 1. الصفحات العامة (الزوار والأعضاء)
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
    $allData = Service::where('status', 'active')->latest()->get();
    return view('Services', compact('allData'));
})->name('services.index');

Route::get('/Works', function () {
    $works = Project::where('status', 'completed')->latest()->get();
    return view('works', compact('works'));
})->name('works.index');

Route::get('/top-rated', function () {
    $freelancers = User::where('role', 'freelancer')
                    ->where('is_profile_completed', 1)
                    ->where('freelancer_rating', '>=', 4)
                    ->orderBy('freelancer_rating', 'desc')
                    ->take(20)->get();
    return view('top-rated', compact('freelancers'));
})->name('top_rated');

Route::get('/fix-wallets', function () {
    $users = User::all();
    foreach ($users as $user) {
        $user->wallet()->firstOrCreate([], ["balance" => 0]);
    }
    return "مبروك يا هاني.. كل المحافظ جاهزة!";
});

// --- روت الـ Webhook و الـ Callback ---
// نقلنا الـ Callback هنا عشان يكون متاح مباشرة لـ Paymob بدون prefix
Route::post('/payment/processed', [PaymentController::class, 'processedCallback'])->name('pay.webhook');
Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('pay.callback');

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

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| 3. مسارات الأعضاء (Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- روت نظام النزاعات الجديد ---
    Route::post('/dispute/store', [DisputeController::class, 'store'])->name('dispute.store');

    // --- نظام المحفظة والمدفوعات ---
    Route::prefix('wallet')->group(function () {
        Route::get('/', [ClientDashboardController::class, 'wallet'])->name('wallet.index');
        Route::get('/deposit', [PaymentController::class, 'showDepositForm'])->name('wallet.deposit');
        Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment'])->name('pay.initiate');
        // تم حذف الـ callback من هنا ونقله للأعلى ليعمل بشكل صحيح مع Paymob
        Route::post('/withdraw', [WithdrawController::class, 'store'])->name('wallet.process_withdraw');
    });

    // --- إعدادات الحساب ---
    Route::prefix('profile')->group(function () {
        Route::get('/settings', [ProfileController::class, 'settings'])->name('profile.settings');
        Route::post('/settings/update-personal', [ProfileController::class, 'updatePersonal'])->name('profile.update.personal');
        Route::post('/settings/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update.password');
        Route::post('/settings/update-image', [ProfileController::class, 'updateImage'])->name('profile.update_image');
        Route::get('/portfolio/create', [PortfolioController::class, 'create'])->name('portfolio.create');
        // تم تصحيح Portfolio Store هنا ليعمل مع الدالة الصحيحة
        Route::post('/portfolio/store', [PortfolioController::class, 'store'])->name('portfolio.store');

        Route::get('/orders/{order}/deliver', [OrderController::class, 'showDeliverPage'])->name('orders.deliver_page');
        Route::post('/orders/{order}/deliver', [OrderController::class, 'submitDelivery'])->name('orders.deliver');

        Route::get('/orders/{order}/dispute', [OrderController::class, 'dispute'])->name('orders.dispute');
    });

    Route::get('/profile/{id}', [UserController::class, 'show'])->name('profile.show');
    Route::get('/profile/{id}/reviews', [UserController::class, 'showReviews'])->name('profile.reviews');
    Route::get('/profile/{id}/portfolio', [UserController::class, 'showPortfolio'])->name('profile.portfolio');

    Route::get('/complete-profile', [ProfileCompletionController::class, 'index'])->name('profile.complete');
    Route::post('/complete-profile', [ProfileCompletionController::class, 'store'])->name('profile.store'); // تم التعديل هنا ليطابق الـ Blade

    // --- نظام المراسلة ---
    Route::get('/chat/{user?}', [ChatController::class, 'chat'])->name('messages.chat');
    Route::post('/chat/{user}/send', [ChatController::class, 'sendMessage'])->name('messages.send');
    Route::get('/chat-list', [ChatController::class, 'index'])->name('chat.index');
    Broadcast::routes();

    Route::get('/support', [ProfileController::class, 'tickets'])->name('support.tickets');
    Route::post('/support/send', [ProfileController::class, 'sendTicket'])->name('contact.send');

    // --- مسارات تتطلب بروفايل مكتمل ---
    Route::middleware(['profile.completed'])->group(function () {

        Route::get('/freelancer/dashboard', [DashboardController::class, 'index'])->name('freelancer.dashboard');
        Route::get('/client/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');

        Route::prefix('client/projects')->group(function () {
            Route::get('/', [ClientDashboardController::class, 'myProjects'])->name('projects.my_projects');
            Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
            // تم تصحيح Project Store هنا ليعمل مع الدالة الصحيحة
            Route::post('/store', [ProjectController::class, 'store'])->name('projects.store');
            Route::get('/{id}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
            Route::get('/{id}/offers', [ClientDashboardController::class, 'projectOffers'])->name('projects.offers');
            Route::post('/{project}/assign/{proposal}', [ProjectController::class, 'assignFreelancer'])->name('projects.assign');
            Route::get('/{project}/review', [ProjectController::class, 'reviewPage'])->name('projects.review');
            Route::post('/{project}/complete', [ProjectController::class, 'completeProject'])->name('projects.complete');
            Route::post('/{project}/request-delivery', [ProjectController::class, 'requestDelivery'])->name('projects.requestDelivery');

            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::get('/orders/{id}/complete', [OrderController::class, 'showCompletePage'])->name('orders.complete.view');
            Route::post('/orders/complete-process', [OrderController::class, 'completeAndRate'])->name('orders.complete.post');
            Route::post('/services/order/{order}/request-delivery', [ServiceController::class, 'requestDelivery'])->name('services.requestDelivery');

            Route::get('/blog', [App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
            Route::get('/blog/{id}', [App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');
        });

        Route::get('/withdraw', [WithdrawController::class, 'create'])->name('withdraw.create');
        Route::post('/withdraw/process', [WithdrawController::class, 'store'])->name('withdraw.request');

        // 👇 تم ترتيب مسارات الخدمات هنا؛ وضعنا المسارات الثابتة أولاً تلافياً لمشكلة الـ 404 👇
        Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
        Route::post('/services/store', [ServiceController::class, 'store'])->name('services.store');
        Route::get('/purchased-services', [OrderController::class, 'purchasedServices'])->name('purchased.services');
        Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');
        // مسار تنفيذ الدفع من المحفظة
        Route::post('/services/pay-wallet/{id}', [App\Http\Controllers\ServiceController::class, 'payFromWallet'])->name('service.pay.wallet');

        Route::get('/notifications', function () { return view('notifications.index'); })->name('notifications.index');
        Route::get('/notifications/mark-all-read', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return back()->with('success', 'تم التحديد كمقروء');
        })->name('notifications.markAllRead');

        Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

        Route::get('/freelancers/favorites', [ClientDashboardController::class, 'favorites'])->name('freelancers.favorites');
        Route::post('/projects/{project}/proposals', [ProposalController::class, 'store'])->name('proposals.store');
    });

    // 👇 تم وضع مسارات المتغيرات العامة للخدمات هنا بالأسفل لكي لا تبتلع روابط الـ create 👇
    // تم إضافة هذا الروت ليتمكن الزوار من رؤية تفاصيل الخدمة
    Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');
    // تم نقل روت الـ checkout إلى هنا ليصبح عاماً
    Route::get('/services/checkout/{id}', [ServiceController::class, 'checkout'])->name('services.checkout');

    /*
    |--------------------------------------------------------------------------
    | 4. لوحة تحكم الأدمن
    |--------------------------------------------------------------------------
    |
    */
    Route::prefix('admin')->name('admin.')->middleware('can:admin-access')->group(function () {

        Route::controller(AdminDashboardController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::post('/user/{id}/approve', 'approveUser')->name('user.approve');
            Route::post('/user/{id}/ban', 'banUser')->name('user.ban');
            Route::post('/user/{id}/reset-wallet', 'resetWallet')->name('user.reset-wallet');
            Route::get('/users/edit/{id}', 'editUser')->name('user.edit');
            Route::post('/withdrawals/{id}/process', 'processWithdrawal')->name('withdrawals.process');
        });

        Route::controller(FinanceAdminController::class)->group(function () {
            Route::get('/financial/disputes', 'disputesIndex')->name('disputes.index');
            Route::post('/financial/disputes/{id}/refund', 'refundToClient')->name('disputes.refund');
            Route::post('/financial/disputes/{id}/release', 'releaseToFreelancer')->name('disputes.release');
            Route::get('/financial/disputes/{id}', 'showDispute')->name('disputes.show');
            Route::get('/financial/user/{user}', 'userTransactions')->name('user.transactions');
            Route::get('/financial/radar', 'financeRadar')->name('finance.radar');
        });

        Route::controller(App\Http\Controllers\Admin\ProjectController::class)->group(function () {
            Route::get('/projects/all', 'index')->name('projects.index');
            Route::get('/projects/pending', 'pendingProjects')->name('projects.pending');
            Route::post('/projects/{id}/approve', 'approve')->name('projects.approve');
            Route::post('/projects/{id}/reject', 'reject')->name('projects.reject');
            Route::delete('/projects/delete/{id}', 'deleteProject')->name('projects.delete');
        });

        Route::controller(UserManagementController::class)->group(function () {
            Route::get('/users/view/{user}', 'show')->name('user.details');
            Route::put('/users/update/{user}', 'update')->name('user.update');
            Route::get('/users/impersonate/{user}', 'impersonate')->name('user.impersonate');
            Route::post('/users/verify-action/{user}', 'verify')->name('verify');

            // 👇 التصحيح النهائي لسطر الحذف هنا (تم حذف /admin/ الزائدة) 👇
            Route::delete('/user/{id}/delete', [AdminDashboardController::class, 'destroyUser'])->name('user.delete');
        });
    });
});
