<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BoardUserController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CardLabelController;
use App\Http\Controllers\CardUserController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ChecklistItemController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FallbackController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceUserController;


// AUTH ROUTES
Route::controller(AuthController::class)->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', 'login')->name('auth.login');
        Route::post('register', 'register')->name('auth.register');
        Route::post('forgot-password', 'forgot-password')->name('auth.password.forgot');
        Route::post('reset-password', 'reset-password')->name('auth.password.reset');
        Route::prefix('email')->group(function () {
            Route::post('verify', 'verify')->name('auth.email.verify');
            Route::post('resend', 'resend')->name('auth.email.resend');
        });

        Route::middleware('auth:api')->group(function () {
            Route::post('logout', 'logout')->name('auth.logout');
            Route::post('refresh', 'refresh')->name('auth.refresh');
            Route::post('change-password', 'change-password')->name('auth.password.change');
            Route::get('me', 'me')->name('auth.me');
        });
    });
});


// MAIN RESOURCES
Route::apiResources([
    'users' => UserController::class,
    'workspace' => WorkspaceController::class,
    'board' => BoardController::class,
    'lists' => ListController::class,
    'cards' => CardController::class,
]);


// WORKSPACE USER
Route::prefix('workspaces/{workspace}')->group(function () {
    Route::get('users', [WorkspaceUserController::class, 'index']);
    Route::post('users', [WorkspaceUserController::class, 'store'])->name('workspaces.users.add');
    Route::delete('users/{user}', [WorkspaceUserController::class, 'destroy'])->name('workspaces.users.remove');
});


// BOARD USER
Route::prefix('boards/{board}')->group(function () {
    Route::get('users', [BoardUserController::class, 'index']);
    Route::post('users', [BoardUserController::class, 'store'])->name('boards.join');
    Route::delete('users/{user}', [BoardUserController::class, 'destroy'])->name('boards.leave');

    // Labels
    Route::apiResource('labels', LabelController::class)->only(['index', 'store', 'update', 'destroy']);

    // Activities (read-only)
    Route::get('activities', [ActivityController::class, 'index'])->name('boards.activities.index');
});


// CARD RELATIONS
Route::prefix('cards/{card}')->group(function () {
    // users
    Route::get('users', [CardUserController::class, 'index']);
    Route::post('users', [CardUserController::class, 'store'])->name('cards.users.assign');
    Route::delete('users/{user}', [CardUserController::class, 'destroy'])->name('cards.users.unassign');

    // Labels
    Route::get('labels', [CardLabelController::class, 'index']);
    Route::post('labels', [CardLabelController::class, 'store'])->name('cards.labels.attach');
    Route::delete('labels/{label}', [CardLabelController::class, 'destroy'])->name('cards.labels.detach');

    // Checklists
    Route::apiResource('checklists', ChecklistController::class)->only(['index', 'store', 'update', 'destroy']);

    // Attachments
    Route::apiResource('attachments', AttachmentController::class)->only(['index', 'store', 'destroy']);

    // Upload file 
    Route::post('attachments', [AttachmentController::class, 'upload'])
        ->middleware('auth:api')
        ->name('cards.attachments.upload');

    // Comments
    Route::apiResource('comments', CommentController::class)->only(['index', 'store', 'update', 'destroy']);
});


// CHECKLIST ITEMS (nested)
Route::prefix('checklists/{checklist}')->group(function () {
    Route::apiResource('items', ChecklistItemController::class)->only(['index', 'store', 'update', 'destroy']);
});

// Search boards
Route::get('/boards/search', [BoardController::class, 'search'])
    ->name('boards.search');

// Search cards
Route::get('/cards/search', [CardController::class, 'search'])
    ->name('cards.search');

// Search users 
Route::get('/users/search', [UserController::class, 'search'])
    ->name('users.search');

// Search workspaces
Route::get('/workspaces/search', [WorkspaceController::class, 'search'])
    ->name('workspaces.search');

// Fallback (code 404)
Route::fallback(FallbackController::class);
