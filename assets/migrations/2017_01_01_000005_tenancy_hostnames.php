<?php

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
            $table->bigInteger('customer_id')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('website_id')->references('id')->on('websites')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hostnames');
    }
}
