<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\EventsCategoryController;
use App\Http\Controllers\API\FaqController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\NewsCategoryController;
use App\Http\Controllers\API\NewsController;
use App\Http\Controllers\API\NotifController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\VideosContorller;
use App\Http\Controllers\Callback\XenditCallbackController;
use App\Http\Controllers\API\ContactUsController;
use App\Http\Controllers\API\MarketingAdsController;
use App\Http\Controllers\API\MikrotikController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ScanController;
use App\Http\Controllers\API\SponsorsController;
use App\Http\Controllers\API_WEB\AboutController;
use App\Http\Controllers\API_WEB\AuthController as API_WEBAuthController;
use App\Http\Controllers\API_WEB\HomeController as API_WEBHomeController;
use App\Http\Controllers\API_WEB\EventsController as API_WEBEventsController;
use App\Http\Controllers\API_WEB\GalleryController;
use App\Http\Controllers\API_WEB\PaymentController as API_WEBPaymentController;
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
Route::post('/xendit/invoice_v2', [XenditCallbackController::class, 'invoice_v2']);
Route::post('/xendit/fva_create', [XenditCallbackController::class, 'fva_create']);
Route::post('/xendit/fva_paid', [XenditCallbackController::class, 'fva_paid']);
Route::post('payment/creditcard', [PaymentController::class, 'creditCard']);

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

    Route::post('/profile/request_otp', [UserController::class, 'requestOtp']);
    Route::post('/profile/verify_otp', [UserController::class, 'verifyOtp']);
    Route::post('/profile/check', [UserController::class, 'check']);

    Route::post('/payment', [PaymentController::class, 'payment']);

    Route::post('/events/myEvent', [EventController::class, 'myEvent']);
    Route::post('/events/waitingPay', [EventController::class, 'waitingPay']);
    Route::post('/detail/payment', [EventController::class, 'detailPayment']);

    Route::post('/contact_us', [ContactUsController::class, 'index']);

    Route::post('/detail/news/{slug}', [NewsController::class, 'detail']);
    Route::post('/news/bookmark', [NewsController::class, 'bookmark']);
    Route::post('/news/like', [NewsController::class, 'like']);
    Route::post('/news/comment', [NewsController::class, 'comment']);

    Route::post('/bookmark_list', [NewsController::class, 'bookmarkList']);


    Route::post('/scan/users_event', [ScanController::class, 'usersEvent']);
    Route::post('/scan/users', [ScanController::class, 'users']);

    Route::post('/scan/request_connection', [ScanController::class, 'postRequest']);
    Route::post('/list_connection', [ScanController::class, 'listConnected']);


    Route::post('/delete/account', [UserController::class, 'deleteAccount']);
});
Route::post('/news/category', [NewsCategoryController::class, 'index']);
Route::post('/news', [NewsController::class, 'ListAll']);

Route::post('/events/category', [EventsCategoryController::class, 'index']);
Route::post('/events', [EventController::class, 'index']);
Route::post('/events/{slug}/detail', [EventController::class, 'detail']);

//Events Done
Route::post('/events/{slug}/detail/done', [EventController::class, 'detailDone']);
Route::post('/events/{slug}/highlight', [EventController::class, 'highlight']);
Route::post('/events/{slug}/conference', [EventController::class, 'conference']);

Route::post('/list-payment', [PaymentController::class, 'listbank']);

Route::post('/carosel', [HomeController::class, 'index']);

Route::post('/videos_highlight', [VideosContorller::class, 'index']);

Route::post('/faq', [FaqController::class, 'index']);

Route::post('/notif', [NotifController::class, 'index']);

Route::post('/marketing-ads', [MarketingAdsController::class, 'index']);

Route::post('/notification/list', [NotificationController::class, 'notification']);

Route::post('/highlight/list', [NotificationController::class, 'highlight']);


Route::prefix('web')->group(function () {

    Route::post('/signin-phone', [API_WEBAuthController::class, 'signin_phone']);
    Route::any('request_otp', [API_WEBAuthController::class, 'requestOtp']);

    Route::post('sponsors', [SponsorsController::class, 'sponsor']);
    Route::post('sponsors/{slug}/detail', [SponsorsController::class, 'detail']);

    Route::post('advertisement', [MarketingAdsController::class, 'advertisementSide']);


    Route::post('home/carousel', [API_WEBHomeController::class, 'getCarousel']);
    Route::post('home/comingSoon', [API_WEBHomeController::class, 'getComingSoon']);
    Route::post('home/upComing', [API_WEBHomeController::class, 'postUpComing']);
    Route::post('home/pastGalleryEvent', [API_WEBHomeController::class, 'getPastGalleryEvent']);
    Route::post('home/scheduleEvent', [API_WEBHomeController::class, 'getScheduleEvent']);

    Route::post('events/{slug}/detail', [API_WEBEventsController::class, 'detail']);
    Route::post('events/{slug}/rundown', [API_WEBEventsController::class, 'rundown']);

    Route::post('/latestNews', [NewsController::class, 'index']);
    Route::post('/news', [NewsController::class, 'ListAll']);
    Route::post('/detail/news/{slug}', [NewsController::class, 'detail']);

    Route::post('gallery/home', [GalleryController::class, 'home']);
    Route::post('gallery/event', [GalleryController::class, 'eventList']);
    Route::post('gallery/feature', [GalleryController::class, 'feature']);
    Route::post('gallery/navigate', [GalleryController::class, 'navigate']);

    Route::post('about/image', [AboutController::class, 'sectionImage']);

    Route::post('payment/history', [API_WEBPaymentController::class, 'historyPayment']);
});

Route::post('/mikrotik', [MikrotikController::class, 'process']);
