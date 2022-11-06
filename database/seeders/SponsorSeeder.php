<?php

namespace Database\Seeders;

use App\Models\Sponsors\Sponsor;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Sponsor::insert([
            [
                'name' => 'Hexagon Mining',
            ],
            [
                'name' => 'PT ExxonMobil Lubricants Indonesia',
            ],
            [
                'name' => 'PT Kemenangan'
            ],
            [
                'name' => 'MMD Mining Machinery Indonesia'
            ],
            [
                'name' => 'RPM Global'
            ],
            [
                'name' => 'Tekenomiks Indonesia'
            ],
            [
                'name' => 'PT. ROAD TECHNOLOGY INDONESIA'
            ],
            [
                'name' => 'WEIR MINERALS'
            ],
            [
                'name' => 'TRANSKON RENT'
            ],
            [
                'name' => 'McLanahan'
            ],
            [
                'name' => 'Suprabakti Mandiri'
            ],
            [
                'name' => 'WEIR MINERALS'
            ],
            [
                'name' => 'NEWCREST'
            ],
            [
                'name' => 'PT. ABEL'
            ],
            [
                'name' => 'HHP Law Firm'
            ],
            [
                'name' => 'ARMILA RAKO'
            ],
            [
                'name' => 'Pwc'
            ],
            [
                'name' => 'Schenck Process'
            ],
            [
                'name' => 'Coates'
            ],
            [
                'name' => 'S&P Global'
            ],
            [
                'name' => 'AON'
            ],
            [
                'name' => 'FluidTek'
            ],
            [
                'name' => 'Hexindo'
            ],
            [
                'name' => 'ProChile'
            ],
            [
                'name' => 'Cranserco'
            ],
            [
                'name' => 'Sandvik'
            ],
            [
                'name' => 'ORICA'
            ],
            [
                'name' => 'PT. JSG'
            ],
            [
                'name' => 'Bukit Asam'
            ],
            [
                'name' => 'Aden'
            ],
        ]);
    }
}
