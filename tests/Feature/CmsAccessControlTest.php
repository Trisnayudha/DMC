<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\MemberLeadFollowUpController;
use App\Models\CmsRole;
use App\Models\CmsUser;
use App\Support\CmsAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CmsAccessControlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'cms_access_test', 'database.connections.cms_access_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        require_once database_path('migrations/2026_06_17_120000_create_cms_users_table.php');
        (new \CreateCmsUsersTable)->up();
        DB::table('cms_users')->insert([
            'id' => 1, 'name' => 'Existing Admin', 'email' => 'root@example.test',
            'password' => Hash::make('secret123'), 'is_active' => true,
        ]);
        $migration = require database_path('migrations/2026_10_28_100000_add_roles_and_menu_permissions_to_cms_users.php');
        $migration->up();
        $memberMigration = require database_path('migrations/2026_10_28_110000_add_member_team_cms_role.php');
        $memberMigration->up();
        // Tests below explicitly exercise the unconfigured and configured states.
        CmsRole::where('key', '!=', 'superadmin')->update(['permissions' => '[]']);
        Schema::create('company_categories', function ($table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
        });
    }

    private function team(string $role = 'event_team'): CmsUser
    {
        return CmsUser::create([
            'name' => $role, 'email' => $role . '@example.test', 'password' => Hash::make('secret123'),
            'is_active' => true, 'role' => $role,
        ]);
    }

    public function test_migration_preserves_existing_access_and_new_accounts_default_to_a_team(): void
    {
        $this->assertTrue(CmsUser::find(1)->isSuperadmin());
        $this->assertSame(6, CmsRole::count());
        $this->assertSame([], CmsRole::find('event_team')->permissions);
        $this->actingAs(CmsUser::find(1), 'cms')->get('/admin/cms-users')->assertOk()->assertSee('Hak Akses Menu per Level')->assertSee('Secretariat Team')->assertSee('Financial Team')->assertSee('permissions[finance_team][]', false);
        $new = CmsUser::create(['name' => 'New', 'email' => 'new@example.test', 'password' => 'hash']);
        $this->assertSame('event_team', $new->fresh()->role);
    }

    public function test_all_four_teams_default_to_dashboard_and_cannot_access_other_pages_or_actions(): void
    {
        foreach (['event_team', 'marketing_team', 'secretariat_team', 'finance_team'] as $role) {
            $this->actingAs($this->team($role), 'cms');
            $this->get('/admin/home')->assertOk()->assertSee('Akses menu tim belum diatur')->assertDontSee('href="' . url('/admin/cms-users') . '"', false);
            $this->get('/admin/cms-users')->assertForbidden();
            $this->get('/admin/leads')->assertForbidden();
            $this->get('/admin/quick-search?q=test')->assertForbidden();
            $this->post('/admin/leads', ['user_id' => 1])->assertForbidden();
            $this->post('/admin/leads/bulk/temperature', ['ids' => [1]])->assertForbidden();
        }
    }

    public function test_superadmin_can_configure_permissions_and_they_apply_to_allowed_pages_and_sidebar(): void
    {
        $team = $this->team();
        $this->actingAs(CmsUser::find(1), 'cms')->post('/admin/cms-users/permissions', [
            'permissions' => ['event_team' => ['leads', 'events'], 'marketing_team' => ['news']],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(['leads', 'events'], CmsRole::find('event_team')->permissions);
        $this->assertSame([], CmsRole::find('secretariat_team')->permissions);
        $this->mock(MemberLeadFollowUpController::class, function ($mock) {
            $mock->makePartial();
            $mock->shouldReceive('getMiddleware')->andReturn([]);
            $mock->shouldReceive('index')->once()->andReturn(response('Allowed leads'));
        });
        $this->actingAs($team->fresh(), 'cms')->get('/admin/leads')->assertOk()->assertSee('Allowed leads');
        $this->get('/admin/home')->assertSee('href="' . url('admin/leads') . '"', false)->assertDontSee('href="' . url('admin/news') . '"', false);
        $this->get('/admin/news')->assertForbidden();
        $this->post('/admin/cms-users/permissions', ['permissions' => ['event_team' => ['cms_users']]])->assertForbidden();

        $this->actingAs(CmsUser::find(1), 'cms')->post('/admin/cms-users/permissions')->assertRedirect();
        $this->actingAs($team->fresh(), 'cms')->get('/admin/leads')->assertForbidden();
    }

    public function test_finance_team_menu_access_is_configurable_independently(): void
    {
        $finance = $this->team('finance_team');
        $this->actingAs(CmsUser::find(1), 'cms')->post('/admin/cms-users/permissions', [
            'permissions' => ['finance_team' => ['payments']],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(['payments'], CmsRole::find('finance_team')->permissions);
        $this->assertSame([], CmsRole::find('event_team')->permissions);
        $this->actingAs($finance->fresh(), 'cms')->get('/admin/home')->assertOk()
            ->assertSee('Financial Team')->assertSee('href="' . url('/admin/financial-reports') . '"', false)
            ->assertDontSee('href="' . url('/admin/users') . '"', false);
        $this->get('/admin/users')->assertForbidden();
        $this->post('/admin/cms-users/permissions')->assertForbidden();
        $report = Route::getRoutes()->match(\Illuminate\Http\Request::create('/admin/events/sample/financial-report'));
        $this->assertTrue(CmsAccess::allowsRoute($finance->fresh(), $report));
    }

    public function test_requested_team_menu_defaults_apply_without_overwriting_event_team(): void
    {
        CmsRole::find('event_team')->update(['permissions' => ['events', 'members']]);
        $this->artisan('cms:apply-team-defaults')->assertExitCode(1);
        $this->artisan('cms:apply-team-defaults', ['--force' => true])->assertExitCode(0);
        $this->assertSame(['events', 'members'], CmsRole::find('event_team')->permissions);
        foreach (['member_team', 'marketing_team', 'secretariat_team', 'finance_team'] as $team) {
            $this->assertSame(config('cms_access.team_defaults.' . $team), CmsRole::find($team)->permissions);
        }
        $member = $this->team('member_team');
        $nav = CmsAccess::navigation($member);
        $this->assertSame('Events', $nav['Event']['Events']['label']);
        $this->assertArrayNotHasKey('children', $nav['Event']['Events']);
        $this->assertTrue($member->canAccessModule('mailchimp'));
        $this->assertFalse($member->canAccessModule('emails'));
        $this->actingAs($member, 'cms')->get('/admin/home')->assertOk()
            ->assertSee('href="' . url('admin/events') . '"', false)
            ->assertDontSee('href="' . url('admin/cms-users') . '"', false);
    }

    /** Top-level menu labels in sidebar order, across the team's sub-category headers. */
    private function menuLabels(array $navigation): array
    {
        unset($navigation['Main']);
        return array_column(array_merge(...array_map('array_values', array_values($navigation))), 'label');
    }

    public function test_team_menus_follow_requested_order_and_keep_extra_grants_visible(): void
    {
        $this->artisan('cms:apply-team-defaults', ['--force' => true])->assertExitCode(0);
        $expectations = [
            'member_team' => ['Data Tampung', 'Members DMC', 'Follow-Up', 'Database', 'Company Settings', 'Member Sources', 'Member Conversion', 'Lucky Draw', 'Membership Tier Banners', 'Mailchimp Unsubscribes', 'Events'],
            'marketing_team' => ['News', 'Digital Edition', 'Videos Highlight', 'Advertisement', 'Email Management', 'Notifications', 'Whatsapp'],
            'secretariat_team' => ['Sponsors', 'Scholarship'],
            'finance_team' => ['Payment & Financial Report', 'Invoice VA Mandiri', 'Sponsors Annual Report'],
        ];
        $sections = [
            'member_team' => ['Main', 'Member', 'Data & Settings', 'Engagement', 'Membership', 'Event'],
            'marketing_team' => ['Main', 'Content', 'Campaign & Communication'],
            'secretariat_team' => ['Main', 'Sponsor & Scholarship'],
            'finance_team' => ['Main', 'Finance'],
        ];
        foreach ($expectations as $role => $expected) {
            $navigation = CmsAccess::navigation($this->team($role));
            $this->assertSame($sections[$role], array_keys($navigation));
            $this->assertSame($expected, $this->menuLabels($navigation));
        }
        // Finance only gets the annual report: a direct menu, not the Sponsors dropdown.
        $finance = CmsUser::where('role', 'finance_team')->first();
        $annual = Route::getRoutes()->match(\Illuminate\Http\Request::create('/admin/sponsors/annual-report'));
        $download = Route::getRoutes()->match(\Illuminate\Http\Request::create('/admin/sponsors/download-annual-report'));
        $sponsorList = Route::getRoutes()->match(\Illuminate\Http\Request::create('/admin/sponsors'));
        $this->assertTrue(CmsAccess::allowsRoute($finance, $annual));
        $this->assertTrue(CmsAccess::allowsRoute($finance, $download));
        $this->assertFalse(CmsAccess::allowsRoute($finance, $sponsorList));
        // Annual Report actions for Finance: renewal form + Confirm Renewal only.
        $match = function (string $uri, string $method = 'GET') {
            return Route::getRoutes()->match(\Illuminate\Http\Request::create($uri, $method));
        };
        foreach ([['/admin/sponsors/5/update-contract', 'POST'], ['/admin/sponsors/5/renewal-form', 'POST'],
            ['/admin/sponsors/5/renewal-form'], ['/admin/sponsors/5/renewal-form/preview'], ['/admin/sponsors/5/renewal-form/latest'],
            ['/admin/sponsors/kmk-rate'], ['/admin/sponsors/next-quotation-number'], ['/admin/sponsors/next-form-number'],
            ['/admin/sponsors/renewal-form-options'], ['/admin/sponsors/5/followups']] as $allowed) {
            $this->assertTrue(CmsAccess::allowsRoute($finance, $match(...$allowed)), $allowed[0]);
        }
        foreach ([['/admin/sponsors/5/mark-not-renewed', 'POST'], ['/admin/sponsors/5/followups', 'POST'],
            ['/admin/sponsors/contract-history']] as $denied) {
            $this->assertFalse(CmsAccess::allowsRoute($finance, $match(...$denied)), $denied[0]);
        }
        $this->assertSame('sponsors/annual-report', CmsAccess::navigation($finance)['Finance']['sponsors']['path']);
        $secretariat = CmsAccess::navigation(CmsUser::where('role', 'secretariat_team')->first());
        $this->assertContains('Annual Report', array_column($secretariat['Sponsor & Scholarship']['sponsors']['children'], 'label'));
        $role = CmsRole::find('member_team');
        $role->permissions = array_merge($role->permissions, ['interviews']);
        $role->save();
        $labels = $this->menuLabels(CmsAccess::navigation(CmsUser::where('role', 'member_team')->first()->fresh()));
        $this->assertSame('Interview Schedule', end($labels));
        $this->assertContains('Events', $labels);
    }

    public function test_new_install_seeds_default_permissions_without_overwriting_existing_configuration(): void
    {
        CmsRole::find('event_team')->update(['permissions' => ['events']]);
        CmsRole::find('marketing_team')->update(['permissions' => []]);
        $migration = require database_path('migrations/2026_10_28_110000_add_member_team_cms_role.php');
        $migration->up();
        $this->assertSame(['events'], CmsRole::find('event_team')->permissions);
        $this->assertSame(config('cms_access.team_defaults.marketing_team'), CmsRole::find('marketing_team')->permissions);
        $this->assertSame(config('cms_access.team_defaults.member_team'), CmsRole::find('member_team')->permissions);
    }

    public function test_permissions_cannot_grant_cms_management_or_unknown_modules(): void
    {
        $this->actingAs(CmsUser::find(1), 'cms');
        $this->post('/admin/cms-users/permissions', ['permissions' => ['event_team' => ['cms_users']]])
            ->assertSessionHasErrors('permissions.event_team.0');
        $this->post('/admin/cms-users/permissions', ['permissions' => ['superadmin' => []]])
            ->assertSessionHasErrors('permissions');
        $this->post('/admin/cms-users/permissions', ['permissions' => ['event_team' => ['unknown']]])
            ->assertSessionHasErrors('permissions.event_team.0');
        CmsRole::find('event_team')->update(['permissions' => ['cms_users']]);
        $this->actingAs($this->team(), 'cms')->get('/admin/cms-users')->assertForbidden();
    }

    public function test_superadmin_can_assign_roles_and_teams_cannot_create_or_promote_cms_accounts(): void
    {
        $this->actingAs(CmsUser::find(1), 'cms')->post('/admin/cms-users', [
            'name' => 'Marketing Staff', 'email' => 'marketing@example.test', 'password' => 'secret123', 'role' => 'marketing_team',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $staff = CmsUser::where('email', 'marketing@example.test')->firstOrFail();
        $this->assertSame('marketing_team', $staff->role);
        $this->assertTrue(Hash::check('secret123', $staff->password));
        $this->post('/admin/cms-users/' . $staff->id . '/update', [
            'name' => $staff->name, 'email' => $staff->email, 'role' => 'secretariat_team',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('secretariat_team', $staff->fresh()->role);
        $this->actingAs($staff->fresh(), 'cms');
        $this->post('/admin/cms-users', [
            'name' => 'Injected', 'email' => 'injected@example.test', 'password' => 'secret123', 'role' => 'superadmin',
        ])->assertForbidden();
        $this->post('/admin/cms-users/' . $staff->id . '/update', [
            'name' => $staff->name, 'email' => $staff->email, 'role' => 'superadmin',
        ])->assertForbidden();
        $this->assertSame('secretariat_team', $staff->fresh()->role);
        $this->assertSame(2, CmsUser::count());
    }

    public function test_active_superadmin_cannot_remove_own_access_and_invalid_roles_are_rejected(): void
    {
        $this->actingAs(CmsUser::find(1), 'cms');
        $this->post('/admin/cms-users/1/update', [
            'name' => 'Existing Admin', 'email' => 'root@example.test', 'role' => 'event_team',
        ])->assertSessionHasErrors('role');
        $this->post('/admin/cms-users/1/toggle')->assertSessionHas('error');
        $this->delete('/admin/cms-users/1')->assertSessionHas('error');
        $this->post('/admin/cms-users', [
            'name' => 'Unknown', 'email' => 'unknown@example.test', 'password' => 'secret123', 'role' => 'admin',
        ])->assertSessionHasErrors('role');
        $this->assertTrue(CmsUser::find(1)->isSuperadmin());
        $this->assertTrue(CmsUser::find(1)->is_active);
    }

    public function test_inactive_or_unknown_roles_and_non_cms_sessions_are_denied(): void
    {
        $team = $this->team();
        $team->update(['role' => 'unknown']);
        $this->actingAs($team->fresh(), 'cms')->get('/admin/home')->assertForbidden();
        $team->update(['role' => 'superadmin', 'is_active' => false]);
        $this->actingAs($team->fresh(), 'cms')->get('/admin/cms-users')->assertRedirect(route('cms.login'));
        $this->get('/admin/cms-users')->assertRedirect(route('cms.login'));
    }

    public function test_route_catalog_is_explicit_and_uploads_require_a_content_module(): void
    {
        $team = $this->team();
        foreach (Route::getRoutes() as $route) {
            $middleware = $route->gatherMiddleware();
            if (!in_array('cms_auth', $middleware, true)) {
                continue;
            }
            $controller = class_basename(explode('@', $route->getAction('controller') ?? '')[0]);
            if (in_array($controller, ['TestController', 'ImageUploadController', 'UploadController', 'EditorUploadController'], true)) {
                continue;
            }
            $this->assertNotNull(CmsAccess::moduleForRoute($route), $route->uri());
        }
        foreach (CmsAccess::modules() as $key => $module) {
            $route = Route::getRoutes()->match(\Illuminate\Http\Request::create('/admin/' . $module[2]));
            $this->assertSame($key, CmsAccess::moduleForRoute($route), 'Navigation mismatch: ' . $key);
        }
        foreach (config('cms_access.submenus') as $key => $links) {
            foreach ($links as $link) {
                $route = Route::getRoutes()->match(\Illuminate\Http\Request::create('/admin/' . $link[1]));
                $this->assertSame($link[2] ?? $key, CmsAccess::moduleForRoute($route), 'Submenu mismatch: ' . $link[0]);
            }
        }
        $this->actingAs($team, 'cms')->post('/admin/editor/upload')->assertForbidden();
        CmsRole::find('event_team')->update(['permissions' => ['news']]);
        $route = Route::getRoutes()->match(\Illuminate\Http\Request::create('/admin/editor/upload', 'POST'));
        $this->assertTrue(CmsAccess::allowsRoute($team->fresh(), $route));
        $unknown = new \Illuminate\Routing\Route('GET', '/admin/future-module', ['uses' => function () {}]);
        $this->assertFalse(CmsAccess::allowsRoute($team, $unknown));
        $this->assertTrue(CmsAccess::allowsRoute(CmsUser::find(1), $unknown));
    }
}
