<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Helpers\AES;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admins = [
            [
                'code' => 'ES3665',
                'name' => 'Erick Sumargo',
                'phone' => '082245032143',
                'password' => '$2y$10$xyST8f2Q3CXmTWffszmnNean8yoaGBGIs1HkHRmW2ltWzQfrJT7ae'
            ],
            [
                'code' => 'HGL2091',
                'name' => 'Hilary Gunarsa Lubis',
                'phone' => '085371117293',
                'password' => '$2y$10$f5wj3dGIzpPP3QKePMTfA.kFXNJRtz5.7wyEGWS7Y4JlrrzcW1F.W'
            ]
        ];
        foreach ($admins as $admin) {
            DB::table('admins')->insert([
                'code' => $admin['code'],
                'name' => $admin['name'],
                'phone' => $admin['phone'],
                'password' => $admin['password'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}