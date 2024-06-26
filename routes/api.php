<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('reset/password', 'requestPasswordReset');
    Route::post('verify/password/code/{user}', 'verifyPasswordResetCode');
    Route::post('reset/password/{user}', 'resetPassword');
    Route::post('verify/code', 'verifyCode');
    Route::post('create/nickname', 'createNickName')->middleware('auth:sanctum');
    Route::post('pin/code/create', 'createPinCode')->middleware('auth:sanctum');
    Route::post('pin/code/verify', 'verifyPinCode')->middleware('auth:sanctum');
    Route::post('resend/verification/code', 'resendVerificationCode');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
});
