<?php

use App\Http\Controllers\Callback\XenditCallbackController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FormMemberController;
use App\Models\Payments\Payment;
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
Route::post('/payment-personal', [EventController::class, 'payment_personal']);

Route::get('/asu', function () {
    $findUser = Payment::where('code_payment', 'LADWM3I')
        ->join('xtwp_users_dmc as a', 'a.id', 'payment.member_id')
        ->first();
    dd($findUser->code_payment);
});
