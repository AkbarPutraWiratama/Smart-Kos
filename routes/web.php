<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocaleController;

// Locale Switch Route
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\Guest\LandingController;
use App\Http\Controllers\Guest\RoomViewController;
use App\Http\Controllers\Payment\MidtransWebhookController;

// Public Smart Kos Guest Routes
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/rooms', [RoomViewController::class, 'index'])->name('guest.rooms');

// Original TailAdmin Demo Dashboard
Route::get('/tailadmin-demo', function () {
    return view('pages.dashboard.ecommerce', ['title' => 'E-commerce Dashboard']);
})->name('dashboard');

// calender pages
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Calendar']);
})->name('calendar');

// profile pages
Route::get('/profile', function () {
    return view('pages.profile', ['title' => 'Profile']);
})->name('profile');

// form pages
Route::get('/form-elements', function () {
    return view('pages.form.form-elements', ['title' => 'Form Elements']);
})->name('form-elements');

// tables pages
Route::get('/basic-tables', function () {
    return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
})->name('basic-tables');

// pages

Route::get('/blank', function () {
    return view('pages.blank', ['title' => 'Blank']);
})->name('blank');

// error pages
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

// chart pages
Route::get('/line-chart', function () {
    return view('pages.chart.line-chart', ['title' => 'Line Chart']);
})->name('line-chart');

Route::get('/bar-chart', function () {
    return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
})->name('bar-chart');


// authentication pages
use App\Http\Controllers\Auth\LoginController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/signin', function () {
    return view('pages.auth.signin', ['title' => 'Sign In']);
})->name('signin');

Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => 'Sign Up']);
})->name('signup');

// ui elements pages
Route::get('/alerts', function () {
    return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
})->name('alerts');

Route::get('/avatars', function () {
    return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
})->name('avatars');

Route::get('/badge', function () {
    return view('pages.ui-elements.badges', ['title' => 'Badges']);
})->name('badges');

Route::get('/buttons', function () {
    return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
})->name('buttons');

Route::get('/image', function () {
    return view('pages.ui-elements.images', ['title' => 'Images']);
})->name('images');

Route::get('/videos', function () {
    return view('pages.ui-elements.videos', ['title' => 'Videos']);
})->name('videos');

// Protected Smart Kos Role Routes
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FinanceController as AdminFinanceController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\TenantAssignmentController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\AccountController as TenantAccountController;
use App\Http\Controllers\Tenant\PaymentController as TenantPaymentController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\PostController as StaffPostController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/riwayat-pengumuman', [AdminPostController::class, 'index'])->name('posts.index');
    Route::post('/posts', [AdminPostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{post}', [AdminPostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [AdminPostController::class, 'destroy'])->name('posts.destroy');
    Route::delete('/posts-trash/{id}/force', [AdminPostController::class, 'forceDestroy'])->name('posts.force-destroy');
    Route::delete('/posts-trash/destroy-all', [AdminPostController::class, 'destroyAll'])->name('posts.destroy-all');

    // Location CRUD (Phase 10)
    Route::resource('locations', LocationController::class);
    Route::delete('/locations/{location}/photos/bulk-destroy', [LocationController::class, 'bulkDestroyPhotos'])->name('locations.photos.bulk-destroy');
    Route::delete('/locations/{location}/photos/{photo}', [LocationController::class, 'destroyPhoto'])->name('locations.photos.destroy');
    Route::delete('/locations/{location}/legacy-photo', [LocationController::class, 'destroyLegacyPhoto'])->name('locations.legacy-photo.destroy');

    // Room CRUD (nested under location, Phase 10)
    Route::post('/locations/{location}/rooms', [RoomController::class, 'store'])->name('locations.rooms.store');
    Route::get('/locations/{location}/rooms/{room}/edit', [RoomController::class, 'edit'])->name('locations.rooms.edit');
    Route::put('/locations/{location}/rooms/{room}', [RoomController::class, 'update'])->name('locations.rooms.update');
    Route::delete('/locations/{location}/rooms/{room}', [RoomController::class, 'destroy'])->name('locations.rooms.destroy');
    Route::post('/locations/{location}/rooms/{room}/sync-va', [RoomController::class, 'syncVa'])->name('locations.rooms.sync-va');

    // Tenant Account Management (Phase 12)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::patch('/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Staff Account Management (Phase 13)
    Route::get('/staff-members', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff-members/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff-members', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff-members/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff-members/{staff}', [StaffController::class, 'update'])->name('staff.update');
    Route::patch('/staff-members/{staff}/deactivate', [StaffController::class, 'deactivate'])->name('staff.deactivate');
    Route::patch('/staff-members/{staff}/reactivate', [StaffController::class, 'reactivate'])->name('staff.reactivate');
    Route::post('/staff-members/{staff}/reset-password', [StaffController::class, 'resetPassword'])->name('staff.reset-password');
    Route::delete('/staff-members/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');

    // Tenant Room Assignment (Phase 15)
    Route::get('/tenants', [TenantAssignmentController::class, 'index'])->name('tenants.index');
    Route::post('/assignments', [TenantAssignmentController::class, 'store'])->name('assignments.store');
    Route::patch('/assignments/{assignment}/price', [TenantAssignmentController::class, 'updatePrice'])->name('assignments.update-price');
    Route::patch('/assignments/{assignment}/change-room', [TenantAssignmentController::class, 'changeRoom'])->name('assignments.change-room');
    Route::post('/assignments/{assignment}/end', [TenantAssignmentController::class, 'endAssignment'])->name('assignments.end');

    // Manual Payment Verification (Phase 26)
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/approve', [AdminPaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');

    // Expense Management (Phase 29)
    Route::get('/expenses', [\App\Http\Controllers\Admin\ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [\App\Http\Controllers\Admin\ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [\App\Http\Controllers\Admin\ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}/edit', [\App\Http\Controllers\Admin\ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{expense}', [\App\Http\Controllers\Admin\ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [\App\Http\Controllers\Admin\ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Payroll and Cash Advance Management (Phase 30)
    Route::get('/payroll', [\App\Http\Controllers\Admin\PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll', [\App\Http\Controllers\Admin\PayrollController::class, 'store'])->name('payroll.store');
    Route::delete('/payroll/{payroll}', [\App\Http\Controllers\Admin\PayrollController::class, 'destroy'])->name('payroll.destroy');
    Route::post('/cash-advances', [\App\Http\Controllers\Admin\CashAdvanceController::class, 'store'])->name('cash-advances.store');
    Route::patch('/cash-advances/{cashAdvance}/status', [\App\Http\Controllers\Admin\CashAdvanceController::class, 'updateStatus'])->name('cash-advances.update-status');
    Route::delete('/cash-advances/{cashAdvance}', [\App\Http\Controllers\Admin\CashAdvanceController::class, 'destroy'])->name('cash-advances.destroy');

    // Cash Flow Visualization (Phase 31)
    Route::get('/cash-flow', [\App\Http\Controllers\Admin\CashFlowController::class, 'index'])->name('cash-flow.index');

    // Profit and Loss Statement (Phase 32)
    Route::get('/profit-loss', [\App\Http\Controllers\Admin\ProfitLossController::class, 'index'])->name('profit-loss.index');

    // Dummy placeholder pages (sidebar links)
    Route::get('/complaints', fn () => view('admin.complaints.complaint-management'))->name('complaints.index');
    Route::get('/finance', [AdminFinanceController::class, 'index'])->name('finance.index');
    Route::get('/account', fn () => view('admin.account'))->name('account');
});

// Midtrans Webhook Notification Callback (CSRF exempt)
Route::post('/midtrans/callback', [MidtransWebhookController::class, 'handle'])->name('midtrans.callback');
Route::post('/api/midtrans/callback', [MidtransWebhookController::class, 'handle'])->name('midtrans.callback.api');


Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
    Route::get('/riwayat-pengumuman', [StaffPostController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [StaffPostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [StaffPostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{post}', [StaffPostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [StaffPostController::class, 'destroy'])->name('posts.destroy');
    Route::delete('/posts-trash/{id}/force', [StaffPostController::class, 'forceDestroy'])->name('posts.force-destroy');
    Route::delete('/posts-trash/destroy-all', [StaffPostController::class, 'destroyAll'])->name('posts.destroy-all');
    Route::get('/payments', fn () => view('staff.payments.index'))->name('payments.index');
    Route::get('/complaints', fn () => view('staff.complaints.index'))->name('complaints.index');
    Route::get('/account', fn () => view('staff.account'))->name('account');
});

Route::middleware(['auth', 'role:penyewa'])->prefix('penyewa')->name('penyewa.')->group(function () {
    Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('dashboard');
    Route::get('/payments', [TenantPaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/va', [TenantPaymentController::class, 'createVa'])->name('payments.va.create');
    Route::post('/payments/va/check-status', [TenantPaymentController::class, 'checkVaStatus'])->name('payments.va.check-status');
    Route::post('/payments/manual', [TenantPaymentController::class, 'storeManual'])->name('payments.manual.store');
    Route::get('/complaints', fn () => view('penyewa.complaints.index'))->name('complaints.index');
    Route::get('/account', [TenantAccountController::class, 'index'])->name('account');
    Route::put('/account/profile', [TenantAccountController::class, 'updateProfile'])->name('account.profile');
    Route::put('/account/password', [TenantAccountController::class, 'updatePassword'])->name('account.password');
});

// Midtrans Webhook Notification Callback (CSRF exempt)
Route::post('/midtrans/callback', [MidtransWebhookController::class, 'handle'])->name('midtrans.callback');
Route::post('/api/midtrans/callback', [MidtransWebhookController::class, 'handle'])->name('midtrans.callback.api');
























