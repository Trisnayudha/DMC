<?php

namespace Database\Seeders;

use App\Models\News\News;
use Database\Factories\News\NewsFactory;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class NewsSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        for ($i = 1; $i <= 50; $i++) {

            // insert data ke table pegawai menggunakan Faker
            News::insert([
                'title' => $faker->sentence(mt_rand(2, 5)),
                'highlight' => $faker->randomElement(['Yes', 'No']),
                'desc' => $faker->paragraph(),
                'image' => $faker->imageUrl(500, 500, 'animals', true),
                'slug' => $faker->slug(),
                'views' => mt_rand(1, 50),
                'location' => $faker->state,
                'share' => mt_rand(1, 50),
                'date_news' => $faker->dateTimeBetween('-1 week'),
                'reference_link' => 'https://google.com',
                'created_at' => $faker->dateTimeBetween('-1 week'),
            ]);
        }
    }
}
