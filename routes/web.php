<?php

use App\Http\Controllers\Callback\XenditCallbackController;
use App\Http\Controllers\EventCategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventsTicketController;
use App\Http\Controllers\FormMemberController;
use App\Http\Controllers\NewsCategoryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VideosController;
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

Route::get('/payment-success', function () {
    return view('success');
});

Route::get('ajax', function () {
    return view('ajax');
});

Route::get('/scan', function () {
    return view('scan');
});

Route::get('/', [FormMemberController::class, 'index']);
Route::post('/membership', [FormMemberController::class, 'store']);
Route::get('/test', [TestController::class, 'test']);
Route::post('/test/upload', [TestController::class, 'upload']);
Route::get('/privacy', function () {
    return view('privacy-policy');
});

Route::get('/term', function () {
    return view('term-condition');
});

Route::get('/register-event', [EventController::class, 'view2']);
// Route::get('/register-event/free', [EventController::class, 'free']);
Route::get('/register-event/multiple', [EventController::class, 'view']);
Route::get('/register-event/sponsor', [EventController::class, 'sponsor']);
Route::post('/regis-sponsor', [EventController::class, 'register_sponsor']);
Route::post('/regis-multiple', [EventController::class, 'register_multiple']);
Route::post('/payment-personal', [EventController::class, 'payment_personal']);
Route::post('/register/email', [FormMemberController::class, 'check_email']);
Route::post('renewal-payment', [PaymentController::class, 'renewal']);
Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);

Route::get('/admin/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('payment', [PaymentController::class, 'index'])->name('payment');

Route::get('/events-sementara', [EventController::class, 'sementara'])->name('events-sementara');

Route::post('/request-event', [EventController::class, 'request']);

Route::get('/events', [EventController::class, 'index'])->name('events');
Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
Route::get('/events/edit/{id}', [EventController::class, 'edit'])->name('events.edit');
Route::post('/events/update', [EventController::class, 'update'])->name('events.update');
Route::post('events/store', [EventController::class, 'store'])->name('events.store');
Route::get('/events/{slug}/detail', [EventController::class, 'detail'])->name('events-details');
Route::post('/events/addCheck', [EventController::class, 'dataCheck'])->name('events.add.check');
Route::post('/events/addInvitation', [EventController::class, 'regisInvitation'])->name('events.add.invitation');
//Events Category
Route::get('/events/category', [EventCategoryController::class, 'index'])->name('events.category');
Route::post('/events/addcategory', [EventCategoryController::class, 'store']);
Route::post('/events/editcategory', [EventCategoryController::class, 'edit']);
Route::post('/events/deletecategory', [EventCategoryController::class, 'destroy']);

Route::get('/events/tickets', [EventsTicketController::class, 'index'])->name('events.tickets');
Route::post('/events-tickets/addcategory', [EventsTicketController::class, 'store']);
Route::post('/events-tickets/editcategory', [EventsTicketController::class, 'edit']);
Route::post('/events-tickets/deletecategory', [EventsTicketController::class, 'destroy']);

Route::post('/events-import', [EventController::class, 'import'])->name('events.import');

Route::get('/news', [NewsController::class, 'index'])->name('news');
Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');
Route::post('/news/store', [NewsController::class, 'store'])->name('news.store');

//news Category
Route::get('news/category', [NewsCategoryController::class, 'index'])->name('news.category');
Route::post('news/addcategory', [NewsCategoryController::class, 'store']);
Route::post('news/editcategory', [NewsCategoryController::class, 'edit']);
Route::post('news/deletecategory', [NewsCategoryController::class, 'destroy']);

//Videos Highlight
Route::get('videos', [VideosController::class, 'index'])->name('videos');
Route::post('videos/addcategory', [VideosController::class, 'store']);
Route::post('videos/editcategory', [VideosController::class, 'edit']);
Route::post('videos/deletecategory', [VideosController::class, 'destroy']);

Route::get('/admin/users', [UsersController::class, 'index'])->name('users');
Route::post('/users-import', [UsersController::class, 'import'])->name('users.import');

Route::get('/admin/member', [UsersController::class, 'member'])->name('members');
