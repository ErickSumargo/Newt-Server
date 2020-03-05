<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BankTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $banks = [
            [
                'bank' => 'BCA',
                'owner' => 'Erick Sumargo',
                'account' => '8430202406'
            ],
            [
                'bank' => 'BNI',
                'owner' => 'Hilary Gunarsa Lubis',
                'account' => '1410199386'
            ]
        ];
        foreach ($banks as $bank) {
            DB::table('banks')->insert([
                'bank' => $bank['bank'],
                'owner' => $bank['owner'],
                'account' => $bank['account'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
