<?php

use App\Http\Controllers\Callback\XenditCallbackController;
use App\Http\Controllers\EventCategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventsConferenceController;
use App\Http\Controllers\EventsDetailController;
use App\Http\Controllers\EventsDetailParticipantController;
use App\Http\Controllers\EventsHighlightController;
use App\Http\Controllers\EventsPaymentController;
use App\Http\Controllers\EventsRegisterController;
use App\Http\Controllers\EventsTicketController;
use App\Http\Controllers\FormMemberController;
use App\Http\Controllers\MarketingAdsController;
use App\Http\Controllers\NewsCategoryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\SpecialEventController;
use App\Http\Controllers\SponsorController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VideosController;
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

Route::get('/save-invoice', [TestController::class, 'saveInvoice']);
Route::get('/register', function () {
    return view('register_event.register');
});
Route::get('/scan', [PrintController::class, 'scan']);
Route::get('/scan/print', [PrintController::class, 'index']);
Route::post('/scan/request', [PrintController::class, 'request']);
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

// Route::get('/register-event', [EventController::class, 'view2']);
// Route::get('/register-event/multiple', [EventController::class, 'view']);
// Route::get('/special-event/free', [SpecialEventController::class, 'free']);
// Route::post('regis-special-event', [SpecialEventController::class, 'store']);


Route::get('/{slug}/exclusive-invitation', [EventsRegisterController::class, 'single']);
Route::post('/payment-personal', [EventsPaymentController::class, 'payment_personal']);

Route::get('/{slug}/register-event', [EventsRegisterController::class, 'multiple']);
Route::post('/payment-multiple', [EventsPaymentController::class, 'payment_multiple']);

Route::get('/{slug}/register-event/sponsor', [SponsorController::class, 'sponsor']);
Route::post('/regis-sponsor', [SponsorController::class, 'register_sponsor']);
Route::get('/sponsor/{id}', [SponsorController::class, 'show_sponsor']);

// Route::post('/regis-multiple', [EventController::class, 'register_multiple']);
// Route::post('/register/email', [FormMemberController::class, 'check_email']);



Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);

Route::get('/admin/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('payment', [PaymentController::class, 'index'])->name('payment');

Route::get('/events-sementara', [EventController::class, 'sementara'])->name('events-sementara');

// Events List
Route::get('/events', [EventController::class, 'index'])->name('events');
Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
Route::get('/events/edit/{id}', [EventController::class, 'edit'])->name('events.edit');
Route::post('/events/update', [EventController::class, 'update'])->name('events.update');
Route::post('events/store', [EventController::class, 'store'])->name('events.store');

// Events Detail
Route::get('/events/{slug}/detail', [EventsDetailController::class, 'detail'])->name('events-details');
Route::post('/events/addUser', [EventsDetailController::class, 'add_user'])->name('events.add.user');
Route::post('/events/addInvitation', [EventsDetailController::class, 'add_invitation'])->name('events.add.invitation');
Route::post('/events/action', [EventsDetailController::class, 'action']);
Route::post('/events/invoice', [EventsDetailController::class, 'invoice']);
Route::post('/events/ticket', [EventsDetailController::class, 'ticket']);
Route::get('/edit/user/{id}', [UsersController::class, 'editUserEvent']); //Edit user yg di dalam event
Route::post('remove-participant', [EventsDetailController::class, 'removeParticipant']);
Route::post('renewal-payment', [PaymentController::class, 'renewal']);
Route::post('/events/update/user', [EventsDetailController::class, 'editPeserta']);


// Events Detail participant
Route::get('/events/{slug}/detail-participant', [EventsDetailParticipantController::class, 'detail_participant'])->name('events-details-participant');
Route::post('/events/confirmation', [EventsDetailParticipantController::class, 'sendParticipant'])->name('events-send-participant');
//Events Category
Route::get('/events/category', [EventCategoryController::class, 'index'])->name('events.category');
Route::post('/events/addcategory', [EventCategoryController::class, 'store']);
Route::post('/events/editcategory', [EventCategoryController::class, 'edit']);
Route::post('/events/deletecategory', [EventCategoryController::class, 'destroy']);

//Events tickets
Route::get('/events/tickets', [EventsTicketController::class, 'index'])->name('events.tickets');
Route::post('/events-tickets/addcategory', [EventsTicketController::class, 'store']);
Route::post('/events-tickets/editcategory', [EventsTicketController::class, 'edit']);
Route::post('/events-tickets/deletecategory', [EventsTicketController::class, 'destroy']);

//Events Conference
Route::get('/events/conference', [EventsConferenceController::class, 'index'])->name('events.conference');
Route::get('/events/conference/create', [EventsConferenceController::class, 'create'])->name('events.conference.create');
Route::post('/events-conference/addcategory', [EventsConferenceController::class, 'store']);
Route::post('/events-conference/editcategory', [EventsConferenceController::class, 'edit']);
Route::post('/events-conference/deletecategory', [EventsConferenceController::class, 'destroy']);


//Events Highlight
Route::get('/events/highlight', [EventsHighlightController::class, 'index'])->name('events.highlight');
Route::post('/events-highlight/addcategory', [EventsHighlightController::class, 'store']);
Route::post('/events-highlight/editcategory', [EventsHighlightController::class, 'edit']);
Route::post('/events-highlight/deletecategory', [EventsHighlightController::class, 'destroy']);

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

//Marketing Adds
Route::get('marketing-ads', [MarketingAdsController::class, 'index'])->name('marketing.ads');
Route::post('marketing-ads/add', [MarketingAdsController::class, 'store']);
Route::post('marketing-ads/edit', [MarketingAdsController::class, 'edit']);
Route::post('marketing-ads/delete', [MarketingAdsController::class, 'destroy']);
Route::get('marketing-ads/event', [MarketingAdsController::class, 'event']);
Route::get('marketing-ads/news', [MarketingAdsController::class, 'news']);

//Notification
Route::prefix('admin')->group(function () {
    Route::get('notification', [NotificationController::class, 'index'])->name('notification');
    Route::post('notification/add', [NotificationController::class, 'store']);
    Route::post('notification/edit', [NotificationController::class, 'edit']);
    Route::post('notification/delete', [NotificationController::class, 'destroy']);
    Route::get('notification/users', [NotificationController::class, 'users']);

    Route::get('/special-event', [SpecialEventController::class, 'index'])->name('special-event');
    Route::post('/special-event', [SpecialEventController::class, 'request']);
});


Route::get('/admin/users', [UsersController::class, 'index'])->name('users');
Route::post('/admin/users', [UsersController::class, 'store'])->name('users.store');
Route::post('/users-import', [UsersController::class, 'import'])->name('users.import');

Route::get('/admin/member', [UsersController::class, 'member'])->name('members');
