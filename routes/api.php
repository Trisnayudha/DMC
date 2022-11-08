<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\Callback\XenditCallbackController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/xendit/invoice', [XenditCallbackController::class, 'invoice']);

Route::post('/signin-phone', [AuthController::class, 'signin_phone']);
Route::post('/verify_signin_phone', [AuthController::class, 'verify_signin_phone']);
Route::post('/signin-email', [AuthController::class, 'signin_email']);

Route::post('/check-signup', [AuthController::class, 'check']);
Route::post('/signup', [AuthController::class, 'signup']);

Route::any('request_otp', [AuthController::class, 'requestOtp']);
Route::post('verify_otp', [AuthController::class, 'verifyOtp']);

Route::post('/forgot-password', [AuthController::class, 'forgot']);
Route::post('/verify_forgot', [AuthController::class, 'verify_forgot']);
Route::post('/reset-password', [AuthController::class, 'resetpassword']);

Route::group(['middleware' => 'auth:sanctum'], function () { // Semua Request Route Menggunakan Token API
    Route::post('profile', [UserController::class, 'index']);
    Route::post('profile/update_profile', [UserController::class, 'update_profile']);
    Route::post('profile/update_company', [UserController::class, 'update_company']);
    Route::post('profile/changePassword', [UserController::class, 'changePassword']);
    Route::post('profile/subscribe', [UserController::class, 'subscribe']);
    Route::post('profile/unsubscribe', [UserController::class, 'unsubscribe']);
});


Route::get('/list-payment', [PaymentController::class, 'listbank']);
