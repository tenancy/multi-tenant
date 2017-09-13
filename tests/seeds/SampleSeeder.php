<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SampleSeeder extends Seeder
{
    public function run()
    {
        DB::table('samples')->insert([
            'name' => Str::random(5)
        ]);
    }
}