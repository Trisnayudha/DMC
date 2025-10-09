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
        $user = User::where('email', 'niema@dmc-cci.com')->first();

        if ($user) {
            $user->assignRole('admin');
            echo "Role admin berhasil ditambahkan ke {$user->name}";
        } else {
            echo "User tidak ditemukan";
        }


        // $user = User::create([
        //     'name' => 'Callula',
        //     'email' => 'calulla@indonesiaminer.com',
        //     'password' => bcrypt('12345')
        // ]);
        // $user->assignRole('admin');

        // $damun = User::create([
        //     'name' => 'Damun',
        //     'email' => 'damun@indonesiaminer.com',
        //     'password' => bcrypt('12345')
        // ]);
        // $damun->assignRole('admin');

        // $cahyani = User::create([
        //     'name' => 'Cahyani',
        //     'email' => 'cahyani@djakarta-miningclub.com',
        //     'password' => bcrypt('12345')
        // ]);
        // $cahyani->assignRole('admin');

        // $dumy = User::factory(25)->create();
    }
}
