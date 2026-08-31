<?php

namespace Database\Seeders;

use App\Models\MemberSource;
use Illuminate\Database\Seeder;

class MemberSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MemberSource::updateOrCreate(
            ['category' => 'EP', 'code' => 'MI2026'],
            [
                'name' => 'Mining Indonesia 2026',
                'form_type' => 'individual',
                'is_active' => true,
            ]
        );

        MemberSource::updateOrCreate(
            ['category' => 'REF', 'code' => 'PREMIUM'],
            [
                'name' => 'Premium Referral Link',
                'form_type' => 'corporate',
                'is_active' => true,
            ]
        );
    }
}
