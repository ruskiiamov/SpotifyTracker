<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
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

Route::get('/', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('index');

Route::get('/login', [AuthController::class, 'login'])
    ->middleware('guest')
    ->name('login');

Route::get('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::post('/login', [AuthController::class, 'loginSpotify'])
    ->middleware('guest')
    ->name('loginSpotify');

Route::get('/callback', [AuthController::class, 'callback'])
    ->middleware('guest')
    ->name('callback');

Route::get('/followed', [HomeController::class, 'followed'])
    ->middleware('auth')
    ->name('followed');

Route::get('/subscribed', [HomeController::class, 'subscribed'])
    ->middleware('auth')
    ->name('subscribed');

Route::get('/genres', [HomeController::class, 'genres'])
    //->middleware('auth')
    ->name('genres');

Route::post('/subscription', [HomeController::class, 'saveSubscriptions'])
    ->middleware('auth')
    ->name('subscription');
