<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DutyScheduleController;
use App\Http\Controllers\FireMapController;
use App\Http\Controllers\IncidentActionController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\RiskAnalyticsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified', 'bfp.staff'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('incidents', [IncidentController::class, 'index'])->name('incidents.index');
    Route::get('incidents/{report}', [IncidentController::class, 'show'])->name('incidents.show');
    Route::patch('incidents/{report}/status', [IncidentActionController::class, 'updateStatus'])
        ->name('incidents.updateStatus');
    Route::post('incidents/{report}/complete', [IncidentActionController::class, 'complete'])
        ->name('incidents.complete');

    Route::get('map', [FireMapController::class, 'index'])->name('map');
    Route::get('fire-prone', [RiskAnalyticsController::class, 'index'])->name('fireProne');
    // Static stand-in until the actual Dijkstra routing + dispatch selection
    // logic is implemented; see resources/js/pages/response-tracking/index.tsx.
    // Incident location is community_report #44's real coordinates (barangay
    // Prenza, via incident_record #36); distance/ETA below are a straight-line
    // estimate, not computed by a real routing engine yet.
    Route::inertia('response', 'response-tracking/index', [
        'incident' => [
            'reference' => 'INC-2026-0036',
            'type' => 'Other (Open Burning)',
            'barangay' => 'Prenza',
            'severity' => 'Low',
            'reporter' => 'Juan Corpuz',
            'latitude' => 13.99796296,
            'longitude' => 120.65501697,
        ],
        'routeSummary' => [
            'distanceKm' => 5.1,
            'etaMinutes' => 8,
            'routeType' => 'Optimal',
            'algorithm' => 'Dijkstra',
        ],
        'directions' => [
            'Head north on BFP Lian Station access road',
            'Turn right onto Lian-Nasugbu National Highway',
            'Continue on National Highway',
            'Turn left onto Prenza Barangay Road',
            'Arrive at incident location on the right',
        ],
        'station' => [
            'name' => 'BFP Lian Station',
        ],
    ])->name('response');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.readAll');

    Route::middleware('bfp.admin')->group(function () {
        Route::post('incidents/{report}/verify', [IncidentActionController::class, 'verify'])
            ->name('incidents.verify');
        Route::post('incidents/{report}/reject', [IncidentActionController::class, 'reject'])
            ->name('incidents.reject');

        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics');

        Route::get('personnel', [PersonnelController::class, 'index'])->name('personnel');
        Route::post('personnel', [PersonnelController::class, 'store'])->name('personnel.store');
        Route::patch('personnel/{user}', [PersonnelController::class, 'update'])->name('personnel.update');
        Route::delete('personnel/{user}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');

        Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements');
        Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::patch('announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

        Route::get('duty-schedule', [DutyScheduleController::class, 'index'])->name('dutySchedule');
        Route::post('duty-schedule', [DutyScheduleController::class, 'store'])->name('dutySchedule.store');
        Route::patch('duty-schedule/{schedule}', [DutyScheduleController::class, 'update'])->name('dutySchedule.update');
        Route::delete('duty-schedule/{schedule}', [DutyScheduleController::class, 'destroy'])->name('dutySchedule.destroy');
    });
});

require __DIR__.'/settings.php';
