<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\WpdaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/google-login', [AuthenticationController::class, 'googleLogin']);
Route::post('/register', [AuthenticationController::class, 'register']);


Route::middleware(['auth:sanctum'])->group(function () {
    // WPDA API
    Route::get('/wpda', [WpdaController::class, 'index']);
    Route::post('/wpda/create', [WpdaController::class, 'createWpda']);
    Route::get('/wpda/history/{id}', [WpdaController::class, 'getByUserId']);
    Route::put('/wpda/update/{id}', [WpdaController::class, 'updateWpda']);
    Route::delete('/wpda/delete/{id}', [WpdaController::class, 'delete']);

    // LIKE WPDA
    Route::post('/like/{userId}/{wpdaId}', [LikeController::class, 'likeWpda']);
    Route::delete('/unlike/{userId}/{wpdaId}', [LikeController::class, 'unlikeWpda']);


    // FILTER DATA WPDA
    Route::get('/wpda/history/filter/{id}/', [WpdaController::class, 'getWpdaByMonth']);
    
     // COMMENT WPDA
    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/delete/{id}', [CommentController::class, 'deleteComment']);


    // USER API
    Route::get('/users/all', [AuthenticationController::class, 'getUsers']);
    Route::put('/approve/{id}', [AuthenticationController::class, 'approve']);
    Route::delete('/user/logout', [AuthenticationController::class, 'logout']);
    Route::delete('/user/delete/{id}', [AuthenticationController::class, 'deleteUser']);
    
    Route::put('/users/{userId}/update-full-name', [UserController::class, 'updateFullName']);
    

    Route::get('/users/monthly-data', [UserController::class, 'getMonthlyDataForAllUsers']);

    // USER PROFILE API
    Route::post('/profile/{id}', [UserProfileController::class, 'createProfile']);
    Route::get('/profile', [UserProfileController::class, 'index']);
    Route::put('/profile/update/{id}', [UserProfileController::class, 'updateProfile']);
    Route::delete('/profile/delete/{id}', [UserProfileController::class, 'deleteProfile']);
    Route::get('/user-gender-total', [UserProfileController::class, 'getUserGenderTotal']);


    // PROFILE PICTURE API

    Route::post('/users/{userId}/upload-profile-picture', [UserProfileController::class, 'uploadProfilePicture']);
    Route::get('/users/{userId}/profile-picture', [UserProfileController::class, 'getProfilePicture']);
    Route::delete('/users/{userId}/delete-profile-picture', [UserProfileController::class, 'deleteProfilePicture']);
    
    
    

    Route::get('/users/{userId}', [UserController::class, 'show']);
    Route::get('/total-users', [UserController::class, 'getTotalUsers']);
    Route::get('/total-new-users', [UserController::class, 'getNewUsersLastMonth']);

    //Verified Email
    Route::post('/verify-user', [UserController::class, 'verifyUser']);
});
