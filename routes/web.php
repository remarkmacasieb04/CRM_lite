<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Clients\ClientAttachmentController;
use App\Http\Controllers\Clients\ClientCommunicationController;
use App\Http\Controllers\Clients\ClientController;
use App\Http\Controllers\Clients\ClientDocumentController;
use App\Http\Controllers\Clients\ClientNoteController;
use App\Http\Controllers\Clients\ClientPortalShareController;
use App\Http\Controllers\Clients\SavedClientViewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Portal\ClientPortalController;
use App\Http\Controllers\Tasks\TaskController;
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
Route::get('portal/{token}', [ClientPortalController::class, 'show'])->name('portal.show');

Route::middleware(['auth', 'workspace', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
        Route::patch('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    });

    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('clients/board', [ClientController::class, 'board'])->name('clients.board');
    Route::get('clients/export', [ClientController::class, 'exportCsv'])->name('clients.export');
    Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('clients/import', [ClientController::class, 'importCsv'])->name('clients.import');
    Route::post('clients/saved-views', [SavedClientViewController::class, 'store'])->name('clients.saved-views.store');
    Route::delete('clients/saved-views/{savedClientView}', [SavedClientViewController::class, 'destroy'])->name('clients.saved-views.destroy');
    Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::patch('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::patch('clients/{client}/status', [ClientController::class, 'updateStatus'])->name('clients.status.update');
    Route::patch('clients/{client}/contacted', [ClientController::class, 'markContacted'])->name('clients.contacted');
    Route::patch('clients/{client}/archive', [ClientController::class, 'archive'])->name('clients.archive');
    Route::patch('clients/{client}/restore', [ClientController::class, 'restore'])->name('clients.restore');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::post('clients/{client}/notes', [ClientNoteController::class, 'store'])->name('clients.notes.store');
    Route::post('clients/{client}/communications', [ClientCommunicationController::class, 'store'])->name('clients.communications.store');
    Route::post('clients/{client}/documents', [ClientDocumentController::class, 'store'])->name('clients.documents.store');
    Route::patch('documents/{document}/status', [ClientDocumentController::class, 'updateStatus'])->name('clients.documents.status.update');
    Route::delete('documents/{document}', [ClientDocumentController::class, 'destroy'])->name('clients.documents.destroy');
    Route::post('clients/{client}/portal-share', [ClientPortalShareController::class, 'store'])->name('clients.portal-share.store');
    Route::delete('portal-shares/{share}', [ClientPortalShareController::class, 'destroy'])->name('clients.portal-share.destroy');
    Route::post('clients/{client}/attachments', [ClientAttachmentController::class, 'store'])->name('clients.attachments.store');
    Route::get('attachments/{attachment}/download', [ClientAttachmentController::class, 'download'])->name('clients.attachments.download');
    Route::delete('attachments/{attachment}', [ClientAttachmentController::class, 'destroy'])->name('clients.attachments.destroy');
});

require __DIR__.'/settings.php';
