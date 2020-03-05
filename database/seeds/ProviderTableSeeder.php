<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ProviderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $providers = [
            [
                'provider' => 'Telkomsel',
                'phone' => '081397357677'
            ],
            [
                'provider' => 'XL',
                'phone' => '087811998994'
            ],
            [
                'provider' => '3',
                'phone' => '082245032143'
            ],
            [
                'provider' => 'Indosat',
                'phone' => '085762670993'
            ],
        ];
        foreach ($providers as $provider) {
            DB::table('providers')->insert([
                'provider' => $provider['provider'],
                'phone' => $provider['phone'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
