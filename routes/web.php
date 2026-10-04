<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocaleController;

// Locale Switch Route
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\Guest\LandingController;
use App\Http\Controllers\Guest\RoomViewController;

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
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\RoomController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');

    // Location CRUD (Phase 10)
    Route::resource('locations', LocationController::class);

    // Room CRUD (nested under location, Phase 10)
    Route::post('/locations/{location}/rooms', [RoomController::class, 'store'])->name('locations.rooms.store');
    Route::get('/locations/{location}/rooms/{room}/edit', [RoomController::class, 'edit'])->name('locations.rooms.edit');
    Route::put('/locations/{location}/rooms/{room}', [RoomController::class, 'update'])->name('locations.rooms.update');
    Route::delete('/locations/{location}/rooms/{room}', [RoomController::class, 'destroy'])->name('locations.rooms.destroy');

    // Dummy placeholder pages (sidebar links)
    Route::get('/tenants', fn () => view('admin.tenants.index'))->name('tenants.index');
    Route::get('/complaints', fn () => view('admin.complaints.index'))->name('complaints.index');
    Route::get('/finance', fn () => view('admin.finance.index'))->name('finance.index');
    Route::get('/users', fn () => view('admin.users.index'))->name('users.index');
    Route::get('/account', fn () => view('admin.account'))->name('account');
});

Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', fn () => view('staff.dashboard'))->name('dashboard');
    Route::get('/payments', fn () => view('staff.payments.index'))->name('payments.index');
    Route::get('/complaints', fn () => view('staff.complaints.index'))->name('complaints.index');
    Route::get('/account', fn () => view('staff.account'))->name('account');
});

Route::middleware(['auth', 'role:penyewa'])->prefix('penyewa')->name('penyewa.')->group(function () {
    Route::get('/dashboard', fn () => view('penyewa.dashboard'))->name('dashboard');
    Route::get('/payments', fn () => view('penyewa.payments.index'))->name('payments.index');
    Route::get('/complaints', fn () => view('penyewa.complaints.index'))->name('complaints.index');
    Route::get('/account', fn () => view('penyewa.account'))->name('account');
});























