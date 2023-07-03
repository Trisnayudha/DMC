<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::create([
            'name' => 'Erina',
            'email' => 'erina@indonesiaminer.com',
            'password' => bcrypt('12345')
        ]);
        $admin->assignRole('admin');

        $user = User::create([
            'name' => 'Callula',
            'email' => 'calulla@indonesiaminer.com',
            'password' => bcrypt('12345')
        ]);
        $user->assignRole('admin');

        $damun = User::create([
            'name' => 'Damun',
            'email' => 'damun@indonesiaminer.com',
            'password' => bcrypt('12345')
        ]);
        $damun->assignRole('admin');

        $cahyani = User::create([
            'name' => 'Cahyani',
            'email' => 'cahyani@djakarta-miningclub.com',
            'password' => bcrypt('12345')
        ]);
        $cahyani->assignRole('admin');

        // $dumy = User::factory(25)->create();
    }
}
