<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LessonTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lessons = ['Matematika', 'Fisika'];
        foreach ($lessons as $lesson) {
            DB::table('lessons')->insert([
                'name' => $lesson,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}