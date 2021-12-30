<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\StreamStatsController;
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

    // Game stats
    Route::get('/games/by-stream-count', [GamesController::class, 'getByStreamCount']);
    Route::get('/games/by-viewer-count', [GamesController::class, 'getByViewerCount']);

    // Stream stats
    Route::get('streams/top-streams', [StreamStatsController::class, 'getTopStreams']);
    Route::get('streams/followed-by-user', [StreamStatsController::class, 'getStreamsFollowedByUser']);
    Route::get('streams/median-view-count', [StreamStatsController::class, 'getMedianViewCount']);
    Route::get('streams/count-by-hour', [StreamStatsController::class, 'getStreamCountByHour']);
    Route::get('streams/min-viewer-count-needed', [StreamStatsController::class, 'getViewerCountNeededForLowest']);
    Route::get('streams/shared-tags', [StreamStatsController::class, 'getSharedTags']);
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/authenticate', [AuthController::class, 'authenticate']);
