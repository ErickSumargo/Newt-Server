<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PrivateLessonTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lessons = [
            [
                'name' => 'SD'
            ],
            [
                'name' => 'SMP'
            ],
            [
                'name' => 'SMA'
            ],
            [
                'name' => 'Inggris'
            ],
            [
                'name' => 'Mandarin'
            ],
            [
                'name' => 'Programming'
            ]
        ];
        foreach ($lessons as $lesson) {
            DB::table('private_lessons')->insert([
                'name' => $lesson['name'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
