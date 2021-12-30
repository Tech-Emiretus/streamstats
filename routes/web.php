<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserStreamsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/refresh-streams', [UserStreamsController::class, 'refreshStreams']);
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/authenticate', [AuthController::class, 'authenticate']);
