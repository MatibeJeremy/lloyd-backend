<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\Auth\AuthController;
use App\Http\Controllers\V1\Blogs\ApiController;

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

Route::post(
    '/login',
    [AuthController::class, 'login']
)->name('login');

Route::post(
    '/register',
    [AuthController::class, 'register']
)->name('register');

Route::get(
    '/blogs',
    [ApiController::class, 'index']
)->name('blogs');

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::get(
        '/logout',
        [AuthController::class, 'logout']
    )->name('logout');

    Route::get(
        '/get_user',
        [AuthController::class, 'me']
    )->name('me');

    Route::post(
        '/create',
        [ApiController::class, 'store']
    )->name('create');

    Route::put(
        '/update/{id}',
        [ApiController::class, 'update']
    )->name('update');

    Route::delete(
        '/delete/{id}',
        [ApiController::class, 'destroy']
    )->name('destroy');
});
