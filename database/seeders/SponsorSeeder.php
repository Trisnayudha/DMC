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
                'address' => 'Office Tower Grand Sudirman, Lt. 10 Unit 11 & 01A, Jl. Jenderal Sudirman No. 7, Klandasan Ilir, Balikpapan 76113, Indonesia',
                'company_website' => 'www.hexagonmining.com',
                'office_number' => '625428808629'
            ],
            [
                'name' => 'PT ExxonMobil Lubricants Indonesia',
                'address' => 'Wisma GKBI, Jl. Jendral Sudirman, Bendungan Hilir, 28, Tanah Abang, Jakarta Pusat 10210',
                'company_website' => 'www.exxonmobil.co.id/en-id',
                'office_number' => '622157986224'
            ],
            [
                'name' => 'PT Kemenangan',
                'address' => 'Gunung Sahari Raya No. 75, Jakarta, Indonesia 10340',
                'company_website' => 'www.kemenangan.co.id/',
                'office_number' => '62214207083'
            ],
            [
                'name' => 'MMD Mining Machinery Indonesia',
                'address' => 'The Manhattan Square, Mid Tower 7th floor suite H, Jl. TB Simatupang Kav.1S, Cilandak Timur, South Jakarta 12560',
                'company_website' => 'www.mmdsizers.com',
                'office_number' => '622129049871'
            ],
            [
                'name' => 'RPM Global',
                'address' => 'Nissi Bintaro Campus 2nd Floor, JL. Tegal Rotan No. 78,Bintaro, Banten 15413',
                'company_website' => 'www.rpmglobal.com',
                'office_number' => '622122213058'
            ],
            [
                'name' => 'Techenomics Indonesia',
                'address' => 'Jl. Jend Sudirman No. 58 RT 29 Kel. Damai Bahagia, Balikpapan - Kalimantan Timur 76114',
                'company_website' => 'www.techenomics.net',
                'office_number' => '622129049871'
            ],
            [
                'name' => 'PT. ROAD TECHNOLOGY INDONESIA',
                'address' => 'Gedung Wahid 27, 2nd Floor,  Jl. KH. Wahid Hasyim No. 27 , Jakarta Pusat 10340',
                'company_website' => '',
                'office_number' => '62213101399'
            ],
            [
                'name' => 'WEIR MINERALS',
                'address' => 'JL. Mulawarman No. 020 RT 020, Manggar, Balikpapan - East Kalimantan 76116',
                'company_website' => 'www.global.weir',
                'office_number' => '6221542746098'
            ],
            [
                'name' => 'TRANSKON RENT',
                'address' => 'Jl. Mulawarman No 21, RT 23, Manggar, Balikpapan Timur, Kalimantan Timur 76116, Indonesia',
                'company_website' => 'www.transkon-rent.com',
                'office_number' => '6221542770401'
            ],
            [
                'name' => 'McLanahan',
                'address' => '27 Kalinya Close, Cameron Park, New South Wales',
                'company_website' => 'www.mclanahan.com',
                'office_number' => '61249248228'
            ],
            [
                'name' => 'Suprabakti Mandiri',
                'address' => 'Jl. Danau Sunter Utara Blok A No. 9 Sunter, Jakarta Utara - 14350',
                'company_website' => 'www.suprabakti.co.id',
                'office_number' => '622165833666'
            ],
            [
                'name' => 'Resource Equipment Indonesia, PT',
                'address' => 'Jl. Mulawarman KM 17,5 RT.23 Manggar, Balikpapan Timur 76116, Indonesia',
                'company_website' => 'www.rel.co.id',
                'office_number' => '+62811534335'
            ],
            [
                'name' => 'Dassault Systemes Singapore Pte. Ltd',
                'address' => '9 Tampines Grande #06-13, Asia Green, Singapore 528735',
                'company_website' => 'www.3ds.com',
                'office_number' => '+6565117988'
            ],
            [
                'name' => 'Michelin Indonesia',
                'address' => 'Pondok Indah Office Tower 2, 12th Floor Suite 1202, Jl. Sultan Iskandar Muda Kav. V-TA – Jakarta Selatan, Indonesia',
                'company_website' => 'www.michelin.co.id',
                'office_number' => '+6281119189985'
            ],
            [
                'name' => 'Xylem Water Solutions Indonesia',
                'address' => 'Tempo Scan Tower 32/F, Jl. H.R. Rasuna Said Kav. 3-4, Jakarta 12950, Indonesia',
                'company_website' => 'www.xylem.com',
                'office_number' => '+628158989393'
            ],
            [
                'name' => 'NEWCREST',
                'address' => 'The Manhattan Square, Mid Tower, 3rd Floor, Jl. TB Simatupang Kav. 1-S, Cilandak Timur, Ps. Minggu, Jakarta Selatan,12560',
                'company_website' => 'https://www.newcrest.com/',
                'office_number' => '+622129049920'
            ],
            [
                'name' => 'ABEL Grup Indonesia',
                'address' => 'EightyEight Office Tower A 38th Floor Unit A-D, Jl. Casablanca Raya Kav.88, Menteng Dalam-Tebet, Jakarta 12870',
                'company_website' => 'https://www.abelgrup.com/',
                'office_number' => '+62215200550'
            ],
            [
                'name' => 'Hadiputranto, Hadinoto & Partners',
                'address' => 'Pacific Century Place, Level 35, Sudirman Central Business District Lot 10 , Jl. Jendral Sudirman Kav.52-53 , Jakarta 12190 ',
                'company_website' => 'https://www.hhp.co.id/en/',
                'office_number' => '+622129608888'
            ],
            [
                'name' => 'ARMILA RAKO',
                'address' => 'Suite 12-C, 12th Floor Lippo Kuningan,  Jl.  HR Rasuna Said Kav. 12,  Jakarta 12920',
                'company_website' => 'https://armilarako.com/',
                'office_number' => '+62215212901'
            ],
            [
                'name' => 'Pwc Indonesia',
                'address' => 'WTC 3, Jl. Jend. Sudirman Kav. 29-31 Jakarta 1290',
                'company_website' => 'https://www.pwc.com/id/en/industry-sectors/energy--utilities---mining.html',
                'office_number' => '+622150992901'
            ],
            [
                'name' => 'Schenck Process',
                'address' => 'Wisma 46 – Kota BNI Tower 32nd Floor Unit 1 Jalan Jenderal Sudirman Kav.1, Jakarta 10220',
                'company_website' => 'https://www.schenckprocess.com/',
                'office_number' => '+622129022335'
            ],
            [
                'name' => 'Coates Hire Indonesia',
                'address' => 'Jln. Mulawarman No 116 RT 32 Kel. Sepinggan Raya Balikpapan, East Kalimantan 76115',
                'company_website' => 'http://ptcoates.com/',
                'office_number' => '+6221542760174'
            ],
            [
                'name' => 'S&P Global Market Intelligence',
                'address' => '12 Marina Boulevard, #23-01, Marina Bay Financial Centre Tower 3, Singapore 018982',
                'company_website' => 'https://www.spglobal.com/en/',
                'office_number' => '+6590048962'
            ],
            [
                'name' => 'AON Indonesia',
                'address' => 'The Energy 27th Floor, SCBD Lot 11A, Jln. Jend Sudirman Kav. 52-53, Jakarta 12190',
                'company_website' => 'https://www.aon.com/home/index',
                'office_number' => '+622129858500'
            ],
            [
                'name' => 'Fluida Teknologi Indonesia',
                'address' => 'Alamanda Tower 25th Floor, Jl. T.B. Simatupang Kav. 23-24, Jakarta 12430',
                'company_website' => 'https://fluidteknologi.com/',
                'office_number' => '+622129657978'
            ],
            [
                'name' => 'Hexindo Adiperkasa',
                'address' => 'Kawasan Industri Pulo Gadung, Jl. Pulo Kambing 2 Kav I-11 No. 33, Jakarta 13930',
                'company_website' => 'https://www.hexindo-tbk.co.id/',
                'office_number' => '+62214611688'
            ],
            [
                'name' => 'Berlian Cranserco Indonesia',
                'address' => 'Gedung Cibis 8, 3rd Floor, Jalan TB. Simatupang No. 2, Jakarta, Indonesia, 12560',
                'company_website' => 'https://www.cranserco.com/',
                'office_number' => '+62217802269'
            ],
            [
                'name' => 'Sandvik Mining and Rock Technology',
                'address' => 'CIBIS Nine Building 6th Floor, Jl. TB Simatupang No 2, Jakarta, Indonesia, 12560',
                'company_website' => 'https://www.rocktechnology.sandvik/',
                'office_number' => '+6221542763066'
            ],
            [
                'name' => 'Schneider Electric Indonesia',
                'address' => 'CIBIS Nine Park, Jl. TB Simatupang No 2, Jakarta, Indonesia, 12560',
                'company_website' => 'https://www.se.com/ww/en/',
                'office_number' => '+62217504406'
            ],
            [
                'name' => 'PT. Beraeu Veritas Indonesia',
                'address' => 'Wisma 76 Building, 21st Floor. Jl. Letnan Jendral S. Parman Kav. 76, Jakarta, Indonesia, 11410',
                'company_website' => 'https://group.bureauveritas.com/',
                'office_number' => '+622153666861'
            ],
            [
                'name' => 'Orica Mining Services',
                'address' => 'Pd. Indah Office Tower, Jl. Sultan Iskandar Muda No.29, Jakarta, Indonesia, 12310',
                'company_website' => 'https://www.orica.com/',
                'office_number' => '+622127650123'
            ],
            [
                'name' => 'JSG International, PT',
                'address' => 'Jl. Ciputat Raya No.1C Kebayoran Lama Selatan Kebayoran Lama, Jakarta Selatan 12240',
                'company_website' => 'http://www.ptjsg.co.id/',
                'office_number' => '+62217239511'
            ],
            [
                'name' => 'PT. Bukit Asam',
                'address' => 'Menara Kadin Indonesia, Jl. H. R. Rasuna Said, RT.1/RW.2, Kuningan, Kuningan Tim., Kecamatan Setiabudi, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12950',
                'company_website' => 'https://www.ptba.co.id/',
                'office_number' => '+628119202060'
            ],
            [
                'name' => 'ADEN Indonesia',
                'address' => 'Haery 1 Building, 2nd Floor, Suite 001, Kemang Selatan, Jakarta',
                'company_website' => 'www.adenservices.com',
                'office_number' => '+6281212537933'
            ],
            [
                'name' => 'Jacon / Jtech Jasa Pertambangan, PT',
                'address' => 'JI Pratama Indah No 5 Gundang Asri A2, Pelemwatu,Kec,Menganti Kab,Gresik, Jawa Timur 61174',
                'company_website' => 'www.jacon.id',
                'office_number' => '+628115414185'
            ],
            [
                'name' => 'Britmindo Group',
                'address' => 'Graha STR, 4th Floor, Suite 407 - 408, Jalan Ampera Raya No. 11, Jakarta Selatan, Indonesia, 12550',
                'company_website' => 'www.britmindo.com',
                'office_number' => '+622178849999'
            ],
        ]);
    }
}
