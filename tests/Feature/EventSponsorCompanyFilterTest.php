<?php

namespace Tests\Feature;

use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventSponsorCompanyFilterTest extends TestCase
{
    public function test_filter_uses_assigned_sponsor_or_event_sponsor_code(): void
    {
        config(['database.default' => 'sponsor_filter_test', 'database.connections.sponsor_filter_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        Schema::create('users', function ($t) { $t->id(); $t->string('status_member')->nullable(); });
        Schema::create('company', function ($t) { $t->id(); $t->integer('users_id'); });
        Schema::create('profiles', function ($t) { $t->id(); $t->integer('users_id'); });
        Schema::create('cms_users', function ($t) { $t->id(); $t->string('name'); });
        Schema::create('sponsors', function ($t) { $t->id(); $t->string('name'); $t->string('status'); });
        Schema::create('event_sponsors', function ($t) { $t->id(); $t->integer('events_id'); $t->integer('sponsors_id'); $t->string('code_access'); });
        Schema::create('payment', function ($t) { $t->id(); $t->integer('member_id'); $t->integer('events_id'); $t->integer('pic_id')->nullable(); $t->integer('sponsor_id')->nullable(); $t->string('sponsor_code')->nullable(); $t->string('package'); $t->timestamp('created_at')->nullable(); });
        DB::table('users')->insert([['id' => 1], ['id' => 2], ['id' => 3]]);
        DB::table('sponsors')->insert([['id' => 10, 'name' => 'Sponsor A', 'status' => 'publish'], ['id' => 20, 'name' => 'Sponsor B', 'status' => 'publish']]);
        DB::table('event_sponsors')->insert([['events_id' => 1, 'sponsors_id' => 10, 'code_access' => 'ABC'], ['events_id' => 2, 'sponsors_id' => 20, 'code_access' => 'ABC']]);
        DB::table('payment')->insert([
            ['id' => 1, 'member_id' => 1, 'events_id' => 1, 'sponsor_id' => 10, 'sponsor_code' => null, 'package' => 'sponsor'],
            ['id' => 2, 'member_id' => 2, 'events_id' => 1, 'sponsor_id' => null, 'sponsor_code' => 'ABC', 'package' => 'sponsor'],
            ['id' => 3, 'member_id' => 3, 'events_id' => 1, 'sponsor_id' => 20, 'sponsor_code' => null, 'package' => 'free'],
        ]);
        $this->assertEqualsCanonicalizing([1, 2], PaymentService::listPaymentRegister(1, null, 10)->pluck('payment_id')->all());
        $this->assertSame([3], PaymentService::listPaymentRegister(1, 'free', 20)->pluck('payment_id')->all());
        $this->assertCount(0, PaymentService::listPaymentRegister(1, 'free', 10));
        DB::table('sponsors')->where('id', 20)->update(['status' => 'draft']);
        $this->assertCount(0, PaymentService::listPaymentRegister(1, null, 20));
        $this->assertCount(3, PaymentService::listPaymentRegister(1, null));
    }
}
