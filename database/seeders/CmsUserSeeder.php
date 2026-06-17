<?php

namespace Database\Seeders;

use App\Models\CmsUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CmsUserSeeder extends Seeder
{
    public function run()
    {
        $admins = [
            ['name' => 'Admininstration', 'email' => 'admin@admin.com', 'password' => 'DMC2026'],
            ['name' => 'Niema', 'email' => 'niema@dmc-cci.com', 'password' => 'DMC2026'],
            ['name' => 'Andira', 'email' => 'andira@djakarta-miningclub.com', 'password' => 'DMC2026'],
            ['name' => 'Dian', 'email' => 'dian@indonesiaminer.com', 'password' => 'DMC2026']
        ];

        foreach ($admins as $admin) {
            CmsUser::firstOrCreate(
                ['email' => $admin['email']],
                [
                    'name'      => $admin['name'],
                    'password'  => Hash::make($admin['password']),
                    'is_active' => true,
                ]
            );
        }
    }
}
