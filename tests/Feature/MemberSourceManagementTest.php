<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\MemberSourceController;
use App\Models\MemberSource;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MemberSourceManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'member_source_test', 'database.connections.member_source_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        require_once database_path('migrations/2026_08_31_165823_create_member_sources_table.php');
        (new \CreateMemberSourcesTable)->up();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('source')->nullable();
        });
    }

    private function input(array $values = []): Request
    {
        return Request::create('/admin/member-sources', 'POST', array_merge([
            'category' => 'ep', 'code' => 'mi2026', 'name' => 'Mining Indonesia 2026',
            'form_type' => 'individual', 'is_active' => '1',
        ], $values));
    }

    public function test_create_normalizes_keys_and_rejects_duplicate_links(): void
    {
        $controller = new MemberSourceController;
        $controller->store($this->input());
        $this->assertSame('EP', MemberSource::first()->category);
        $this->assertSame('MI2026', MemberSource::first()->code);
        $this->assertTrue(MemberSource::first()->is_active);
        $this->expectException(ValidationException::class);
        $controller->store($this->input());
    }

    public function test_registration_counts_and_history_guards_ignore_letter_case(): void
    {
        $controller = new MemberSourceController;
        $controller->store($this->input());
        $source = MemberSource::first();
        DB::table('users')->insert([['source' => 'ep/mi2026'], ['source' => 'EP/MI2026']]);
        $this->assertSame(2, $controller->index()->getData()['registrations']['ep/mi2026']);
        $controller->destroy($source->id);
        $this->assertSame(1, MemberSource::count());
        $controller->update($this->input(['name' => 'Renamed Event']), $source->id);
        $this->assertSame('Renamed Event', $source->fresh()->name);
        $this->expectException(ValidationException::class);
        $controller->update($this->input(['code' => 'NEWCODE']), $source->id);
    }

    public function test_toggle_changes_public_link_availability_and_unused_sources_can_be_deleted(): void
    {
        $controller = new MemberSourceController;
        $controller->store($this->input());
        $source = MemberSource::first();
        $public = new \App\Http\Controllers\API\MemberSourceController;
        $request = Request::create('/api/member-source/check', 'GET', ['category' => 'EP', 'code' => 'MI2026']);
        $this->assertSame(200, $public->check($request)->getData()->status);
        $controller->toggleActive($source->id);
        $this->assertFalse($source->fresh()->is_active);
        $this->assertSame(404, $public->check($request)->getData()->status);
        $controller->toggleActive($source->id);
        $this->assertSame(200, $public->check($request)->getData()->status);
        $controller->destroy($source->id);
        $this->assertSame(0, MemberSource::count());
    }

    public function test_invalid_source_codes_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        (new MemberSourceController)->store($this->input(['code' => 'BAD/CODE']));
    }

    protected function tearDown(): void
    {
        DB::purge('member_source_test');
        parent::tearDown();
    }
}
