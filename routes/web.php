<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify-mail/{token}', [UserController::class, 'verificationMail']);
Route::get('/reset-password', [UserController::class, 'resetPasswordLoad'])->name('reset-password');
Route::post('/reset-password', [UserController::class, 'resetPassword']);
