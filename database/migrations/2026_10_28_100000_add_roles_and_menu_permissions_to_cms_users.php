<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cms_roles', function (Blueprint $table) {
            $table->string('key', 32)->primary();
            $table->json('permissions');
            $table->timestamps();
        });
        foreach (['event_team', 'marketing_team', 'secretariat_team', 'finance_team', 'superadmin'] as $role) {
            DB::table('cms_roles')->insert([
                'key' => $role, 'permissions' => '[]', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        Schema::table('cms_users', function (Blueprint $table) {
            $table->string('role', 32)->default('event_team')->index();
        });
        // Preserve the access existing CMS accounts had before roles were introduced.
        DB::table('cms_users')->update(['role' => 'superadmin']);
    }

    public function down(): void
    {
        Schema::table('cms_users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        Schema::dropIfExists('cms_roles');
    }
};
