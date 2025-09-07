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


/******************* AUTH ROUTES *******************/
Route::controller(AuthController::class)->name('auth.')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', 'login')->middleware(['throttle:login'])->name('login');
        Route::post('register', 'register')->name('register');
        Route::post('forgot-password', 'forgotPassword')->name('password.forgot');
        Route::post('reset-password', 'resetPassword')->name('password.reset');
        Route::prefix('email')->group(function () {
            Route::post('verify', 'verify')->name('email.verify');
            Route::post('resend', 'resend')->name('email.resend');
        });

        Route::middleware('[auth:api]')->group(function () {
            Route::post('logout', 'logout')->name('logout');
            Route::post('refresh', 'refresh')->name('refresh');
            Route::post('change-password', 'changePassword')->name('password.change');
            Route::get('me', 'me')->name('me');
        });
    });
});


/******************* USER ROUTES *******************/
Route::apiResource('users', UserController::class)
    ->middlewareFor(
        ['index', 'show', 'store', 'update', 'destroy'],
        ['auth:api']
    );


/******************* WORKSPACE ROUTES *******************/
Route::apiResource('workspaces', WorkspaceController::class)
    ->middlewareFor(
        ['index', 'show', 'store', 'update', 'destroy'],
        ['auth:api']
    );


/******************* BOARD ROUTES *******************/
Route::apiResource('boards', BoardController::class)
    ->middlewareFor(
        ['index', 'show', 'store', 'update', 'destroy'],
        ['auth:api']
    );


/******************* LIST ROUTES *******************/
Route::apiResource('lists', ListController::class)
    ->middlewareFor(
        ['index', 'show', 'store', 'update', 'destroy'],
        ['auth:api']
    );


/******************* CARD ROUTES *******************/
Route::apiResource('cards', CardController::class)
    ->middlewareFor(
        ['index', 'show', 'store', 'update', 'destroy'],
        ['auth:api']
    );


/******************* WORKSPACE USER *******************/
Route::prefix('workspaces/{workspace}')->name('workspaces.')->group(function () {
    Route::controller(WorkspaceUserController::class)->group(function () {
        Route::get('users', 'index')->name('users');
        Route::post('users', 'store')->name('users.add');
        Route::delete('users/{user}', 'destroy')->name('users.remove');
    });
});


/******************* BOARD USER *******************/
Route::prefix('boards/{board}')->name('boards.')->group(function () {
    Route::controller(BoardUserController::class)->group(function () {
        Route::get('users', 'index')->name('users');
        Route::post('users', 'store')->name('users.join');
        Route::delete('users/{user}', 'destroy')->name('users.leave');
    });

    //Labels
    Route::apiResource('labels', LabelController::class);

    //Activities (read-only)
    Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
});


/******************* CARD RELATIONS *******************/
Route::prefix('cards/{card}')->group(function () {
    //users
    Route::controller(CardUserController::class)->group(function () {
        Route::get('users', 'index')->name('cards.users');
        Route::post('users', 'store')->name('cards.users.assign');
        Route::delete('users/{user}', 'destroy')->name('cards.users.unassign');
    });

    //Labels
    Route::controller(CardLabelController::class)->group(function () {
        Route::get('users', 'index')->name('cards.labels');
        Route::post('users', 'store')->name('cards.labels.attach');
        Route::delete('users/{user}', 'destroy')->name('cards.labels.detach');
    });

    // Checklists
    Route::apiResource('checklists', ChecklistController::class);

    // Attachments
    Route::apiResource('attachments', AttachmentController::class);

    //Upload file 
    Route::post('attachments', [AttachmentController::class, 'upload'])
        ->middleware('auth:api')
        ->name('cards.attachments.upload');

    //Comments
    Route::apiResource('comments', CommentController::class)->except('update');
});


/******************* CHECKLIST ITEMS (nested) *******************/
Route::prefix('checklists/{checklist}')->group(function () {
    Route::apiResource('items', ChecklistItemController::class);
});


/******************* SEARCH *******************/
//Search boards
Route::get('/boards/search', [BoardController::class, 'search'])
    ->name('boards.search');

//Search cards
Route::get('/cards/search', [CardController::class, 'search'])
    ->name('cards.search');

//Search users 
Route::get('/users/search', [UserController::class, 'search'])
    ->name('users.search');

//Search workspaces
Route::get('/workspaces/search', [WorkspaceController::class, 'search'])
    ->name('workspaces.search');


/******************* FALLBACK *******************/
Route::fallback(FallbackController::class);
