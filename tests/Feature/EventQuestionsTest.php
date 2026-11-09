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
    }

    public function test_reminder_link_prefills_only_matching_event_and_preserves_edits(): void
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

        $this->get(route('event-questions.create', ['code_payment' => 'MATCH123']))
            ->assertOk()->assertSee('value="Yudha"', false)->assertSee('value="yudha@example.test"', false)
            ->assertSee('value="DMC"', false)->assertHeader('Cache-Control', 'no-store, private');
        $this->get(route('event-questions.create', ['code_payment' => 'OTHER123']))->assertDontSee('yudha@example.test');
        $this->get(route('event-questions.create', ['code_payment' => 'UNKNOWN']))->assertDontSee('yudha@example.test');
        $this->withSession(['_old_input' => ['name' => 'Edited']])
            ->get(route('event-questions.create', ['code_payment' => 'MATCH123']))->assertSee('value="Edited"', false);
    }

    public function test_public_submission_is_validated_and_saved(): void
    {
        $this->get(route('event-questions.create'))->assertOk()->assertSee('Submit Your Question');
        $this->post(route('event-questions.store'), [])->assertSessionHasErrors(['name', 'email', 'question']);
        $this->post(route('event-questions.store'), ['name' => 'Yudha', 'email' => 'yudha@example.test', 'company' => 'DMC', 'question' => 'What is next?'])
            ->assertRedirect(route('event-questions.create'))->assertSessionHas('success');
        $this->assertDatabaseHas('event_questions', ['event_slug' => 'indonesian-energy-market-briefing-2026', 'question' => 'What is next?']);
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
