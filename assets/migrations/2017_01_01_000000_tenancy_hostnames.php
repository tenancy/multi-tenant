<?php

use Hyn\Tenancy\Abstracts\AbstractMigration;
use Illuminate\Database\Schema\Blueprint;

class TenancyHostnames extends AbstractMigration
{
    public function up()
    {
        if (!$this->systemConnection()->hasTable('hostnames')) {
            $this->systemConnection()->create('hostnames', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('fqdn');
                $table->string('redirect_to')->nullable();
                $table->boolean('force_https')->default(false);
                $table->timestamp('under_maintenance_since')->nullable();
                $table->bigInteger('website_id')->unsigned()->nullable();
                $table->bigInteger('customer_id')->unsigned();

                $table->timestamps();
                $table->softDeletes();

                $table->foreign('website_id')->references('id')->on('websites')->onDelete('set null');
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        $this->systemConnection()->dropIfExists('hostnames');
    }
}
