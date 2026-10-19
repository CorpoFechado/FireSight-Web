<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\EducationController;
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
    // ── 1. Fire Safety Education ─────────────────────────────────────────
    Route::get('/education', [EducationController::class, 'index'])->name('api.v1.education.index');
    Route::get('/education/{id}', [EducationController::class, 'show'])
        ->whereNumber('id')
        ->name('api.v1.education.show');

    // Compatibility aliases with firesight-api .php paths
    Route::get('/education/list.php', [EducationController::class, 'index']);
    Route::get('/education/detail.php', [EducationController::class, 'show']);

    // ── 2. Emergency & Barangay Contacts ─────────────────────────────────
    Route::get('/contacts', [ContactController::class, 'index'])->name('api.v1.contacts.index');
    Route::get('/contacts/barangay', [ContactController::class, 'barangayContacts'])->name('api.v1.contacts.barangay');

    // Compatibility alias
    Route::get('/contacts/list.php', [ContactController::class, 'index']);

    // ── 3. Announcements ────────────────────────────────────────────────
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('api.v1.announcements.index');

    // Compatibility alias
    Route::get('/announcements/list.php', [AnnouncementController::class, 'index']);
});
