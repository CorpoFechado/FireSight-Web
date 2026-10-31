<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EducationController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
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

        // ── 6. Reports Endpoints ─────────────────────────────────────────
        Route::post('/reports', [ReportController::class, 'store'])->name('api.v1.reports.store');
        Route::get('/reports', [ReportController::class, 'index'])->name('api.v1.reports.index');
        Route::get('/reports/{id}', [ReportController::class, 'show'])->whereNumber('id')->name('api.v1.reports.show');
        Route::get('/reports/{id}/history', [ReportController::class, 'statusHistory'])->whereNumber('id')->name('api.v1.reports.history');

        // Reports Compatibility Aliases
        Route::post('/reports/create.php', [ReportController::class, 'store']);
        Route::get('/reports/list.php', [ReportController::class, 'index']);
        Route::get('/reports/detail.php', [ReportController::class, 'show']);
        Route::get('/reports/status_history.php', [ReportController::class, 'statusHistory']);

        // ── 7. Incidents Endpoints (BFP Operations) ───────────────────────
        Route::get('/incidents', [IncidentController::class, 'index'])->name('api.v1.incidents.index');
        Route::get('/incidents/{id}', [IncidentController::class, 'show'])->whereNumber('id')->name('api.v1.incidents.show');
        Route::post('/incidents/{id}/verify', [IncidentController::class, 'verify'])->whereNumber('id')->name('api.v1.incidents.verify');
        Route::post('/incidents/{id}/status', [IncidentController::class, 'updateStatus'])->whereNumber('id')->name('api.v1.incidents.status');
        Route::match(['put', 'post'], '/incidents/{id}/details', [IncidentController::class, 'updateDetails'])->whereNumber('id')->name('api.v1.incidents.details');
        Route::get('/incidents/{id}/history', [IncidentController::class, 'statusHistory'])->whereNumber('id')->name('api.v1.incidents.history');
        Route::post('/incidents/{id}/invalid', [IncidentController::class, 'markInvalid'])->whereNumber('id')->name('api.v1.incidents.invalid');
        Route::post('/incidents/{id}/evidence', [IncidentController::class, 'addEvidence'])->whereNumber('id')->name('api.v1.incidents.evidence');
        Route::post('/incidents/manual', [IncidentController::class, 'createManual'])->name('api.v1.incidents.manual');

        // Incidents Compatibility Aliases
        Route::get('/incidents/list.php', [IncidentController::class, 'index']);
        Route::get('/incidents/detail.php', [IncidentController::class, 'show']);
        Route::post('/incidents/verify.php', [IncidentController::class, 'verify']);
        Route::post('/incidents/update_status.php', [IncidentController::class, 'updateStatus']);
        Route::match(['put', 'post'], '/incidents/update_details.php', [IncidentController::class, 'updateDetails']);
        Route::get('/incidents/status_history.php', [IncidentController::class, 'statusHistory']);
        Route::post('/incidents/mark_invalid.php', [IncidentController::class, 'markInvalid']);
        Route::post('/incidents/add_evidence.php', [IncidentController::class, 'addEvidence']);
        Route::post('/incidents/create_manual.php', [IncidentController::class, 'createManual']);

        // ── 8. Profile Endpoints ─────────────────────────────────────────
        Route::get('/profile', [ProfileController::class, 'show'])->name('api.v1.profile.show');
        Route::match(['put', 'post'], '/profile', [ProfileController::class, 'update'])->name('api.v1.profile.update');
        Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto'])->name('api.v1.profile.photo');
        Route::match(['post', 'delete'], '/profile/delete-account', [ProfileController::class, 'deleteAccount'])->name('api.v1.profile.delete_account');

        // Profile Compatibility Aliases
        Route::get('/profile/get.php', [ProfileController::class, 'show']);
        Route::match(['put', 'post'], '/profile/update.php', [ProfileController::class, 'update']);
        Route::post('/profile/upload_photo.php', [ProfileController::class, 'uploadPhoto']);
        Route::match(['post', 'delete'], '/profile/delete_account.php', [ProfileController::class, 'deleteAccount']);

        // ── 9. Notifications Endpoints ───────────────────────────────────
        Route::get('/notifications', [NotificationController::class, 'index'])->name('api.v1.notifications.index');
        Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])->name('api.v1.notifications.mark_read');

        // Notifications Compatibility Aliases
        Route::get('/notifications/list.php', [NotificationController::class, 'index']);
        Route::post('/notifications/mark_read.php', [NotificationController::class, 'markRead']);

        // ── 10. Dashboard Analytics ──────────────────────────────────────
        Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('api.v1.dashboard.analytics');
        Route::get('/dashboard/analytics.php', [DashboardController::class, 'analytics']);
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
Route::get('/education/list.php', [EducationController::class, 'index']);
Route::get('/education/detail.php', [EducationController::class, 'show']);
Route::get('/announcements/list.php', [AnnouncementController::class, 'index']);
Route::get('/contacts/list.php', [ContactController::class, 'index']);

Route::middleware('auth.mobile')->group(function (): void {
    Route::get('/map/incidents_bfp.php', [MapController::class, 'incidentsBfp']);
    Route::get('/reports/area_status.php', [MapController::class, 'areaStatus']);

    // Reports legacy routes
    Route::post('/reports/create.php', [ReportController::class, 'store']);
    Route::get('/reports/list.php', [ReportController::class, 'index']);
    Route::get('/reports/detail.php', [ReportController::class, 'show']);
    Route::get('/reports/status_history.php', [ReportController::class, 'statusHistory']);

    // Incidents legacy routes
    Route::get('/incidents/list.php', [IncidentController::class, 'index']);
    Route::get('/incidents/detail.php', [IncidentController::class, 'show']);
    Route::post('/incidents/verify.php', [IncidentController::class, 'verify']);
    Route::post('/incidents/update_status.php', [IncidentController::class, 'updateStatus']);
    Route::match(['put', 'post'], '/incidents/update_details.php', [IncidentController::class, 'updateDetails']);
    Route::get('/incidents/status_history.php', [IncidentController::class, 'statusHistory']);
    Route::post('/incidents/mark_invalid.php', [IncidentController::class, 'markInvalid']);
    Route::post('/incidents/add_evidence.php', [IncidentController::class, 'addEvidence']);
    Route::post('/incidents/create_manual.php', [IncidentController::class, 'createManual']);

    // Profile legacy routes
    Route::get('/profile/get.php', [ProfileController::class, 'show']);
    Route::match(['put', 'post'], '/profile/update.php', [ProfileController::class, 'update']);
    Route::post('/profile/upload_photo.php', [ProfileController::class, 'uploadPhoto']);
    Route::match(['post', 'delete'], '/profile/delete_account.php', [ProfileController::class, 'deleteAccount']);

    // Notifications legacy routes
    Route::get('/notifications/list.php', [NotificationController::class, 'index']);
    Route::post('/notifications/mark_read.php', [NotificationController::class, 'markRead']);

    // Dashboard analytics legacy route
    Route::get('/dashboard/analytics.php', [DashboardController::class, 'analytics']);
});
