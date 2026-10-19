<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\EducationController;
use App\Http\Controllers\Api\MapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile Application API Routes
|--------------------------------------------------------------------------
|
| Dedicated endpoints for the FireSight mobile application.
| All endpoints return pure JSON matching the mobile ApiClient expectations.
|
*/

Route::prefix('v1')->group(function (): void {
    // ── 1. Authentication ────────────────────────────────────────────────
    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
        Route::post('/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.v1.auth.forgot_password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('api.v1.auth.reset_password');
        Route::post('/google-login', [AuthController::class, 'googleLogin'])->name('api.v1.auth.google_login');

        Route::middleware('auth.mobile')->group(function (): void {
            Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        });

        // Compatibility aliases with firesight-api
        Route::post('/login.php', [AuthController::class, 'login']);
        Route::post('/register.php', [AuthController::class, 'register']);
        Route::post('/forgot_password.php', [AuthController::class, 'forgotPassword']);
        Route::post('/reset_password.php', [AuthController::class, 'resetPassword']);
        Route::post('/google_login.php', [AuthController::class, 'googleLogin']);
        Route::post('/logout.php', [AuthController::class, 'logout'])->middleware('auth.mobile');
    });

    // ── 2. Fire Safety Education ─────────────────────────────────────────
    Route::get('/education', [EducationController::class, 'index'])->name('api.v1.education.index');
    Route::get('/education/{id}', [EducationController::class, 'show'])
        ->whereNumber('id')
        ->name('api.v1.education.show');

    // Compatibility aliases with firesight-api .php paths
    Route::get('/education/list.php', [EducationController::class, 'index']);
    Route::get('/education/detail.php', [EducationController::class, 'show']);

    // ── 3. Emergency & Barangay Contacts ─────────────────────────────────
    Route::get('/contacts', [ContactController::class, 'index'])->name('api.v1.contacts.index');
    Route::get('/contacts/barangay', [ContactController::class, 'barangayContacts'])->name('api.v1.contacts.barangay');

    // Compatibility alias
    Route::get('/contacts/list.php', [ContactController::class, 'index']);

    // ── 4. Announcements ────────────────────────────────────────────────
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('api.v1.announcements.index');

    // Compatibility alias
    Route::get('/announcements/list.php', [AnnouncementController::class, 'index']);

    // ── 5. Map & Risk Endpoints ──────────────────────────────────────────
    Route::get('/map/incidents', [MapController::class, 'incidents'])->name('api.v1.map.incidents');
    Route::get('/map/barangay-risk', [MapController::class, 'barangayRisk'])->name('api.v1.map.barangay_risk');

    Route::middleware('auth.mobile')->group(function (): void {
        Route::get('/map/incidents-bfp', [MapController::class, 'incidentsBfp'])->name('api.v1.map.incidents_bfp');
        Route::get('/reports/area-status', [MapController::class, 'areaStatus'])->name('api.v1.reports.area_status');
    });

    // Compatibility aliases with firesight-api
    Route::get('/map/incidents.php', [MapController::class, 'incidents']);
    Route::get('/map/barangay_risk.php', [MapController::class, 'barangayRisk']);
    Route::get('/map/incidents_bfp.php', [MapController::class, 'incidentsBfp'])->middleware('auth.mobile');
    Route::get('/reports/area_status.php', [MapController::class, 'areaStatus'])->middleware('auth.mobile');
});

// Root-level legacy compatibility routes for direct /api/*.php requests
Route::prefix('auth')->group(function (): void {
    Route::post('/login.php', [AuthController::class, 'login']);
    Route::post('/register.php', [AuthController::class, 'register']);
    Route::post('/forgot_password.php', [AuthController::class, 'forgotPassword']);
    Route::post('/reset_password.php', [AuthController::class, 'resetPassword']);
    Route::post('/google_login.php', [AuthController::class, 'googleLogin']);
    Route::post('/logout.php', [AuthController::class, 'logout'])->middleware('auth.mobile');
});

Route::get('/map/incidents.php', [MapController::class, 'incidents']);
Route::get('/map/barangay_risk.php', [MapController::class, 'barangayRisk']);
Route::get('/map/incidents_bfp.php', [MapController::class, 'incidentsBfp'])->middleware('auth.mobile');
Route::get('/reports/area_status.php', [MapController::class, 'areaStatus'])->middleware('auth.mobile');
