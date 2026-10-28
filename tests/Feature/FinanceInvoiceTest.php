<?php

namespace Tests\Feature;

use App\Models\CmsUser;
use App\Models\Payments\FinanceInvoice;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinanceInvoiceTest extends TestCase
{
    private $callbackToken;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'finance_invoice_test', 'database.connections.finance_invoice_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        require_once database_path('migrations/2026_06_17_120000_create_cms_users_table.php');
        (new \CreateCmsUsersTable)->up();
        (require database_path('migrations/2026_10_28_100000_add_roles_and_menu_permissions_to_cms_users.php'))->up();
        (require database_path('migrations/2026_10_28_110000_add_member_team_cms_role.php'))->up();
        (require database_path('migrations/2026_10_28_120000_create_finance_invoices_table.php'))->up();
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->text('address')->nullable(); $table->string('email')->nullable(); $table->string('office_number')->nullable();
        });
        Schema::create('sponsors_billing', function (Blueprint $table) {
            $table->id(); $table->integer('sponsor_id'); $table->string('name')->nullable(); $table->string('title')->nullable(); $table->string('email')->nullable(); $table->string('phone')->nullable(); $table->timestamps();
        });
        Schema::create('sponsors_pic', function (Blueprint $table) {
            $table->id(); $table->integer('sponsor_id'); $table->string('name')->nullable(); $table->string('title')->nullable(); $table->string('email')->nullable(); $table->string('phone')->nullable(); $table->timestamps();
        });
        Schema::create('sponsor_renewals', function (Blueprint $table) {
            $table->id(); $table->integer('sponsor_id'); $table->integer('renewal_year')->nullable(); $table->string('contract_start')->nullable(); $table->string('contract_end')->nullable();
            $table->string('package')->nullable(); $table->string('renewal_status')->nullable(); $table->string('renewal_type')->nullable();
            $table->decimal('amount_usd')->nullable(); $table->decimal('amount_idr', 15, 2)->nullable(); $table->boolean('is_current')->default(0); $table->text('notes')->nullable();
            $table->string('quotation_number')->nullable(); $table->date('quotation_date')->nullable(); $table->date('invoice_date')->nullable();
            $table->string('invoice_number')->nullable(); $table->date('paid_date')->nullable(); $table->timestamps();
        });
        Schema::create('events', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug')->nullable(); $table->date('start_date')->nullable(); $table->timestamps();
        });
        Schema::create('events_tickets', function (Blueprint $table) {
            $table->id(); $table->integer('events_id')->nullable(); $table->string('title')->nullable(); $table->decimal('price_rupiah', 15, 2)->nullable(); $table->decimal('price_dollar')->nullable(); $table->timestamps();
        });

        Mail::fake();
        $this->callbackToken = 'test-callback-token';
        foreach (['XENDIT_ISPROD' => '', 'XENDIT_CALLBACK_TOKEN_TEST' => $this->callbackToken, 'XENDIT_SECRET_KEY_TEST' => 'xnd_test_fake'] as $key => $value) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
        Http::fake(['api.xendit.co/*' => Http::sequence()
            ->push($this->vaResponse('8860812345678901'))
            ->push($this->vaResponse('8860899999999999'))]);

        $finance = CmsUser::create(['name' => 'Finance', 'email' => 'finance@example.test', 'password' => Hash::make('secret123'), 'role' => 'finance_team', 'is_active' => true]);
        $this->actingAs($finance, 'cms');
    }

    private function vaResponse(string $number): array
    {
        return [
            'payment_request_id' => 'pr-' . $number, 'reference_id' => 'ignored', 'currency' => 'IDR', 'country' => 'ID', 'request_amount' => 1,
            'actions' => [['type' => 'PRESENT_TO_CUSTOMER', 'descriptor' => 'VIRTUAL_ACCOUNT_NUMBER', 'value' => $number]],
        ];
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'custom', 'bill_to_name' => 'Budi', 'bill_to_company' => 'PT Tambang Maju', 'bill_to_email' => 'budi@example.test',
            'items' => [['description' => 'Iklan majalah', 'qty' => 2, 'unit_price' => 1500000]],
            'vat_percent' => 11, 'issued_date' => now()->toDateString(), 'due_date' => now()->addDays(7)->toDateString(),
        ], $overrides);
    }

    private function webhook(string $reference, string $status = 'SUCCEEDED', $amount = 3330000)
    {
        return $this->postJson('/api/xendit/payment-callback', [
            'event' => 'payment.capture',
            'data'  => ['reference_id' => $reference, 'status' => $status, 'channel_code' => 'MANDIRI_VIRTUAL_ACCOUNT', 'request_amount' => $amount],
        ], ['x-callback-token' => $this->callbackToken]);
    }

    public function test_finance_creates_custom_invoice_with_mandiri_va(): void
    {
        $response = $this->post('/admin/finance-invoices', $this->payload(['send_email' => '1']));

        $invoice = FinanceInvoice::first();
        $response->assertRedirect(route('admin.finance_invoices.show', $invoice->id));
        $this->assertSame('DMC-INV-' . date('Y') . '-0001', $invoice->invoice_number);
        $this->assertSame(3000000, (int) $invoice->subtotal);
        $this->assertSame(330000, (int) $invoice->vat_amount);
        $this->assertSame(3330000, (int) $invoice->total_amount);
        $this->assertSame('8860812345678901', $invoice->va_number);
        $this->assertSame('FINV-' . $invoice->id . '-1', $invoice->reference_id);
        $this->assertNotNull($invoice->email_sent_at);
        Http::assertSent(function ($request) {
            return $request['channel_code'] === 'MANDIRI_VIRTUAL_ACCOUNT' && $request['request_amount'] === 3330000
                && $request['channel_properties']['display_name'] === 'PT Tambang Maju';
        });

        $this->get('/admin/finance-invoices/' . $invoice->id)->assertOk()->assertSee('8860812345678901');
        $this->get('/admin/finance-invoices')->assertOk()->assertSee($invoice->invoice_number);
        $this->get('/admin/finance-invoices/' . $invoice->id . '/pdf')->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get('/admin/finance-invoices/create')->assertOk()->assertSee('DMC-INV-' . date('Y') . '-0002');
    }

    public function test_sponsor_renewal_invoice_paid_by_webhook_completes_the_contract(): void
    {
        DB::table('sponsors')->insert(['id' => 1, 'name' => 'Berlian Cranserco', 'email' => 'info@berlian.test']);
        DB::table('sponsor_renewals')->insert(['id' => 9, 'sponsor_id' => 1, 'renewal_year' => 2026, 'contract_start' => '2026-11', 'contract_end' => '2027-10',
            'package' => 'gold', 'amount_idr' => 3000000, 'is_current' => 1]);

        $this->post('/admin/finance-invoices', $this->payload(['type' => 'sponsor_renewal', 'sponsor_id' => 1, 'sponsor_renewal_id' => 9]));
        $invoice = FinanceInvoice::first();

        $this->webhook($invoice->reference_id)->assertOk()->assertJson(['api_status' => 1]);
        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame(3330000, (int) $invoice->paid_amount);

        $renewal = DB::table('sponsor_renewals')->find(9);
        $this->assertSame(now()->timezone('Asia/Jakarta')->toDateString(), substr($renewal->paid_date, 0, 10));
        $this->assertSame($invoice->invoice_number, $renewal->invoice_number);

        // Webhook dobel tidak memproses ulang.
        $this->webhook($invoice->reference_id)->assertOk()->assertJson(['api_message' => 'Already paid']);
    }

    public function test_expired_va_can_be_regenerated_and_old_reference_is_ignored(): void
    {
        $this->post('/admin/finance-invoices', $this->payload());
        $invoice = FinanceInvoice::first();
        $oldReference = $invoice->reference_id;

        $this->webhook($oldReference, 'EXPIRED')->assertOk();
        $this->assertSame('expired', $invoice->fresh()->status);

        $this->post('/admin/finance-invoices/' . $invoice->id . '/regenerate-va', ['due_date' => now()->addDays(3)->toDateString()])->assertRedirect();
        $invoice->refresh();
        $this->assertSame('pending', $invoice->status);
        $this->assertSame('FINV-' . $invoice->id . '-2', $invoice->reference_id);
        $this->assertSame('8860899999999999', $invoice->va_number);

        $this->webhook($oldReference)->assertOk()->assertJson(['api_status' => 0]);
        $this->assertSame('pending', $invoice->fresh()->status);
    }

    public function test_validation_and_access(): void
    {
        $this->post('/admin/finance-invoices', $this->payload(['items' => [['description' => 'Kecil', 'qty' => 1, 'unit_price' => 5000]], 'vat_percent' => 0]))
            ->assertSessionHasErrors('items');
        $this->post('/admin/finance-invoices', $this->payload(['type' => 'sponsor_renewal']))->assertSessionHasErrors('sponsor_id');
        $this->assertSame(0, FinanceInvoice::count());
        Http::assertNothingSent();

        $event = CmsUser::create(['name' => 'Event', 'email' => 'event@example.test', 'password' => Hash::make('secret123'), 'role' => 'event_team', 'is_active' => true]);
        $this->actingAs($event, 'cms')->get('/admin/finance-invoices')->assertForbidden();
    }

    protected function tearDown(): void
    {
        DB::purge('finance_invoice_test');
        parent::tearDown();
    }
}
