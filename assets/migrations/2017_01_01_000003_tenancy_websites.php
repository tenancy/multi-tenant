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

class TenancyWebsites extends AbstractMigration
{
    protected $system = true;

    public function up()
    {
        Schema::connection('system')->create('websites', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('uuid');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::connection('system')->dropIfExists('websites');
    }
}
