<?php

namespace Tests\Feature;

use App\Models\CmsRole;
use App\Models\CmsUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SponsorContactDirectoryActionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'contact_test', 'database.connections.contact_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        require_once database_path('migrations/2026_06_17_120000_create_cms_users_table.php');
        (new \CreateCmsUsersTable)->up();
        $migration = require database_path('migrations/2026_10_28_100000_add_roles_and_menu_permissions_to_cms_users.php');
        $migration->up();
        Schema::create('sponsors', function ($table) {
            $table->id(); $table->string('name'); $table->string('branding_name')->nullable(); $table->string('status')->default('publish'); $table->string('package')->default('gold');
        });
        require_once database_path('migrations/2025_03_13_095147_create_sponsors_pic.php');
        require_once database_path('migrations/2026_06_12_161120_create_sponsors_billing.php');
        (new \CreateSponsorsPic)->up();
        (new \CreateSponsorsBilling)->up();
        require_once database_path('migrations/2026_11_04_010000_add_salutation_to_sponsor_contacts.php');
        (new \AddSalutationToSponsorContacts)->up();
        require_once database_path('migrations/2026_11_04_020000_create_sponsors_cc_contacts.php');
        (new \CreateSponsorsCcContacts)->up();
        require_once database_path('migrations/2026_10_30_120000_create_sponsor_logs_table.php');
        (new \CreateSponsorLogsTable)->up();
        DB::table('sponsors')->insert([['id' => 1, 'name' => 'One'], ['id' => 2, 'name' => 'Two']]);
        CmsRole::where('key', 'secretariat_team')->update(['permissions' => ['sponsors']]);
        $user = CmsUser::create(['name' => 'Secretariat', 'email' => 'secretariat@example.test', 'password' => Hash::make('secret'), 'role' => 'secretariat_team', 'is_active' => true]);
        $this->actingAs($user, 'cms');
    }

    public function test_add_and_remove_pic_and_billing_are_logged_and_scoped(): void
    {
        foreach (['pic' => 'sponsors_pic', 'cc' => 'sponsors_cc_contacts', 'billing' => 'sponsors_billing'] as $role => $table) {
            $this->post('/admin/sponsors/contact-directory', ['sponsor_id' => 1, 'role' => $role, 'name' => 'Contact', 'email' => 'contact@example.test'])->assertRedirect();
            $id = DB::table($table)->where('sponsor_id', 1)->value('id');
            $this->assertNotNull($id);
            $this->delete('/admin/sponsors/contact-directory', ['sponsor_id' => 2, 'role' => $role, 'contact_id' => $id])->assertNotFound();
            $this->assertDatabaseHas($table, ['id' => $id, 'sponsor_id' => 1]);
            $this->delete('/admin/sponsors/contact-directory', ['sponsor_id' => 1, 'role' => $role, 'contact_id' => $id])->assertRedirect();
            $this->assertDatabaseMissing($table, ['id' => $id]);
            $subject = ['pic' => 'PIC', 'cc' => 'CC Contact', 'billing' => 'Billing'][$role];
            $this->assertDatabaseHas('sponsor_logs', ['sponsor_id' => 1, 'subject' => $subject, 'action' => 'created']);
            $this->assertDatabaseHas('sponsor_logs', ['sponsor_id' => 1, 'subject' => $subject, 'action' => 'deleted']);
        }
    }

    public function test_removing_a_pic_keeps_the_matching_billing_record(): void
    {
        foreach (['pic', 'billing'] as $role) {
            $this->post('/admin/sponsors/contact-directory', ['sponsor_id' => 1, 'role' => $role, 'name' => 'Same Person', 'email' => 'same@example.test'])->assertRedirect();
        }
        $id = DB::table('sponsors_pic')->value('id');
        $this->delete('/admin/sponsors/contact-directory', ['sponsor_id' => 1, 'role' => 'pic', 'contact_id' => $id])->assertRedirect();
        $this->assertDatabaseMissing('sponsors_pic', ['id' => $id]);
        $this->assertDatabaseHas('sponsors_billing', ['sponsor_id' => 1, 'email' => 'same@example.test']);
    }

    public function test_directory_search_display_and_export_use_branding_name_with_fallback(): void
    {
        Schema::create('sponsors_representative', function ($table) {
            $table->id(); $table->unsignedBigInteger('sponsor_id');
        });
        Schema::create('users', function ($table) {
            $table->id(); $table->string('name'); $table->string('email'); $table->string('status_member');
        });
        Schema::create('profiles', function ($table) {
            $table->id(); $table->unsignedBigInteger('users_id'); $table->string('fullphone')->nullable(); $table->string('job_title')->nullable();
        });
        Schema::create('payment', function ($table) {
            $table->id(); $table->unsignedBigInteger('sponsor_id'); $table->unsignedBigInteger('member_id');
        });
        DB::table('sponsors')->where('id', 1)->update(['branding_name' => 'Brand One']);
        $controller = new \App\Http\Controllers\Admin\SponsorContactDirectoryController;
        $result = $controller->index(\Illuminate\Http\Request::create('/admin/sponsors/contact-directory', 'GET', ['search' => 'Brand One']))->getData()['sponsors'];
        $this->assertSame([1], $result->pluck('id')->all());
        $this->assertSame([1], $controller->index(\Illuminate\Http\Request::create('/admin/sponsors/contact-directory', 'GET', ['search' => 'One']))->getData()['sponsors']->pluck('id')->all());
        $sponsor = $result->first();
        $html = view('admin.sponsor.contact_directory._sponsor_card', ['sponsor' => $sponsor])->render();
        $this->assertStringContainsString('>Brand One</div>', $html);
        $this->assertStringContainsString('data-name="brand one"', $html);
        $sponsor->contactRows = collect([['name' => 'Contact', 'role' => 'pic', 'title' => null, 'email' => null, 'phone' => null, 'instagram' => null, 'linkedin' => null]]);
        $rows = (new \App\Exports\SponsorContactDirectoryExport(collect([$sponsor])))->collection();
        $this->assertSame('Brand One', $rows[0]['Sponsor']);
        $sponsor->branding_name = null;
        $this->assertSame('One', (new \App\Exports\SponsorContactDirectoryExport(collect([$sponsor])))->collection()[0]['Sponsor']);
        $this->assertStringContainsString('>One</div>', view('admin.sponsor.contact_directory._sponsor_card', ['sponsor' => $sponsor])->render());
    }

    public function test_salutation_can_be_added_and_changed_for_pic_and_billing(): void
    {
        Schema::create('sponsors_representative', function ($table) { $table->id(); $table->unsignedBigInteger('sponsor_id'); });
        Schema::create('users', function ($table) { $table->id(); });
        Schema::create('payment', function ($table) { $table->id(); $table->unsignedBigInteger('member_id'); $table->unsignedBigInteger('sponsor_id'); });
        foreach (['pic' => 'sponsors_pic', 'cc' => 'sponsors_cc_contacts', 'billing' => 'sponsors_billing'] as $role => $table) {
            $this->post('/admin/sponsors/contact-directory', ['sponsor_id' => 1, 'role' => $role, 'name' => 'Contact', 'salutation' => 'Ms'])->assertRedirect();
            $id = DB::table($table)->value('id');
            $this->assertDatabaseHas($table, ['id' => $id, 'salutation' => 'Ms']);
            $key = ['pic' => 'pic_ids', 'cc' => 'cc_ids', 'billing' => 'billing_ids'][$role];
            $this->post('/admin/sponsors/contact-directory/update', ['sponsor_id' => 1, $key => [$id], 'name' => 'Contact', 'salutation' => 'Mrs'])->assertRedirect();
            $this->assertDatabaseHas($table, ['id' => $id, 'salutation' => 'Mrs']);
        }
        $this->post('/admin/sponsors/contact-directory', ['sponsor_id' => 1, 'role' => 'pic', 'name' => 'Contact', 'salutation' => 'Dr'])->assertSessionHasErrors('salutation');
    }

    public function test_cc_and_pic_for_same_email_are_visible_and_exportable(): void
    {
        Schema::create('sponsors_representative', function ($table) { $table->id(); $table->unsignedBigInteger('sponsor_id'); });
        Schema::create('users', function ($table) { $table->id(); $table->string('name'); $table->string('email'); $table->string('status_member'); });
        Schema::create('profiles', function ($table) { $table->id(); $table->unsignedBigInteger('users_id'); $table->string('fullphone')->nullable(); $table->string('job_title')->nullable(); });
        Schema::create('payment', function ($table) { $table->id(); $table->unsignedBigInteger('sponsor_id'); $table->unsignedBigInteger('member_id'); });
        foreach (['pic', 'cc'] as $role) {
            $this->post('/admin/sponsors/contact-directory', ['sponsor_id' => 1, 'role' => $role, 'name' => 'Same Person', 'salutation' => 'Ms', 'email' => 'same@example.test'])->assertRedirect();
        }
        $data = (new \App\Http\Controllers\Admin\SponsorContactDirectoryController)->index(\Illuminate\Http\Request::create('/admin/sponsors/contact-directory'))->getData();
        $sponsor = $data['sponsors']->firstWhere('id', 1);
        $this->assertCount(1, $sponsor->contactRows);
        $this->assertSame(1, $data['totalCcs']);
        $html = view('admin.sponsor.contact_directory._contact_row', ['sponsor' => $sponsor, 'contact' => $sponsor->contactRows->first()])->render();
        $this->assertStringContainsString('Primary Contact (PIC)', $html);
        $this->assertStringContainsString('>CC</span>', $html);
        $rows = (new \App\Exports\SponsorContactDirectoryExport(collect([$sponsor]), 'cc'))->collection();
        $this->assertCount(1, $rows);
        $this->assertSame('CC', $rows[0]['Role']);
        $all = (new \App\Exports\SponsorContactDirectoryExport(collect([$sponsor])))->collection();
        $this->assertSame('Primary Contact (PIC), CC', $all[0]['Role']);
    }

    public function test_other_contact_types_and_unauthorized_users_are_rejected(): void
    {
        $this->post('/admin/sponsors/contact-directory', ['sponsor_id' => 1, 'role' => 'representative', 'name' => 'Contact'])->assertSessionHasErrors('role');
        $this->delete('/admin/sponsors/contact-directory', ['sponsor_id' => 1, 'role' => 'member', 'contact_id' => 1])->assertSessionHasErrors('role');
        CmsRole::where('key', 'secretariat_team')->update(['permissions' => '[]']);
        $user = CmsUser::where('email', 'secretariat@example.test')->first();
        $this->actingAs($user, 'cms')->post('/admin/sponsors/contact-directory', ['sponsor_id' => 1, 'role' => 'pic', 'name' => 'Contact'])->assertForbidden();
        $this->delete('/admin/sponsors/contact-directory', ['sponsor_id' => 1, 'role' => 'pic', 'contact_id' => 1])->assertForbidden();
    }

    protected function tearDown(): void
    {
        DB::purge('contact_test');
        parent::tearDown();
    }
}
