<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatsController;
use App\Http\Controllers\Api\GabungsController;
use App\Http\Controllers\Api\KeluarController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* ====================================================== TOKEN ==================================================== */

Route::prefix('token')->group(function () {
    Route::post('create', [AuthController::class, 'login'])->name('login');
});
/* ====================================================== END TOKEN ==================================================== */

/* ====================================================== AUTH ==================================================== */
Route::prefix('auth')->group(function () {

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->name('login');

    //Protecting Routes
    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('list', [AuthController::class, 'list']);
        Route::get('list/{type}', [AuthController::class, 'list']);
        Route::put('update', [AuthController::class, 'update']);
        Route::put('update-status', [AuthController::class, 'updateStatus']);
        Route::put('chage-password', [AuthController::class, 'changePassword']);
        Route::post('check-password', [AuthController::class, 'checkPassword']);
        Route::delete('delete', [AuthController::class, 'delete']);
    });
});
/* ====================================================== END AUTH ==================================================== */

//Protecting Routes
Route::group(['middleware' => ['auth:api']], function () {

    /* ====================================================== USER TYPE ==================================================== */
    Route::prefix('user-type')->group(function () {
        Route::get('list', [UserTypeController::class, 'list']);
        Route::get('list/{id}', [UserTypeController::class, 'list']);
        Route::post('store', [UserTypeController::class, 'store']);
        Route::put('update', [UserTypeController::class, 'update']);
        Route::delete('delete', [UserTypeController::class, 'delete']);
    });
    /* ====================================================== END USER TYPE ==================================================== */

    /* ====================================================== CHAT ==================================================== */
    Route::prefix('chat')->group(function () {
        Route::get('list', [ChatsController::class, 'list']);
        Route::get('list/{id}', [ChatsController::class, 'list']);
        Route::post('store', [ChatsController::class, 'store']);
        Route::delete('delete-by-uid', [ChatsController::class, 'deleteByUserID']);
        Route::delete('delete-by-uid-to', [ChatsController::class, 'deleteByUserTo']);
    });
    /* ====================================================== END CHAT ==================================================== */

    /* ====================================================== GABUNG ==================================================== */
    Route::prefix('join')->group(function () {
        Route::get('list', [GabungsController::class, 'list']);
        Route::get('list/{id}', [GabungsController::class, 'list']);
        Route::post('store', [GabungsController::class, 'store']);
        Route::delete('delete', [GabungsController::class, 'delete']);
    });
    /* ====================================================== END GABUNG ==================================================== */

    /* ====================================================== KELUAR ==================================================== */
    Route::prefix('exit')->group(function () {
        Route::get('list', [KeluarController::class, 'list']);
        Route::get('list/{id}', [KeluarController::class, 'list']);
        Route::post('store', [KeluarController::class, 'store']);
        Route::delete('delete', [KeluarController::class, 'delete']);
    });
    /* ====================================================== END KELUAR ==================================================== */

    /* ====================================================== REPORT ==================================================== */
    Route::prefix('reports')->group(function () {
        Route::get('list', [ReportController::class, 'list']);
        Route::get('list/{id}', [ReportController::class, 'list']);
        Route::post('store', [ReportController::class, 'store']);
        Route::put('update-status', [ReportController::class, 'updateStatus']);
        Route::delete('delete', [ReportController::class, 'delete']);
    });
    /* ====================================================== END REPORT ==================================================== */
});
