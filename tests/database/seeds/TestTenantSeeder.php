<?php

namespace Hyn\Tests\Seeds;

use DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tenant_migration_test')->insert(
            [
                'some_field' => Str::random(10)
            ]
        );
    }
}
