<?php

namespace Tests\Feature;

use App\Models\CmsUser;
use App\Models\EventQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventQuestionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'questions_test', 'database.connections.questions_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        require_once database_path('migrations/2026_06_17_120000_create_cms_users_table.php');
        (new \CreateCmsUsersTable)->up();
        (require database_path('migrations/2026_10_28_100000_add_roles_and_menu_permissions_to_cms_users.php'))->up();
        Schema::create('company_categories', function ($table) {
            $table->id(); $table->string('name'); $table->boolean('is_active')->default(true); $table->integer('sort_order')->default(0);
        });
        Schema::create('users', function ($table) {
            $table->id(); $table->string('name'); $table->string('email');
        });
        Schema::create('events', function ($table) {
            $table->id(); $table->string('slug');
        });
        Schema::create('payment', function ($table) {
            $table->id(); $table->unsignedBigInteger('member_id'); $table->unsignedBigInteger('events_id'); $table->string('code_payment');
        });
        Schema::create('company', function ($table) {
            $table->id(); $table->unsignedBigInteger('users_id'); $table->string('company_name')->nullable();
        });
        (require database_path('migrations/2026_11_06_000000_create_event_questions_table.php'))->up();
        (require database_path('migrations/2026_11_09_000000_add_code_payment_to_event_questions.php'))->up();
    }

    public function test_question_uses_registration_identity_from_matching_event(): void
    {
        DB::table('users')->insert(['id' => 1, 'name' => 'Yudha', 'email' => 'yudha@example.test']);
        DB::table('events')->insert([
            ['id' => 1, 'slug' => 'indonesian-energy-market-briefing-2026'],
            ['id' => 2, 'slug' => 'another-event'],
        ]);
        DB::table('payment')->insert([
            ['member_id' => 1, 'events_id' => 1, 'code_payment' => 'MATCH123'],
            ['member_id' => 1, 'events_id' => 2, 'code_payment' => 'OTHER123'],
        ]);
        DB::table('company')->insert(['users_id' => 1, 'company_name' => 'DMC']);

        $url = route('event-questions.create', ['code_payment' => 'MATCH123']);
        $this->get($url)->assertOk()->assertSee('Your question')->assertDontSee('yudha@example.test')
            ->assertDontSee('name="name"', false)->assertDontSee('name="email"', false)->assertDontSee('name="company"', false)
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->from($url)->post(route('event-questions.store'), ['code_payment' => 'MATCH123'])
            ->assertSessionHasErrors('question')->assertRedirect($url);
        $this->post(route('event-questions.store'), [
            'code_payment' => 'MATCH123', 'question' => 'What is next?',
            'name' => 'Impersonator', 'email' => 'fake@example.test', 'company' => 'Fake',
        ])->assertRedirect(route('event-questions.create'))->assertSessionHas('success');
        $this->assertDatabaseHas('event_questions', [
            'event_slug' => 'indonesian-energy-market-briefing-2026', 'code_payment' => 'MATCH123',
            'name' => 'Yudha', 'email' => 'yudha@example.test', 'company' => 'DMC', 'question' => 'What is next?',
        ]);
        $this->flushSession();
        foreach (['OTHER123', 'UNKNOWN'] as $code) {
            $this->get(route('event-questions.create', ['code_payment' => $code]))->assertSee('registration link is invalid')->assertDontSee('name="question"', false);
            $this->post(route('event-questions.store'), ['code_payment' => $code, 'question' => 'Invalid'])
                ->assertSessionHasErrors('code_payment');
        }
        $this->post(route('event-questions.store'), ['question' => 'Missing code'])->assertSessionHasErrors('code_payment');
        $this->assertDatabaseCount('event_questions', 1);
    }

    public function test_cms_access_and_escaping(): void
    {
        EventQuestion::create(['event_slug' => 'indonesian-energy-market-briefing-2026', 'name' => 'Yudha', 'email' => 'yudha@example.test', 'question' => '<script>bad</script>']);
        $this->get('/admin/event-questions')->assertRedirect(route('cms.login'));
        $admin = CmsUser::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => Hash::make('secret'), 'role' => 'superadmin', 'is_active' => true]);
        $this->actingAs($admin, 'cms')->get('/admin/event-questions?search=Yudha')->assertOk()->assertSee('&lt;script&gt;bad&lt;/script&gt;', false)->assertDontSee('<script>bad</script>', false);
    }

    protected function tearDown(): void
    {
        DB::purge('questions_test');
        parent::tearDown();
    }
}
