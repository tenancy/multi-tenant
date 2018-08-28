<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Hyn\Tenancy\Abstracts\AbstractMigration;

class TenancyWebsitesNeedsDbHost extends AbstractMigration
{
    protected $system = true;

    public function up()
    {
        Schema::connection('system')->table('websites', function (Blueprint $table) {
            $table->string('managed_by_database_connection')
                ->nullable()
                ->comment('References the database connection key in your database.php');
        });
    }

    public function down()
    {
        Schema::connection('system')->table('websites', function (Blueprint $table) {
            $table->dropColumn('managed_by_database_connection');
        });
    }
}
