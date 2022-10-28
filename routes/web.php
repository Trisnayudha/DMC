<?php

use App\Http\Controllers\Callback\XenditCallbackController;
use App\Http\Controllers\FormMemberController;
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

Route::get('/xendit/invoice', [XenditCallbackController::class, 'invoice']);
