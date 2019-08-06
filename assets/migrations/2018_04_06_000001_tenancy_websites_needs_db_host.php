<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

use Hyn\Tenancy\Abstracts\AbstractMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TenancyWebsitesNeedsDbHost extends AbstractMigration
{
    protected $system = true;

    public function up()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->string('managed_by_database_connection')
                ->nullable()
                ->comment('References the database connection key in your database.php');
        });
    }

    public function down()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('managed_by_database_connection');
        });
    }
}
