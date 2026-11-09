<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\EventsDetailParticipantController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ParticipantConfirmationAjaxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'confirmation_test', 'database.connections.confirmation_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('payment', function ($table) {
            $table->id(); $table->unsignedBigInteger('member_id'); $table->unsignedBigInteger('events_id'); $table->string('code_payment'); $table->timestamps();
        });
        Schema::create('users', function ($table) { $table->id(); $table->string('name'); $table->string('email'); });
        Schema::create('profiles', function ($table) { $table->id(); $table->unsignedBigInteger('users_id'); $table->string('phone'); $table->string('job_title'); });
        Schema::create('company', function ($table) { $table->id(); $table->unsignedBigInteger('users_id'); $table->string('company_name'); $table->string('address'); });
        Schema::create('events', function ($table) {
            $table->id(); $table->string('name'); $table->string('slug'); $table->string('start_date'); $table->string('end_date'); $table->string('start_time'); $table->string('end_time');
        });
        Schema::create('users_event', function ($table) {
            $table->id(); $table->unsignedBigInteger('users_id'); $table->unsignedBigInteger('events_id'); $table->unsignedBigInteger('payment_id');
            $table->timestamp('reminder')->nullable(); $table->timestamp('reminder_wa')->nullable();
            $table->unsignedBigInteger('pic_id_reminder')->nullable(); $table->unsignedBigInteger('pic_id_reminder_wa')->nullable(); $table->timestamps();
        });
        DB::table('users')->insert(['id' => 1, 'name' => 'Participant', 'email' => 'participant@example.test']);
        DB::table('payment')->insert(['id' => 1, 'member_id' => 1, 'events_id' => 1, 'code_payment' => 'TEST123']);
        DB::table('profiles')->insert(['users_id' => 1, 'phone' => '081234', 'job_title' => 'Manager']);
        DB::table('company')->insert(['users_id' => 1, 'company_name' => 'Company', 'address' => 'Jakarta']);
        DB::table('events')->insert(['id' => 1, 'name' => 'Event', 'slug' => 'event', 'start_date' => '2026-11-10', 'end_date' => '2026-11-10', 'start_time' => '09:00', 'end_time' => '17:00']);
        Auth::shouldReceive('id')->andReturn(9);
        Auth::shouldReceive('user')->andReturn((object) ['name' => 'Admin']);
    }

    private function send(string $method, bool $failure = false)
    {
        $controller = Mockery::mock(EventsDetailParticipantController::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('generateAndStoreQrCode')->andReturn('/qr.png');
        $sendMethod = $method === 'confirmation' ? 'sendConfirmationEmail' : 'sendConfirmationWhatsapp';
        $expectation = $controller->shouldReceive($sendMethod)->once();
        if ($failure) $expectation->andThrow(new \RuntimeException('Gateway error'));
        $request = Request::create('/admin/events/confirmation', 'POST', [
            'users_id' => 1, 'events_id' => 1, 'payment_id' => 1, 'method' => $method, 'phone' => '081234',
        ], [], [], ['HTTP_ACCEPT' => 'application/json']);
        return $controller->sendParticipant($request);
    }

    public function test_successful_email_and_whatsapp_update_only_their_own_status(): void
    {
        $email = $this->send('confirmation');
        $this->assertSame(200, $email->getStatusCode());
        $this->assertSame('Admin', $email->getData(true)['sent_by']);
        $this->assertDatabaseHas('users_event', ['payment_id' => 1, 'pic_id_reminder' => 9, 'reminder_wa' => null]);
        $whatsapp = $this->send('confirmation_wa');
        $this->assertSame(200, $whatsapp->getStatusCode());
        $this->assertDatabaseHas('users_event', ['payment_id' => 1, 'pic_id_reminder' => 9, 'pic_id_reminder_wa' => 9]);
        $this->assertNotNull(DB::table('users_event')->value('reminder_wa'));
    }

    public function test_failures_do_not_mark_email_or_whatsapp_as_sent(): void
    {
        foreach (['confirmation', 'confirmation_wa'] as $method) {
            $response = $this->send($method, true);
            $this->assertSame(500, $response->getStatusCode());
            $this->assertSame('Sending failed or its status could not be saved. Please check before retrying.', $response->getData(true)['message']);
        }
        $this->assertDatabaseHas('users_event', ['payment_id' => 1, 'reminder' => null, 'reminder_wa' => null]);
    }

    public function test_mismatched_event_is_rejected_before_sending(): void
    {
        $request = Request::create('/admin/events/confirmation', 'POST', ['users_id' => 1, 'events_id' => 2, 'payment_id' => 1, 'method' => 'confirmation'], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $response = (new EventsDetailParticipantController())->sendParticipant($request);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertDatabaseCount('users_event', 0);
    }

    public function test_whatsapp_event_date_uses_jakarta_day_for_today_and_tomorrow(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-09 18:00:00', 'UTC')); // 10 November in Jakarta
        $method = new \ReflectionMethod(EventsDetailParticipantController::class, 'whatsappEventDate');
        $method->setAccessible(true);
        $controller = new EventsDetailParticipantController();
        $this->assertSame('*TODAY*, 10 November 2026', $method->invoke($controller, '2026-11-10'));
        $this->assertSame('*TOMORROW*, 11 November 2026', $method->invoke($controller, '2026-11-11'));
        $this->assertSame('*TOMORROW*, 12 November 2026', $method->invoke($controller, '2026-11-12'));
        $this->assertSame('13 November 2026', $method->invoke($controller, '2026-11-13'));
        $this->assertSame('9 November 2026', $method->invoke($controller, '2026-11-09'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        DB::purge('confirmation_test');
        parent::tearDown();
    }
}
