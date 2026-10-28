<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Do not overwrite the permissions that administrators have already configured.
        DB::table('cms_roles')->insertOrIgnore([
            'key' => 'member_team',
            'permissions' => json_encode(config('cms_access.team_defaults.member_team', [])),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed defaults only for teams that have not been configured yet.
        foreach (config('cms_access.team_defaults', []) as $key => $permissions) {
            $role = DB::table('cms_roles')->where('key', $key)->first();
            if ($role && json_decode($role->permissions, true) === []) {
                DB::table('cms_roles')->where('key', $key)->update([
                    'permissions' => json_encode($permissions), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (DB::table('cms_users')->where('role', 'member_team')->exists()) {
            throw new \RuntimeException('Reassign Member Team users before rolling back this role.');
        }
        DB::table('cms_roles')->where('key', 'member_team')->delete();
    }
};
