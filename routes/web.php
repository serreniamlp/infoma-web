<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\User\ResidenceController as UserResidenceController;
use App\Http\Controllers\User\ActivityController as UserActivityController;
use App\Http\Controllers\User\BookingController as UserBookingController;
use App\Http\Controllers\User\BookmarkController;
use App\Http\Controllers\User\RatingController;
use App\Http\Controllers\Provider\DashboardController as ProviderDashboardController;
use App\Http\Controllers\Provider\ResidenceController as ProviderResidenceController;
use App\Http\Controllers\Provider\ActivityController as ProviderActivityController;
use App\Http\Controllers\Provider\BookingManagementController;
use App\Http\Controllers\Provider\MarketplaceTransactionController as ProviderMarketplaceTransactionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\MarketplaceTransactionController as UserMarketplaceTransactionController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MarketplaceTransactionController;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [HomeController::class, 'search'])->name('search');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// User Routes (Public - can view without login)
Route::get('/residences', [UserResidenceController::class, 'index'])->name('residences.index');
Route::get('/residences/{residence}', [UserResidenceController::class, 'show'])->name('residences.show');
Route::get('/activities', [UserActivityController::class, 'index'])->name('activities.index');
Route::get('/activities/{activity}', [UserActivityController::class, 'show'])->name('activities.show');

// Marketplace Routes (Public - can view without login)
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/{product}', [MarketplaceController::class, 'show'])->name('marketplace.show');

// Authentication required routes
Route::middleware('auth')->group(function () {

    // Profile (available to any authenticated user)
    Route::get('/profile', [ProfileController::class, 'show'])->name('user.profile.show');

    // User specific routes
    Route::middleware('role:user')->prefix('user')->name('user.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/history', [UserDashboardController::class, 'history'])->name('history');

        // Bookings
        Route::get('/bookings', [UserBookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [UserBookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [UserBookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}', [UserBookingController::class, 'show'])->name('bookings.show');
        Route::patch('/bookings/{booking}/cancel', [UserBookingController::class, 'cancel'])->name('bookings.cancel');
        Route::get('/bookings/{booking}/payment', [UserBookingController::class, 'payment'])->name('bookings.payment');
        Route::post('/bookings/{booking}/payment', [UserBookingController::class, 'processPayment'])->name('bookings.processPayment');

        // Bookmarks
        Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
        Route::post('/bookmarks', [BookmarkController::class, 'store'])->name('bookmarks.store');
        Route::delete('/bookmarks', [BookmarkController::class, 'destroy'])->name('bookmarks.destroy');

        // Ratings
        Route::get('/ratings', [RatingController::class, 'show'])->name('ratings.show');
        Route::post('/ratings', [RatingController::class, 'store'])->name('ratings.store');
        Route::delete('/ratings', [RatingController::class, 'destroy'])->name('ratings.destroy');

        // User Marketplace Transactions
        Route::prefix('marketplace')->name('marketplace.')->group(function () {
            Route::prefix('transactions')->name('transactions.')->group(function () {
                Route::get('/', [UserMarketplaceTransactionController::class, 'index'])->name('index');
                Route::get('/{transaction}', [UserMarketplaceTransactionController::class, 'show'])->name('show');
                Route::post('/{transaction}/upload-payment-proof', [UserMarketplaceTransactionController::class, 'uploadPaymentProof'])->name('upload-payment-proof');
                Route::post('/{transaction}/rate', [UserMarketplaceTransactionController::class, 'rate'])->name('rate');
                Route::patch('/{transaction}/cancel', [UserMarketplaceTransactionController::class, 'cancel'])->name('cancel');
            });
        });
    });

    // Marketplace Routes (Authentication required)
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        // Public routes (available to all authenticated users)
        Route::get('/bookmarks', [MarketplaceController::class, 'bookmarks'])->name('bookmarks');
        Route::post('/{product}/bookmark', [MarketplaceController::class, 'toggleBookmark'])->name('bookmark');

        // Transaction Routes (create and store - available to all authenticated users)
        Route::get('/transactions/create/{product}', [MarketplaceTransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions/{product}', [MarketplaceTransactionController::class, 'store'])->name('transactions.store');
    });

    // Provider routes
    Route::middleware('role:provider')->prefix('provider')->name('provider.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [ProviderDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/charts-data', [ProviderDashboardController::class, 'getChartsData'])->name('dashboard.charts');

        // Residences Management
        Route::resource('residences', ProviderResidenceController::class);
        Route::patch('/residences/{residence}/toggle-status', [ProviderResidenceController::class, 'toggleStatus'])
            ->name('residences.toggleStatus');

        // Activities Management
        Route::resource('activities', ProviderActivityController::class);
        Route::patch('/activities/{activity}/toggle-status', [ProviderActivityController::class, 'toggleStatus'])
            ->name('activities.toggleStatus');

        // Booking Management
        Route::get('/bookings', [BookingManagementController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [BookingManagementController::class, 'show'])->name('bookings.show');
        Route::patch('/bookings/{booking}/approve', [BookingManagementController::class, 'approve'])->name('bookings.approve');
        Route::patch('/bookings/{booking}/reject', [BookingManagementController::class, 'reject'])->name('bookings.reject');

        // Marketplace Product Management (Provider only)
        Route::prefix('marketplace')->name('marketplace.')->group(function () {
            Route::get('/create', [MarketplaceController::class, 'create'])->name('create');
            Route::post('/', [MarketplaceController::class, 'store'])->name('store');
            Route::get('/edit/{product}', [MarketplaceController::class, 'edit'])->name('edit');
            Route::put('/{product}', [MarketplaceController::class, 'update'])->name('update');
            Route::delete('/{product}', [MarketplaceController::class, 'destroy'])->name('destroy');
            Route::get('/my-products', [MarketplaceController::class, 'myProducts'])->name('my-products');
            
            // Marketplace Transaction Management (Provider only)
            Route::prefix('transactions')->name('transactions.')->group(function () {
                Route::get('/', [ProviderMarketplaceTransactionController::class, 'index'])->name('index');
                Route::get('/{transaction}', [ProviderMarketplaceTransactionController::class, 'show'])->name('show');
                Route::put('/{transaction}/status', [ProviderMarketplaceTransactionController::class, 'updateStatus'])->name('update-status');
                Route::post('/{transaction}/confirm-payment', [ProviderMarketplaceTransactionController::class, 'confirmPayment'])->name('confirm-payment');
                Route::get('/statistics/data', [ProviderMarketplaceTransactionController::class, 'getStatistics'])->name('statistics');
            });
        });
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('analytics');

        // User Management
        Route::resource('users', UserManagementController::class);
        Route::get('/users/{user}/activities', [UserManagementController::class, 'activities'])->name('users.activities');
        Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])
            ->name('users.toggleStatus');
    });
});
