<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ResidenceApiController;
use App\Http\Controllers\Api\ActivityApiController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\User\ProfileController as UserProfileController;
use App\Http\Controllers\Api\User\BookingController as UserBookingController;
use App\Http\Controllers\Api\User\BookmarkController as UserBookmarkController;
use App\Http\Controllers\Api\User\RatingController as UserRatingController;
use App\Http\Controllers\Api\User\MarketplaceTransactionController;
use App\Http\Controllers\Api\User\SellerController as ApiSellerController;
use App\Http\Controllers\Api\Provider\DashboardController as ProviderDashboardController;
use App\Http\Controllers\Api\Provider\ResidenceController as ProviderResidenceController;
use App\Http\Controllers\Api\Provider\ActivityController as ProviderActivityController;
use App\Http\Controllers\Api\Provider\BookingManagementController as ProviderBookingController;

Route::prefix('v1')->group(function () {

    // ================================================================
    // PUBLIC
    // ================================================================
    Route::get('/',          [HomeController::class, 'index']);
    Route::get('/search',    [HomeController::class, 'search']);
    Route::get('/categories',[HomeController::class, 'categories']);

    Route::get('/residences',        [ResidenceApiController::class, 'index']);
    Route::get('/residences/{residence}', [ResidenceApiController::class, 'show']);

    Route::get('/activities',        [ActivityApiController::class, 'index']);
    Route::get('/activities/{activity}', [ActivityApiController::class, 'show']);

    Route::get('/marketplace',       [MarketplaceController::class, 'index']);
    Route::get('/marketplace/{product}', [MarketplaceController::class, 'show']);

    // ================================================================
    // AUTH
    // ================================================================
    Route::prefix('auth')->group(function () {
        Route::post('/login',    [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout',  [AuthController::class, 'logout']);
            Route::get('/me',       [AuthController::class, 'me']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });
    });

    // ================================================================
    // AUTHENTICATED
    // ================================================================
    Route::middleware('auth:sanctum')->group(function () {

        // --- Notifikasi (semua role) ---
        Route::prefix('notifications')->group(function () {
            Route::get('/',              [NotificationController::class, 'index']);
            Route::get('/count',         [NotificationController::class, 'count']);
            Route::patch('/{id}/read',   [NotificationController::class, 'markRead']);
            Route::patch('/read-all',    [NotificationController::class, 'markAllRead']);
        });

        // ============================================================
        // USER — BUYER
        // ============================================================
        Route::middleware('role:user')->prefix('user')->group(function () {

            // Profil
            Route::get('/profile',  [UserProfileController::class, 'show']);
            Route::put('/profile',  [UserProfileController::class, 'update']);

            // Booking
            Route::get('/bookings',                         [UserBookingController::class, 'index']);
            Route::post('/bookings',                        [UserBookingController::class, 'store']);
            Route::get('/bookings/{booking}',               [UserBookingController::class, 'show']);
            Route::patch('/bookings/{booking}/cancel',      [UserBookingController::class, 'cancel']);
            Route::post('/bookings/{booking}/payment',      [UserBookingController::class, 'processPayment']);

            // Bookmark
            Route::get('/bookmarks',         [UserBookmarkController::class, 'index']);
            Route::post('/bookmarks/toggle', [UserBookmarkController::class, 'toggle']);

            // Rating
            Route::post('/ratings',           [UserRatingController::class, 'store']);
            Route::put('/ratings/{rating}',   [UserRatingController::class, 'update']);
            Route::delete('/ratings/{rating}',[UserRatingController::class, 'destroy']);

            // Transaksi Marketplace — sisi buyer
            Route::get('/transactions',                              [MarketplaceTransactionController::class, 'index']);
            Route::get('/transactions/{transaction}',                [MarketplaceTransactionController::class, 'show']);
            Route::post('/transactions/{product}',                   [MarketplaceTransactionController::class, 'store']);
            Route::patch('/transactions/{transaction}/cancel',       [MarketplaceTransactionController::class, 'cancel']);
            Route::post('/transactions/{transaction}/payment-proof', [MarketplaceTransactionController::class, 'uploadPaymentProof']);

            // Seller
            Route::prefix('seller')->group(function () {
                Route::get('/status',                          [ApiSellerController::class, 'status']);
                Route::post('/activate',                       [ApiSellerController::class, 'activate']);
                Route::get('/home',                            [ApiSellerController::class, 'home']);
                Route::get('/products',                        [ApiSellerController::class, 'products']);
                Route::post('/products',                       [ApiSellerController::class, 'storeProduct']);
                Route::put('/products/{product}',              [ApiSellerController::class, 'updateProduct']);
                Route::delete('/products/{product}',           [ApiSellerController::class, 'destroyProduct']);
                Route::get('/orders',                          [ApiSellerController::class, 'orders']);
                Route::get('/orders/{transaction}',            [ApiSellerController::class, 'orderShow']);
                Route::patch('/orders/{transaction}/status',   [ApiSellerController::class, 'updateOrderStatus']);
            });
        });

        // ============================================================
        // PROVIDER HUNIAN
        // ============================================================
        Route::middleware('role:provider_residence')
            ->prefix('provider/residence')
            ->group(function () {
                Route::get('/dashboard', [ProviderDashboardController::class, 'index']);

                Route::get('/residences',                          [ProviderResidenceController::class, 'index']);
                Route::post('/residences',                         [ProviderResidenceController::class, 'store']);
                Route::get('/residences/{residence}',              [ProviderResidenceController::class, 'show']);
                Route::put('/residences/{residence}',              [ProviderResidenceController::class, 'update']);
                Route::delete('/residences/{residence}',           [ProviderResidenceController::class, 'destroy']);
                Route::patch('/residences/{residence}/toggle-status', [ProviderResidenceController::class, 'toggleStatus']);

                Route::get('/bookings',                            [ProviderBookingController::class, 'index']);
                Route::get('/bookings/{booking}',                  [ProviderBookingController::class, 'show']);
                Route::patch('/bookings/{booking}/approve',        [ProviderBookingController::class, 'approve']);
                Route::patch('/bookings/{booking}/reject',         [ProviderBookingController::class, 'reject']);
            });

        // ============================================================
        // PROVIDER EVENT
        // ============================================================
        Route::middleware('role:provider_event')
            ->prefix('provider/event')
            ->group(function () {
                Route::get('/dashboard', [ProviderDashboardController::class, 'index']);

                Route::get('/activities',                          [ProviderActivityController::class, 'index']);
                Route::post('/activities',                         [ProviderActivityController::class, 'store']);
                Route::get('/activities/{activity}',               [ProviderActivityController::class, 'show']);
                Route::put('/activities/{activity}',               [ProviderActivityController::class, 'update']);
                Route::delete('/activities/{activity}',            [ProviderActivityController::class, 'destroy']);
                Route::patch('/activities/{activity}/toggle-status', [ProviderActivityController::class, 'toggleStatus']);

                Route::get('/bookings',                            [ProviderBookingController::class, 'index']);
                Route::get('/bookings/{booking}',                  [ProviderBookingController::class, 'show']);
                Route::patch('/bookings/{booking}/approve',        [ProviderBookingController::class, 'approve']);
                Route::patch('/bookings/{booking}/reject',         [ProviderBookingController::class, 'reject']);
            });
    });
});