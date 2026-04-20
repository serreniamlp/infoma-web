<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ResidenceApiController;
use App\Http\Controllers\Api\ActivityApiController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\User\ProfileController as UserProfileController;
use App\Http\Controllers\Api\User\ResidenceController as UserResidenceController;
use App\Http\Controllers\Api\User\ActivityController as UserActivityController;
use App\Http\Controllers\Api\User\BookingController as UserBookingController;
use App\Http\Controllers\Api\User\BookmarkController as UserBookmarkController;
use App\Http\Controllers\Api\User\RatingController as UserRatingController;
use App\Http\Controllers\Api\Provider\DashboardController as ProviderDashboardController;
use App\Http\Controllers\Api\Provider\ResidenceController as ProviderResidenceController;
use App\Http\Controllers\Api\Provider\ActivityController as ProviderActivityController;
use App\Http\Controllers\Api\Provider\BookingManagementController as ProviderBookingManagementController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;

Route::prefix('v1')->group(function () {

    // Public API endpoints
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/search', [HomeController::class, 'search']);
    Route::get('/categories', [HomeController::class, 'categories']);

    Route::get('/residences', [ResidenceApiController::class, 'index']);
    Route::get('/residences/{residence}', [ResidenceApiController::class, 'show']);
    Route::get('/activities', [ActivityApiController::class, 'index']);
    Route::get('/activities/{activity}', [ActivityApiController::class, 'show']);

    // Authentication endpoints
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
    });

    // Authenticated API endpoints
    Route::middleware('auth:sanctum')->group(function () {

        // ================== USER ==================
        Route::middleware('role:user')
            ->prefix('user')
            ->as('api.user.')  // Changed from 'user.' to 'api.user.'
            ->group(function () {
                // Profile
                Route::get('/profile', [UserProfileController::class, 'show']);
                Route::put('/profile', [UserProfileController::class, 'update']);

                // Residences
                Route::apiResource('residences', UserResidenceController::class);

                // Activities
                Route::apiResource('activities', UserActivityController::class);

                // Bookings - Define all routes manually to avoid conflicts
                Route::get('/bookings', [UserBookingController::class, 'index'])->name('bookings.index');
                Route::post('/bookings', [UserBookingController::class, 'store'])->name('bookings.store');
                Route::get('/bookings/{booking}', [UserBookingController::class, 'show'])->name('bookings.show');
                Route::put('/bookings/{booking}', [UserBookingController::class, 'update'])->name('bookings.update');
                Route::patch('/bookings/{booking}', [UserBookingController::class, 'update'])->name('bookings.patch');
                Route::delete('/bookings/{booking}', [UserBookingController::class, 'destroy'])->name('bookings.destroy');
                
                // Custom booking routes
                Route::get('/bookings/{booking}/payment', [UserBookingController::class, 'payment'])->name('bookings.payment');
                Route::post('/bookings/{booking}/payment', [UserBookingController::class, 'processPayment'])->name('bookings.processPayment');
                Route::patch('/bookings/{booking}/cancel', [UserBookingController::class, 'cancel'])->name('bookings.cancel');

                // Bookmarks
                Route::get('/bookmarks', [UserBookmarkController::class, 'index']);
                Route::post('/bookmarks', [UserBookmarkController::class, 'store']);
                Route::delete('/bookmarks', [UserBookmarkController::class, 'destroy']);
                Route::post('/bookmarks/toggle', [UserBookmarkController::class, 'toggle']);

                // Ratings
                Route::get('/ratings', [UserRatingController::class, 'show']);
                Route::post('/ratings', [UserRatingController::class, 'store']);
                Route::put('/ratings/{rating}', [UserRatingController::class, 'update']);
                Route::delete('/ratings', [UserRatingController::class, 'destroy']);
            });

        // ================== PROVIDER ==================
        Route::middleware('role:provider')
            ->prefix('provider')
            ->as('api.provider.')  // Changed from 'provider.' to 'api.provider.'
            ->group(function () {
                Route::get('/dashboard', [ProviderDashboardController::class, 'index']);
                Route::get('/dashboard/charts', [ProviderDashboardController::class, 'getChartData']);
                Route::get('/dashboard/stats', [ProviderDashboardController::class, 'getStats']);
                Route::get('/dashboard/export', [ProviderDashboardController::class, 'exportData']);

                Route::apiResource('residences', ProviderResidenceController::class);
                Route::patch('/residences/{residence}/toggle-status', [ProviderResidenceController::class, 'toggleStatus']);

                Route::apiResource('activities', ProviderActivityController::class);
                Route::patch('/activities/{activity}/toggle-status', [ProviderActivityController::class, 'toggleStatus']);

                Route::get('/bookings', [ProviderBookingManagementController::class, 'index']);
                Route::get('/bookings/{booking}', [ProviderBookingManagementController::class, 'show']);
                Route::patch('/bookings/{booking}/approve', [ProviderBookingManagementController::class, 'approve']);
                Route::patch('/bookings/{booking}/reject', [ProviderBookingManagementController::class, 'reject']);
            });

        // ================== ADMIN ==================
        Route::middleware('role:admin')
            ->prefix('admin')
            ->as('api.admin.')  // Changed from 'admin.' to 'api.admin.'
            ->group(function () {
                Route::get('/dashboard', [AdminDashboardController::class, 'index']);
                Route::get('/analytics', [AdminDashboardController::class, 'analytics']);
                Route::apiResource('users', UserManagementController::class);
                Route::get('/users/{user}/activities', [UserManagementController::class, 'activities']);
                Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus']);
            });
    });
});