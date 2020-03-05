<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ChallengeLessonTableSeeder extends Seeder
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
                'name' => 'Matematika'
            ],
            [
                'name' => 'Fisika'
            ]
        ];
        foreach ($packages as $package) {
            DB::table('challenge_lessons')->insert([
                'name' => $package['name'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
