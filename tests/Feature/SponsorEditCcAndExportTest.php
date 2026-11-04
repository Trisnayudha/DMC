<?php

namespace Tests\Feature;

use App\Exports\SponsorExport;
use App\Models\CmsUser;
use App\Models\Sponsors\SponsorBilling;
use App\Models\Sponsors\SponsorCcContact;
use App\Models\Sponsors\SponsorPic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class SponsorEditCcAndExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sponsor_edit_test', 'database.connections.sponsor_edit_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        require_once database_path('migrations/2026_06_17_120000_create_cms_users_table.php');
        (new \CreateCmsUsersTable)->up();
        $migration = require database_path('migrations/2026_10_28_100000_add_roles_and_menu_permissions_to_cms_users.php');
        $migration->up();
        Schema::create('sponsors', function ($table) {
            $table->id();
            foreach (['name', 'branding_name', 'email', 'company_website', 'address', 'description', 'package', 'status', 'founded', 'location_office', 'employees', 'company_category', 'instagram', 'facebook', 'linkedin', 'video', 'contract_start', 'contract_end', 'slug', 'image', 'office_number'] as $column) {
                $table->string($column)->nullable();
            }
            $table->integer('sort')->default(0); $table->timestamps();
        });
        Schema::create('company_categories', function ($table) { $table->id(); $table->string('name'); $table->boolean('is_active')->default(true); $table->integer('sort_order')->default(0); });
        require_once database_path('migrations/2025_03_13_095147_create_sponsors_pic.php');
        require_once database_path('migrations/2026_06_12_161120_create_sponsors_billing.php');
        require_once database_path('migrations/2026_11_04_010000_add_salutation_to_sponsor_contacts.php');
        require_once database_path('migrations/2026_11_04_020000_create_sponsors_cc_contacts.php');
        (new \CreateSponsorsPic)->up(); (new \CreateSponsorsBilling)->up(); (new \AddSalutationToSponsorContacts)->up(); (new \CreateSponsorsCcContacts)->up();
        DB::table('sponsors')->insert([
            ['id' => 1, 'name' => 'Company One', 'email' => 'one@example.test', 'status' => 'publish', 'package' => 'gold', 'slug' => 'company-one'],
            ['id' => 2, 'name' => 'Company Two', 'email' => 'two@example.test', 'status' => 'publish', 'package' => 'silver', 'slug' => 'company-two'],
        ]);
        $user = CmsUser::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => Hash::make('secret'), 'is_active' => true, 'role' => 'superadmin']);
        $this->actingAs($user, 'cms');
    }

    private function sponsorInput(array $extra = []): array
    {
        return array_merge(['name' => 'Company One', 'email' => 'one@example.test', 'package' => 'gold', 'status' => 'publish'], $extra);
    }

    public function test_edit_form_and_update_support_cc_add_edit_remove_and_old_callers(): void
    {
        $keep = SponsorCcContact::create(['sponsor_id' => 1, 'name' => 'First', 'salutation' => 'Ms', 'email' => 'first@example.test']);
        $remove = SponsorCcContact::create(['sponsor_id' => 1, 'name' => 'Remove']);
        $this->get('/admin/sponsors/1/edit')->assertOk()->assertSee('Sponsor CC Contact Information')->assertSee('first@example.test');
        $this->patch('/admin/sponsors/1', $this->sponsorInput(['cc_contacts_present' => 1, 'cc_contacts' => [
            ['id' => $keep->id, 'name' => 'Updated', 'salutation' => 'Mrs', 'email' => 'updated@example.test'],
            ['id' => '', 'name' => 'New One', 'title' => 'Manager', 'phone' => '08123'],
            ['id' => '', 'name' => 'New Two', 'email' => 'new@example.test'],
        ]]))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('sponsors_cc_contacts', ['id' => $keep->id, 'name' => 'Updated', 'salutation' => 'Mrs']);
        $this->assertDatabaseMissing('sponsors_cc_contacts', ['id' => $remove->id]);
        $this->assertDatabaseHas('sponsors_cc_contacts', ['sponsor_id' => 1, 'name' => 'New One', 'phone' => '08123']);
        $this->assertDatabaseHas('sponsors_cc_contacts', ['sponsor_id' => 1, 'name' => 'New Two']);
        $this->patch('/admin/sponsors/1', $this->sponsorInput())->assertSessionHasNoErrors();
        $this->assertSame(3, SponsorCcContact::where('sponsor_id', 1)->count());
        $this->patch('/admin/sponsors/1', $this->sponsorInput(['cc_contacts_present' => 1]))->assertSessionHasNoErrors();
        $this->assertSame(0, SponsorCcContact::where('sponsor_id', 1)->count());
    }

    public function test_cannot_modify_another_sponsors_cc_contact(): void
    {
        $other = SponsorCcContact::create(['sponsor_id' => 2, 'name' => 'Other']);
        $this->patch('/admin/sponsors/1', $this->sponsorInput(['cc_contacts_present' => 1, 'cc_contacts' => [
            ['id' => $other->id, 'name' => 'Changed'],
        ]]))->assertSessionHasErrors('cc_contacts.0.id');
        $this->assertDatabaseHas('sponsors_cc_contacts', ['id' => $other->id, 'sponsor_id' => 2, 'name' => 'Other']);
    }

    public function test_export_includes_all_cc_and_billing_contacts_when_counts_differ(): void
    {
        DB::connection()->getPdo()->sqliteCreateFunction('FIELD', function ($value, ...$values) { $index = array_search($value, $values, true); return $index === false ? 0 : $index + 1; });
        SponsorPic::create(['sponsor_id' => 1, 'name' => 'PIC One']);
        SponsorCcContact::create(['sponsor_id' => 1, 'name' => 'CC One', 'salutation' => 'Ms', 'email' => 'cc1@example.test']);
        SponsorCcContact::create(['sponsor_id' => 1, 'name' => 'CC Two', 'email' => 'cc2@example.test']);
        SponsorBilling::create(['sponsor_id' => 1, 'name' => 'Billing One', 'phone' => '081234']);
        $raw = Excel::raw(new SponsorExport, ExcelFormat::XLSX);
        $file = tempnam(sys_get_temp_dir(), 'sponsor_export_');
        try {
            file_put_contents($file, $raw);
            $sheet = IOFactory::load($file)->getActiveSheet();
            $this->assertSame('Name CC', $sheet->getCell('N1')->getValue());
            $this->assertSame('Name Billing', $sheet->getCell('R1')->getValue());
            $this->assertSame('PIC One', $sheet->getCell('D2')->getValue());
            $this->assertSame('Ms CC One', $sheet->getCell('N2')->getValue());
            $this->assertSame('CC Two', $sheet->getCell('N3')->getValue());
            $this->assertSame('Billing One', $sheet->getCell('R2')->getValue());
            $this->assertSame('081234', $sheet->getCell('U2')->getValue());
            $this->assertContains('A2:A3', $sheet->getMergeCells());
            $this->assertSame('Company Two', $sheet->getCell('C4')->getValue());
        } finally {
            @unlink($file);
        }
    }

    protected function tearDown(): void
    {
        DB::purge('sponsor_edit_test');
        parent::tearDown();
    }
}
