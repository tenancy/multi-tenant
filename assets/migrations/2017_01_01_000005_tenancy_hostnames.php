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

class TenancyHostnames extends AbstractMigration
{
    protected $system = true;

    public function up()
    {
        Schema::create('hostnames', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('fqdn')->unique();
            $table->string('redirect_to')->nullable();
            $table->boolean('force_https')->default(false);
            $table->timestamp('under_maintenance_since')->nullable();
            $table->bigInteger('website_id')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('website_id')->references('id')->on('websites')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hostnames');
    }
}
