<?php

namespace Tests\Feature;

use App\Models\CmsRole;
use App\Models\CmsUser;
use App\Models\CmsUserLog;
use App\Support\CmsAudit;
use App\Support\CmsAccess;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class CmsUserLogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        // The shared CMS layout renders the quick-search category filter for Superadmins.
        Schema::create('company_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active');
            $table->integer('sort_order');
        });
        require_once database_path('migrations/2026_11_19_000001_create_cms_user_logs_table.php');
        (new \CreateCmsUserLogsTable())->up();
    }

    private function admin(int $id = 701, string $role = 'superadmin'): CmsUser
    {
        $admin = new CmsUser(['name' => 'Yudha', 'role' => $role, 'is_active' => true]);
        $admin->id = $id;
        $admin->setRelation('cmsRole', new CmsRole(['permissions' => ['ngrok', 'users_log']]));
        return $admin;
    }

    public function test_crud_validation_and_missing_records_are_audited_without_values()
    {
        Schema::create('ngrok', function (Blueprint $table) {
            $table->id();
            $table->string('link');
            $table->timestamps();
        });
        $this->actingAs($this->admin(), 'cms');
        $this->get('/admin/ngrok')->assertOk()->assertSee('cms-audit.js');
        $this->postJson('/admin/ngrok', ['link' => 'https://private-link.test', 'password' => 'private-password'])->assertOk();
        $this->putJson('/admin/ngrok/1', ['link' => 'https://private-update.test'])->assertOk();
        $this->putJson('/admin/ngrok/1', [])->assertStatus(422);
        $this->deleteJson('/admin/ngrok/1')->assertOk();
        $this->deleteJson('/admin/ngrok/1')->assertNotFound();

        $logs = CmsUserLog::orderBy('id')->get();
        $this->assertEquals(['menu', 'create', 'update', 'update', 'delete', 'delete'], $logs->pluck('action')->all());
        $this->assertSame(['ngrok'], $logs->pluck('menu')->unique()->all());
        $this->assertSame(['link'], $logs[1]->fields);
        $this->assertSame(['id' => '1'], $logs[4]->record_ids);
        $this->assertSame('invalid', $logs[3]->outcome);
        $this->assertSame('failed', $logs[5]->outcome);
        $this->assertStringNotContainsString('private-', $logs->toJson());
    }

    public function test_browser_interactions_stay_with_the_authenticated_admin_and_original_page()
    {
        $this->actingAs($this->admin(702, 'event_team'), 'cms');
        $this->get('/admin/home')->assertOk();
        $page = CmsUserLog::firstOrFail();
        $payload = ['page' => $page->page_key, 'action' => 'click', 'control' => 'createModal'];
        $this->postJson('/api/cms-activity/interaction', $payload + ['menu' => 'cms_users', 'cms_user_id' => 999])->assertNoContent();
        $this->postJson('/api/cms-activity/interaction', array_merge($payload, ['action' => 'field', 'control' => 'name']))->assertNoContent();
        $this->postJson('/api/cms-activity/interaction', array_merge($payload, ['action' => 'field', 'control' => 'password']))->assertNoContent();
        $this->assertSame(3, CmsUserLog::count());
        $this->assertDatabaseHas('cms_user_logs', ['page_log_id' => $page->id, 'cms_user_id' => 702,
            'menu' => 'dashboard', 'action' => 'click', 'method' => null, 'http_status' => null]);

        $this->actingAs($this->admin(703), 'cms');
        $this->postJson('/api/cms-activity/interaction', $payload)->assertNotFound();
        $this->assertSame(3, CmsUserLog::count());
    }

    public function test_main_log_and_details_use_the_same_filters_and_keep_admins_separate()
    {
        $this->actingAs($this->admin(701, 'event_team'), 'cms');
        $this->get('/admin/home')->assertOk();
        $this->get('/admin/home')->assertOk();
        $this->actingAs($this->admin(704, 'finance_team'), 'cms');
        $this->get('/admin/home')->assertOk();
        $this->actingAs($this->admin(706), 'cms');
        $this->get('/admin/users_log')->assertOk()->assertSee('View activity');
        $filters = ['admin_id' => 701, 'menu' => 'dashboard', 'action' => 'menu',
            'from' => now()->toDateString(), 'to' => now()->toDateString()];
        $rows = $this->get('/admin/users_log?' . http_build_query($filters))->assertOk()->viewData('logs');
        $this->assertSame(1, $rows->total());
        $this->assertEquals(2, $rows[0]->total);
        $details = $this->get('/admin/users_log/701/dashboard?' . http_build_query($filters))->assertOk()->viewData('logs');
        $this->assertSame(2, $details->total());
        $this->get('/admin/users_log/701/dashboard?to=2000-01-01')->assertOk()->assertSee('No activity matches these filters.');
        $this->assertDatabaseHas('cms_user_logs', ['cms_user_id' => 704, 'menu' => 'dashboard', 'action' => 'menu']);
    }

    public function test_browser_collector_uses_the_real_cms_session_and_csrf_without_consuming_flash()
    {
        Schema::create('cms_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('role');
            $table->boolean('is_active');
            $table->rememberToken();
        });
        DB::table('cms_users')->insert(['name' => 'Session admin', 'email' => 'audit@example.test',
            'password' => Hash::make('test-only-password'), 'role' => 'superadmin', 'is_active' => true]);
        $login = $this->post('/admin/login', ['email' => 'audit@example.test', 'password' => 'test-only-password']);
        $login->assertRedirect();
        $this->assertDatabaseHas('cms_user_logs', ['action' => 'login', 'cms_user_id' => 1]);
        $cookie = $login->getCookie(config('session.cookie'));
        $this->withUnencryptedCookie($cookie->getName(), $cookie->getValue())->withCredentials();
        Auth::forgetGuards();
        $this->get('/admin/users_log')->assertOk();
        $session = app('session.store');
        $session->flash('success', 'Saved');
        $session->save();
        $storedSession = $session->getHandler()->read($session->getId());
        $payload = ['page' => CmsUserLog::where('route', 'users_log.index')->firstOrFail()->page_key,
            'action' => 'click', 'control' => 'open'];

        // Exercise actual CSRF validation, which Laravel normally disables in feature tests.
        $this->app['env'] = 'local';
        Auth::forgetGuards();
        $this->postJson('/api/cms-activity/interaction', $payload)->assertStatus(419);
        $headers = ['X-CSRF-TOKEN' => $session->token()];
        $this->postJson('/api/cms-activity/interaction', $payload, $headers)->assertNoContent()
            ->assertCookieMissing(config('session.cookie'));
        $this->assertSame($storedSession, $session->getHandler()->read($session->getId()));
        $this->post('/admin/logout', [], $headers)->assertRedirect();
        $this->assertDatabaseHas('cms_user_logs', ['action' => 'logout', 'cms_user_id' => 1]);
    }

    public function test_only_super_admins_can_read_logs_and_denied_requests_are_recorded()
    {
        $this->getJson('/admin/users_log')->assertRedirect(route('cms.login'));
        $this->getJson('/admin/users_log/export')->assertRedirect(route('cms.login'));
        $this->postJson('/api/cms-activity/interaction')->assertUnauthorized();
        $this->assertSame(0, CmsUserLog::count());
        $this->actingAs($this->admin(705, 'event_team'), 'cms');
        $this->get('/admin/home')->assertOk()->assertDontSee('Users Log');
        $this->assertArrayNotHasKey('users_log', CmsAccess::configurableModules());
        $this->getJson('/admin/users_log')->assertForbidden();
        $this->get('/admin/users_log/701/events')->assertForbidden();
        $this->getJson('/admin/users_log/export')->assertForbidden();
        $this->getJson('/admin/users_log/701/events/export')->assertForbidden();
        $this->assertSame(4, CmsUserLog::where('outcome', 'denied')->count());
    }

    public function test_pagination_keeps_the_same_groups_as_new_events_arrive()
    {
        $event = ['admin_name' => 'Reviewer', 'menu' => 'events', 'action' => 'menu', 'method' => 'GET',
            'route' => 'events.index', 'occurred_at' => '2026-11-10 12:00:00'];
        for ($admin = 1; $admin <= 125; $admin++) {
            CmsUserLog::create(['cms_user_id' => $admin] + $event);
        }
        $this->actingAs($this->admin(), 'cms');
        $first = $this->get('/admin/users_log?menu=events&per_page=50')->assertOk()->assertSee('Rows per page')->viewData('logs');
        $this->assertSame(125, $first->total());
        $this->assertCount(50, $first);
        CmsUserLog::create(['cms_user_id' => 126, 'occurred_at' => '2026-11-11 12:00:00'] + $event);
        $second = $this->get($first->nextPageUrl())->assertOk()->viewData('logs');
        $this->assertSame(125, $second->total());
        $this->assertEquals(51, $second->first()->cms_user_id);
        $last = $this->get($second->nextPageUrl())->assertOk()->viewData('logs');
        $this->assertCount(25, $last);
        $this->getJson('/admin/users_log?per_page=999')->assertStatus(422);
    }

    public function test_excel_downloads_include_every_filtered_page_and_preserve_audit_text()
    {
        $event = ['cms_user_id' => 9, 'admin_name' => '=Reviewer', 'menu' => 'events', 'action' => 'update',
            'route' => 'events.update', 'method' => 'PUT', 'http_status' => 200, 'outcome' => 'completed',
            'record_ids' => ['event' => '14'], 'fields' => ['title'], 'occurred_at' => '2026-11-10 14:30:00'];
        for ($i = 0; $i < 205; $i++) {
            CmsUserLog::create($event);
        }
        foreach ([['cms_user_id' => 12], ['action' => 'delete'], ['menu' => 'news'], ['occurred_at' => '2026-11-11 00:00:00']] as $outside) {
            CmsUserLog::create($outside + $event);
        }
        $this->actingAs($this->admin(), 'cms');
        $query = '?admin_id=9&menu=events&action=update&from=2026-11-10&to=2026-11-10&per_page=10';
        $displayTime = '10 November 2026 14:30 WIB';
        $summary = $this->get('/admin/users_log' . $query)->assertOk()->assertSeeText($displayTime);
        $mainPage = new Crawler($summary->getContent());
        $exportLink = '//a[normalize-space(.)="Export to Excel"]';
        $mainExportUrl = $mainPage->filterXPath($exportLink)->attr('href');
        $detailUrl = $mainPage->filterXPath('//a[normalize-space(.)="View activity"]')->attr('href');
        CmsUserLog::create($event);
        $detail = $this->get($detailUrl)->assertOk()->assertSeeText($displayTime)->viewData('logs');
        $this->assertCount(10, $detail);
        $this->assertEquals(205, $detail->total());
        $pageTwo = $this->get($detail->nextPageUrl())->assertOk();
        $this->assertEquals(195, $pageTwo->viewData('logs')->first()->id);
        $detailExportUrl = (new Crawler($pageTwo->getContent()))->filterXPath($exportLink)->attr('href');

        foreach ([$mainExportUrl, $detailExportUrl] as $url) {
            $download = $this->get($url)->assertOk()->assertDownload();
            $path = $download->baseResponse->getFile()->getPathname();
            try {
                $book = IOFactory::load($path);
                if ($url === $mainExportUrl) {
                    $this->assertSame(['Summary', 'Activity details'], $book->getSheetNames());
                    $sheet = $book->getSheetByName('Summary');
                    $this->assertSame(2, $sheet->getHighestDataRow());
                    $this->assertSame([205, 0, 205], $sheet->rangeToArray('D2:F2', null, false, false)[0]);
                    $this->assertSame(DataType::TYPE_NUMERIC, $sheet->getCell('G2')->getDataType());
                    $this->assertSame($displayTime, $sheet->getCell('G2')->getFormattedValue());
                } else {
                    $this->assertSame(['Activity details'], $book->getSheetNames());
                }
                $sheet = $book->getSheetByName('Activity details');
                $this->assertSame(206, $sheet->getHighestDataRow());
                $this->assertSame('205', $sheet->getCell('A2')->getValue());
                $this->assertSame('1', $sheet->getCell('A206')->getValue());
                $this->assertSame(DataType::TYPE_NUMERIC, $sheet->getCell('B2')->getDataType());
                $this->assertSame($displayTime, $sheet->getCell('B2')->getFormattedValue());
                $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('D2')->getDataType());
                $this->assertSame('=Reviewer', $sheet->getCell('D2')->getValue());
                $this->assertSame('Update record', $sheet->getCell('G2')->getValue());
                $this->assertSame('event #14', $sheet->getCell('K2')->getValue());
                $this->assertSame('title', $sheet->getCell('L2')->getValue());
                $this->assertSame('Request completed', $sheet->getCell('M2')->getValue());
                $book->disconnectWorksheets();
            } finally {
                unlink($path);
            }
        }
        $this->assertSame(2, CmsUserLog::where('menu', 'users_log')->where('action', 'export')->count());
    }

    public function test_all_sidebar_routes_and_shared_actions_resolve_to_their_menu()
    {
        foreach (CmsAccess::modules() as $key => $module) {
            $path = '/admin/' . $module[2];
            $this->assertSame($key, CmsAudit::menu($this->matchedRequest($path)), $path);
        }
        foreach ([
            '/admin/users?status_member=pending' => 'users_status_member_pending',
            '/admin/users?status_member=active' => 'users_status_member_active',
            '/admin/sponsors/contact-directory/export' => 'sponsors_contact_directory',
            '/admin/finance-reports' => 'finance_reports',
            '/admin/partnership-visitors' => 'partnership_visitors',
            '/test/email' => 'system_tools',
        ] as $path => $key) {
            $this->assertSame($key, CmsAudit::menu($this->matchedRequest($path)), $path);
        }

        $shared = $this->matchedRequest('/admin/sponsors/9/update-contract', 'POST');
        $shared->headers->set('referer', 'http://localhost/admin/sponsors/annual-report');
        $this->assertSame('sponsor_annual_report', CmsAudit::menu($shared));
        $shared->headers->set('referer', 'https://external.test/admin/sponsors/annual-report');
        $this->assertSame('sponsors', CmsAudit::menu($shared));
        $this->assertSame(['sponsor' => '9'], CmsAudit::recordIds($shared));

        $edit = $this->matchedRequest('/admin/events/editcategory', 'POST');
        $this->assertSame('event_categories', CmsAudit::menu($edit));
        $this->assertSame('update', CmsAudit::action($edit, 'event_categories'));
    }

    public function test_public_pages_are_excluded_and_inactive_sessions_cannot_submit_interactions()
    {
        $this->actingAs($this->admin(), 'cms');
        $this->get('/privacy')->assertOk()->assertDontSee('cms-audit.js');
        $this->get('/admin/login')->assertRedirect();
        $this->assertSame(0, CmsUserLog::count());
        $this->get('/admin/users_log')->assertOk();
        $page = CmsUserLog::firstOrFail();
        $inactive = $this->admin();
        $inactive->is_active = false;
        $this->actingAs($inactive, 'cms');
        $this->postJson('/api/cms-activity/interaction', [
            'page' => $page->page_key, 'action' => 'click', 'control' => 'submit',
        ])->assertForbidden();
        $this->get('/admin/users_log')->assertRedirect(route('cms.login'));
        $this->assertSame('denied', CmsUserLog::orderByDesc('id')->first()->outcome);
        $this->assertSame(2, CmsUserLog::count());
    }

    private function matchedRequest(string $path, string $method = 'GET'): Request
    {
        $request = Request::create($path, $method);
        $route = Route::getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);
        return $request;
    }
}
