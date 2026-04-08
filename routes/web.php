<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Clients\ClientAttachmentController;
use App\Http\Controllers\Clients\ClientController;
use App\Http\Controllers\Clients\ClientNoteController;
use App\Http\Controllers\Clients\SavedClientViewController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
Route::delete('auth/{provider}', [SocialAuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('social.destroy');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
        Route::patch('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    });

    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('clients/export', [ClientController::class, 'exportCsv'])->name('clients.export');
    Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('clients/import', [ClientController::class, 'importCsv'])->name('clients.import');
    Route::post('clients/saved-views', [SavedClientViewController::class, 'store'])->name('clients.saved-views.store');
    Route::delete('clients/saved-views/{savedClientView}', [SavedClientViewController::class, 'destroy'])->name('clients.saved-views.destroy');
    Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::patch('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::patch('clients/{client}/contacted', [ClientController::class, 'markContacted'])->name('clients.contacted');
    Route::patch('clients/{client}/archive', [ClientController::class, 'archive'])->name('clients.archive');
    Route::patch('clients/{client}/restore', [ClientController::class, 'restore'])->name('clients.restore');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::post('clients/{client}/notes', [ClientNoteController::class, 'store'])->name('clients.notes.store');
    Route::post('clients/{client}/attachments', [ClientAttachmentController::class, 'store'])->name('clients.attachments.store');
    Route::get('attachments/{attachment}/download', [ClientAttachmentController::class, 'download'])->name('clients.attachments.download');
    Route::delete('attachments/{attachment}', [ClientAttachmentController::class, 'destroy'])->name('clients.attachments.destroy');
});

require __DIR__.'/settings.php';
