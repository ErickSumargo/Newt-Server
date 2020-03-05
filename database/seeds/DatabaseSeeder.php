<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminTableSeeder::class);
        $this->call(LessonTableSeeder::class);
        $this->call(PackageTableSeeder::class);
        $this->call(ProviderTableSeeder::class);
        $this->call(BankTableSeeder::class);
        $this->call(PrivateLessonTableSeeder::class);
        $this->call(ChallengeLessonTableSeeder::class);
    }
}