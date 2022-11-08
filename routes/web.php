<?php

use App\Http\Controllers\Callback\XenditCallbackController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FormMemberController;
use App\Http\Controllers\PaymentController;
use App\Models\Payments\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|1
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [FormMemberController::class, 'index']);
Route::post('/membership', [FormMemberController::class, 'store']);
Route::get('/test', [FormMemberController::class, 'test']);
Route::get('/privacy', function () {
    return view('privacy-policy');
});

Route::get('/register-event', [EventController::class, 'view']);
Route::get('/register-event/free', [EventController::class, 'view2']);
Route::get('/register-event/sponsor', [EventController::class, 'sponsor']);
Route::post('/regis-sponsor', [EventController::class, 'register_sponsor']);
Route::post('/payment-personal', [EventController::class, 'payment_personal']);

Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);

Route::get('/admin/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('payment', [PaymentController::class, 'index'])->name('payment');

Route::get('/events-sementara', [EventController::class, 'sementara'])->name('events-sementara');
