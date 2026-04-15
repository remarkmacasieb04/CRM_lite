<?php

use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\Settings\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'workspace'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('settings/profile/reminders', [ProfileController::class, 'updateReminderPreferences'])->name('profile.reminders.update');
    Route::get('settings/workspace', [WorkspaceController::class, 'edit'])->name('workspace.edit');
    Route::post('settings/workspace', [WorkspaceController::class, 'store'])->name('workspace.store');
    Route::patch('settings/workspace/{workspace}/switch', [WorkspaceController::class, 'switch'])->name('workspace.switch');
    Route::post('settings/workspace/members', [WorkspaceController::class, 'storeMember'])->name('workspace.members.store');
});

Route::middleware(['auth', 'workspace', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');
});
