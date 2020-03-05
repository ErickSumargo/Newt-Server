<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PackageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $packages = [
            [
                'transaction' => 20000,
                'days' => 7
            ],
            [
                'transaction' => 50000,
                'days' => 30
            ],
            [
                'transaction' => 125000,
                'days' => 90
            ],
        ];
        foreach ($packages as $package) {
            DB::table('packages')->insert([
                'transaction' => $package['transaction'],
                'days' => $package['days'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
