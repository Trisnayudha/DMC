<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(PermissionTableSeeder::class);
        // $this->call(RoleSeeder::class);
        // $this->call(UserSeeder::class);
        // $this->call(SponsorSeeder::class);
        // $this->call(NewsSeeder::class);
        // $this->call(FaqSeeder::class);
        $this->call(BenefitSeeder::class);
        $this->call(PackageBenefitSeeder::class);
        $this->call(SponsorBenefitUsageSeeder::class);
    }
}
