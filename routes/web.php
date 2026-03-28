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
use App\Http\Controllers\ServiceController;
use App\Models\Service;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\Freelancer\DashboardController;

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

// ملاحظة: تم نقل Route::get('/profile/{id}') للأسفل لتجنب التعارض

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
    Route::get('/forgot-password', function() { return "قيد الإنشاء"; })->name('password.request');
});

/*
|--------------------------------------------------------------------------
| 3. مسارات الأعضاء (Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- إعدادات الحساب (يجب أن تكون قبل مسار الـ ID المتغير) ---
    Route::prefix('profile')->group(function () {
        Route::get('/settings', [ProfileController::class, 'settings'])->name('profile.settings');
        Route::post('/settings/update-personal', [ProfileController::class, 'updatePersonal'])->name('profile.update.personal');
        Route::post('/settings/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update.password');
        Route::post('/settings/update-image', [ProfileController::class, 'updateImage'])->name('profile.update_image');
        Route::get('/portfolio/create', [PortfolioController::class, 'create'])->name('portfolio.create');
        Route::post('/portfolio/store', [PortfolioController::class, 'store'])->name('portfolio.store');
        Route::post('/orders/{order}/submit-delivery', [OrderController::class, 'submitDelivery'])->name('orders.submit_delivery');
    Route::get('/orders/{order}/dispute', [OrderController::class, 'dispute'])->name('orders.dispute');
    });

    // مسارات الملفات الشخصية العامة (تم وضعها هنا بعد الـ settings)
    Route::get('/profile/{id}', [UserController::class, 'show'])->name('profile.show');
    Route::get('/profile/{id}/reviews', [UserController::class, 'showReviews'])->name('profile.reviews');
    Route::get('/profile/{id}/portfolio', [UserController::class, 'showPortfolio'])->name('profile.portfolio');
    // تأكد أن الاسم مطابق تماماً لما تناديه في الـ Blade
    Route::post('/profile/orders/{id}/submit-delivery', [OrderController::class, 'submitDelivery'])
    ->name('orders.submitDelivery');

    // استكمال البروفايل لأول مرة
    Route::get('/complete-profile', [ProfileCompletionController::class, 'index'])->name('profile.complete');
    Route::post('/complete-profile', [ProfileCompletionController::class, 'store'])->name('profile.store');

    // --- نظام المراسلة والدعم ---
    Route::get('/chat/{user?}', [ChatController::class, 'chat'])->name('messages.chat');
    Route::post('/chat/{user}/send', [ChatController::class, 'sendMessage'])->name('messages.send');
    Route::get('/chat-list', [ChatController::class, 'index'])->name('chat.index');
    Broadcast::routes();

    Route::get('/support', [ProfileController::class, 'tickets'])->name('support.tickets');
    Route::post('/support/send', [ProfileController::class, 'sendTicket'])->name('contact.send');

    // --- مسارات تتطلب بروفايل مكتمل (Middleware) ---
    Route::middleware(['profile.completed'])->group(function () {

        // لوحة تحكم المستقل
        Route::get('/freelancer/dashboard', [DashboardController::class, 'index'])->name('freelancer.dashboard');

        // لوحة تحكم العميل
        Route::get('/client/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');

        // إدارة المشاريع للعميل
        Route::prefix('client/projects')->group(function () {
            Route::get('/', [ClientDashboardController::class, 'myProjects'])->name('projects.my_projects');
            Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
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
        });

        // --- نظام المحفظة والدفع ---
        Route::prefix('wallet')->group(function () {
            Route::get('/', [ClientDashboardController::class, 'wallet'])->name('wallet.index');
            Route::get('/deposit', [ClientDashboardController::class, 'deposit'])->name('wallet.deposit');
            Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment'])->name('pay.initiate');
            Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('pay.callback');

            Route::get('/withdraw', [WithdrawController::class, 'create'])->name('withdraw.create');
            Route::post('/withdraw/process', [WithdrawController::class, 'store'])->name('withdraw.request');
            Route::get('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
        });

        // --- نظام الخدمات المصغرة ---
        Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
        Route::post('/services/store', [ServiceController::class, 'store'])->name('services.store');
        Route::get('/services/checkout/{id}', [ServiceController::class, 'checkout'])->name('services.checkout');
        Route::get('/purchased-services', [OrderController::class, 'purchasedServices'])->name('purchased.services');
        Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');

        // الإشعارات والمفضلة
        Route::get('/notifications', function () { return view('notifications.index'); })->name('notifications.index');
        Route::get('/notifications/mark-all-read', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return back()->with('success', 'تم التحديد كمقروء');
        })->name('notifications.markAllRead');

        Route::get('/freelancers/favorites', [ClientDashboardController::class, 'favorites'])->name('freelancers.favorites');
        Route::post('/projects/{project}/proposals', [ProposalController::class, 'store'])->name('proposals.store');
    });

    /*
    |--------------------------------------------------------------------------
    | 4. لوحة تحكم الأدمن
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->middleware('can:admin-access')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/user/{user}', [AdminController::class, 'show'])->name('admin.user.details');
        Route::post('/user/{user}/verify', [AdminController::class, 'verify'])->name('admin.verify');
        Route::post('/user/{user}/ban', [AdminController::class, 'toggleBan'])->name('admin.user.ban');
        Route::post('/projects/{id}/approve', [AdminController::class, 'approveProject'])->name('admin.projects.approve');
        Route::delete('/projects/{id}/delete', [AdminController::class, 'deleteProject'])->name('admin.projects.delete');
    });
});
