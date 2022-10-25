<?php

use App\Http\Controllers\API\AuthController;
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

Route::post('/signin-phone', [AuthController::class, 'signin_phone']);
Route::post('/verify_signin_phone', [AuthController::class, 'verify_signin_phone']);
Route::post('/signin-email', [AuthController::class, 'signin_email']);

Route::post('/check-signup', [AuthController::class, 'check']);
Route::post('/signup', [AuthController::class, 'signup']);

Route::any('request_otp', [AuthController::class, 'requestOtp']);
Route::post('verify_otp', [AuthController::class, 'verifyOtp']);
