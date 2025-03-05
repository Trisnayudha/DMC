<?php

use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\DigitalEditionController;
use App\Http\Controllers\Admin\EventCategoryController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventsConferenceController;
use App\Http\Controllers\Admin\EventsDetailController;
use App\Http\Controllers\Admin\EventsDetailParticipantController;
use App\Http\Controllers\Admin\EventsHighlightController;
use App\Http\Controllers\Admin\EventsRundownController;
use App\Http\Controllers\Admin\EventsScheduleController;
use App\Http\Controllers\Admin\EventsSpeakersController;
use App\Http\Controllers\Admin\EventsTicketController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MarketingAdsController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ScholarshipController as AdminScholarshipController;
use App\Http\Controllers\Admin\SocialMediaEngagementController;
use App\Http\Controllers\Admin\SpecialEventController;
use App\Http\Controllers\Admin\SponsorAddressController;
use App\Http\Controllers\Admin\SponsorAdvertisingController;
use App\Http\Controllers\Admin\SponsorBenefitController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\SponsorCountRepresentativeController;
use App\Http\Controllers\Admin\SponsorRepresentativeController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\VideosController;
use App\Http\Controllers\Admin\WhatsappBlastingController;
use App\Http\Controllers\Admin\WhatsappCampaignController;
use App\Http\Controllers\Admin\WhatsappDBController;
use App\Http\Controllers\Admin\WhatsappSenderController;
use App\Http\Controllers\Admin\WhatsappTemplateController;
use App\Http\Controllers\Frontend\EventsPaymentController;
use App\Http\Controllers\Frontend\EventsRegisterController;
use App\Http\Controllers\Frontend\EventsRegisterSponsorController;
use App\Http\Controllers\Frontend\FormMemberController;
use App\Http\Controllers\Frontend\PrintController;
use App\Http\Controllers\Frontend\ScholarshipController;
use App\Http\Controllers\Admin\SponsorPhotosVideosActivityController;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
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

Route::get('/test/email', [TestController::class, 'testEmail']);

Route::get('web-view-sponsor', function () {
    return view('maps');
});

Route::get('/business-card', function () {
    return view('business-card');
});
Route::post('/business-card/store', [TestController::class, 'storeBusinessCard']);
Route::get('/apps', function () {
    return view('apps');
});
Route::get('/payment-success', function () {
    return view('success');
});

Route::get('ajax', function () {
    return view('ajax');
});

Route::get('collect', function () {
    return view('collect');
});
// Example route call: /collect-exhibitors?ids=560,561,562
Route::get('/collect-exhibitors', function (Request $request, TestController $controller) {
    $ids = explode(',', $request->query('ids'));
    return $controller->collectAndStoreExhibitorData($ids);
});

Route::get('/fetch-contacts', [TestController::class, 'fetchAndStoreContactData']);

Route::get('/save-invoice', [TestController::class, 'saveInvoice']);
Route::get('/register', function () {
    return view('register_event.register');
});
Route::get('/register/step', function () {
    return view('register_event.step');
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

Route::get('/share/news/{slug}', function ($slug) {
    $news = DB::table('news')->where('slug', $slug)->first();
    $data = [
        'news' => $news
    ];
    return view('admin.news.news-share', $data);
});
Route::get('/share/events/{slug}', function ($slug) {
    $event = DB::table('events')->where('slug', $slug)->first();
    $data = [
        'event' => $event
    ];
    return view('admin.events.event-share', $data);
});

// Route::get('/visit', [FormMemberController::class, 'visit']);
// Route::post('/visit', [FormMemberController::class, 'visitStore']);
// Route::get('/register-event', [EventController::class, 'view2']);
// Route::get('/register-event/multiple', [EventController::class, 'view']);
// Route::get('/special-event/free', [SpecialEventController::class, 'free']);
// Route::post('regis-special-event', [SpecialEventController::class, 'store']);


Route::get('/{slug}/exclusive-invitation', [EventsRegisterController::class, 'single']);
Route::get('/{slug}/invitation/{type}', [EventsRegisterController::class, 'sponsor']);
Route::post('/payment-personal', [EventsPaymentController::class, 'payment_personal']);

Route::get('/{slug}/register-event', [EventsRegisterController::class, 'multiple']);
Route::post('/payment-multiple', [EventsPaymentController::class, 'payment_multiple']);

Route::get('/{slug}/register-event/sponsor', [SponsorController::class, 'sponsor']);
Route::post('/regis-sponsor', [SponsorController::class, 'register_sponsor']);
Route::get('/sponsor/{id}', [SponsorController::class, 'show_sponsor']);

// Route::post('/regis-multiple', [EventController::class, 'register_multiple']);
// Route::post('/register/email', [FormMemberController::class, 'check_email']);

Route::get('imc-scholarship-form', [ScholarshipController::class, 'index']);
Route::post('imc-scholarship-form', [ScholarshipController::class, 'store'])->name('scholarship.store');

Route::post('checkMember/{email}', [UsersController::class, 'checkMember'])->withoutMiddleware('auth');

Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);


Route::prefix('admin')->group(function () {
    //Notification
    Route::get('notification', [NotificationController::class, 'index'])->name('notification');
    Route::post('notification/add', [NotificationController::class, 'store']);
    Route::post('notification/edit', [NotificationController::class, 'edit']);
    Route::post('notification/delete', [NotificationController::class, 'destroy']);
    Route::get('notification/users', [NotificationController::class, 'users']);

    Route::get('/special-event', [SpecialEventController::class, 'index'])->name('special-event');
    Route::post('/special-event', [SpecialEventController::class, 'request']);

    Route::resource('sponsors', SponsorController::class);
    Route::get('sponsors-representative-count', [SponsorCountRepresentativeController::class, 'index'])->name('sponsors.representative.index');
    Route::resource('advertisement', AdvertisementController::class);
    Route::post('sponsors/update-status/{id}', [SponsorController::class, 'updateStatus']);

    Route::get('sponsor-engagement', [SocialMediaEngagementController::class, 'index'])
        ->name('sponsor-engagement.index');
    Route::get('sponsor-engagement/create', [SocialMediaEngagementController::class, 'create'])
        ->name('sponsor-engagement.create');
    Route::post('sponsor-engagement', [SocialMediaEngagementController::class, 'store'])
        ->name('sponsor-engagement.store');

    Route::get('sponsor/benefits', [SponsorBenefitController::class, 'index'])
        ->name('sponsors.benefit.index');

    Route::get('sponsors/{sponsor}/benefits', [SponsorBenefitController::class, 'detail'])
        ->name('sponsors.benefit.detail');

    Route::post('admin/sponsors/benefits/{benefitUsage}/mark-used', [SponsorBenefitController::class, 'markUsed'])
        ->name('sponsors.benefit.markUsed');

    Route::resource('sponsors-address', SponsorAddressController::class);
    Route::get('sponsors-representative/{$id}', [SponsorRepresentativeController::class, 'show'])->name('sponsors-representative.show');
    // 1) Tampilkan daftar sponsor representative berdasarkan sponsor_id
    Route::get('sponsors-representative/{sponsor_id}', [SponsorRepresentativeController::class, 'show'])
        ->name('sponsorRepresentative.showBySponsor');

    // 2) Simpan data baru (Create)
    Route::post('sponsors-representative', [SponsorRepresentativeController::class, 'store'])
        ->name('sponsorRepresentative.store');

    // 3) Tampilkan form edit data tertentu (berdasarkan id)
    Route::get('sponsors-representative/{id}/edit', [SponsorRepresentativeController::class, 'edit'])
        ->name('sponsorRepresentative.edit');

    // 4) Update data tertentu (berdasarkan id)
    Route::put('sponsors-representative/{id}', [SponsorRepresentativeController::class, 'update'])
        ->name('sponsorRepresentative.update');

    // 5) Hapus data tertentu (berdasarkan id)
    Route::delete('sponsors-representative/{id}', [SponsorRepresentativeController::class, 'destroy'])
        ->name('sponsorRepresentative.destroy');
    Route::resource('sponsors-advertising', SponsorAdvertisingController::class);
    Route::resource('photos-videos-activity', SponsorPhotosVideosActivityController::class);

    Route::resource('digital-edition', DigitalEditionController::class);

    Route::get('home', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('home');

    Route::get('payment', [PaymentController::class, 'index'])->name('payment');

    Route::get('/events-sementara', [EventController::class, 'sementara'])->name('events-sementara');

    // Events List
    Route::get('/events', [EventController::class, 'index'])->name('events');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::get('/events/edit/{id}', [EventController::class, 'edit'])->name('events.edit');
    Route::post('/events/update', [EventController::class, 'update'])->name('events.update');
    Route::post('events/store', [EventController::class, 'store'])->name('events.store');
    Route::post('/events/delete', [EventController::class, 'destroy']);
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
    Route::post('/events/assign-sponsor', [EventsDetailController::class, 'assignSponsorRepresentative']);

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

    Route::get('/events/sponsor', [EventsRegisterSponsorController::class, 'index'])->name('events.sponsor');
    Route::post('/events-sponsor/addeventsponsor', [EventsRegisterSponsorController::class, 'store']);
    Route::post('/events-sponsor/editeventsponsor', [EventsRegisterSponsorController::class, 'edit']);
    Route::post('/events-sponsor/deleteeventsponsor', [EventsRegisterSponsorController::class, 'destroy']);

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

    //Events Schedule
    Route::get('/events/schedule', [EventsScheduleController::class, 'index'])->name('events.schedule');
    Route::post('/events-schedule/addcategory', [EventsScheduleController::class, 'store']);
    Route::post('/events-schedule/editcategory', [EventsScheduleController::class, 'edit']);
    Route::post('/events-schedule/deletecategory', [EventsScheduleController::class, 'destroy']);
    Route::post('/events/schedule/movesort', [EventsScheduleController::class, 'moveSort'])->name('events.moveSort');

    //Events Speakers
    Route::resource('events/speakers', EventsSpeakersController::class);

    //Events Rundown
    Route::resource('events/rundown', EventsRundownController::class);

    Route::post('/events-import', [EventController::class, 'import'])->name('events.import');

    Route::get('/news', [NewsController::class, 'index'])->name('news');
    Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');
    Route::post('/news/store', [NewsController::class, 'store'])->name('news.store');
    Route::get('news/{id}/edit', [NewsController::class, 'edit'])->name('news.edit');
    Route::patch('/news/update/{id}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('/news/destroy/{id}', [NewsController::class, 'destroy'])->name('news.destroy');

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


    Route::get('users', [UsersController::class, 'index'])->name('users');
    Route::post('users', [UsersController::class, 'store'])->name('users.store');
    Route::post('/users-import', [UsersController::class, 'import'])->name('users.import');

    Route::get('member', [UsersController::class, 'member'])->name('members');

    //Invoice
    Route::get('invoice', [InvoiceController::class, 'index']);
    Route::get('invoice-detail', [InvoiceController::class, 'detail']);

    //Scholarship
    Route::get('/scholarship', [AdminScholarshipController::class, 'index']);

    //Whatsapp Group
    Route::prefix('whatsapp')->group(function () {
        Route::resource('blasting', WhatsappBlastingController::class);
        Route::resource('campaign', WhatsappCampaignController::class);
        Route::resource('db', WhatsappDBController::class);
        Route::resource('sender', WhatsappSenderController::class);
        Route::resource('template', WhatsappTemplateController::class);
    });
});
Route::get('blast', [WhatsappBlastingController::class, 'sendWhatsAppMessagesAsync']);
