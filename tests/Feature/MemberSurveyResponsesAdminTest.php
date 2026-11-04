<?php

namespace Tests\Feature;

use App\Models\CmsRole;
use App\Models\CmsUser;
use App\Models\DmcMemberSurvey;
use App\Support\CmsAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MemberSurveyResponsesAdminTest extends TestCase
{
    private $admin;
    private $member;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'survey_admin_test', 'database.connections.survey_admin_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        require_once database_path('migrations/2026_06_17_120000_create_cms_users_table.php');
        (new \CreateCmsUsersTable)->up();
        $migration = require database_path('migrations/2026_10_28_100000_add_roles_and_menu_permissions_to_cms_users.php');
        $migration->up();
        $migration = require database_path('migrations/2026_10_28_110000_add_member_team_cms_role.php');
        $migration->up();
        Schema::create('company_categories', function ($table) {
            $table->id(); $table->string('name'); $table->boolean('is_active')->default(true); $table->integer('sort_order')->default(0);
        });
        require_once database_path('migrations/2025_12_15_133945_create_dmc_member_surveys_table.php');
        require_once database_path('migrations/2025_12_15_152622_add_email_preference_fields_to_dmc_member_surveys_table.php');
        require_once database_path('migrations/2026_11_04_000000_add_revision_fields_to_dmc_member_surveys.php');
        (new \CreateDmcMemberSurveysTable)->up();
        (new \AddEmailPreferenceFieldsToDmcMemberSurveysTable)->up();
        (new \AddRevisionFieldsToDmcMemberSurveys)->up();
        $this->admin = CmsUser::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => Hash::make('secret'), 'role' => 'superadmin', 'is_active' => true]);
        CmsRole::where('key', 'member_team')->update(['permissions' => ['members']]);
        $this->member = CmsUser::create(['name' => 'Member team', 'email' => 'member@example.test', 'password' => Hash::make('secret'), 'role' => 'member_team', 'is_active' => true]);
    }

    public function test_superadmin_can_search_and_view_complete_responses(): void
    {
        $first = DmcMemberSurvey::create([
            'full_name' => 'Jane Example', 'company' => 'Acme', 'email' => 'jane@example.test',
            'event_types' => ['Other'], 'event_types_other' => 'Site visit', 'nominee_reason' => 'Leadership',
            'event_improvement_aspects' => ['Other'], 'event_improvement_other' => 'More discussion',
            'email_frequency' => ['2 emails per week'], 'social_familiarity' => ['Very familiar'],
            'app_awareness' => ['No'], 'additional_feedback' => '<script>unsafe</script>', 'event_improvement' => 'Previous feedback',
        ]);
        $first->created_at = '2026-03-01 09:00:00'; $first->save();
        $second = DmcMemberSurvey::create(['full_name' => 'Other Person', 'company' => 'Other Co', 'email' => 'other@example.test', 'event_types' => ['Workshops'], 'social_familiarity' => ['Not familiar'], 'app_awareness' => ['No']]);
        $second->created_at = '2025-03-01 09:00:00'; $second->save();
        $this->actingAs($this->admin, 'cms')->get('/admin/member-surveys?search=acme&year=2026')->assertOk()->assertSee('Jane Example')->assertDontSee('Other Person');
        $this->get('/admin/member-surveys?year=2025')->assertOk()->assertSee('Other Person')->assertDontSee('Jane Example');
        $this->get('/admin/member-surveys/' . $first->id)->assertOk()->assertSee('Site visit')->assertSee('Leadership')->assertSee('More discussion')->assertSee('2 emails per week')->assertSee('Previous feedback')->assertSee('&lt;script&gt;unsafe&lt;/script&gt;', false)->assertDontSee('<script>unsafe</script>', false);
        $this->assertSame('Member Survey', CmsAccess::navigation($this->admin)['Member Team — Membership'][2]['label']);
    }

    public function test_member_team_has_no_menu_or_direct_access_by_default(): void
    {
        $survey = DmcMemberSurvey::create(['full_name' => 'Jane', 'company' => 'Acme', 'email' => 'jane@example.test', 'event_types' => ['Workshops'], 'social_familiarity' => ['Not familiar'], 'app_awareness' => ['No']]);
        $nav = CmsAccess::navigation($this->member);
        $this->assertNotContains('Member Survey', array_column($nav['Membership'], 'label'));
        $this->actingAs($this->member, 'cms')->get('/admin/member-surveys')->assertForbidden();
        $this->get('/admin/member-surveys/' . $survey->id)->assertForbidden();
        CmsRole::where('key', 'member_team')->update(['permissions' => ['members', 'member_surveys']]);
        $member = $this->member->fresh();
        $this->assertContains('Member Survey', array_column(CmsAccess::navigation($member)['Membership'], 'label'));
        $this->actingAs($member, 'cms')->get('/admin/member-surveys')->assertOk();
    }

    protected function tearDown(): void
    {
        DB::purge('survey_admin_test');
        parent::tearDown();
    }
}
