<?php

use Hyn\Tenancy\Abstracts\AbstractMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TenancyWebsites extends AbstractMigration
{
    protected $system = true;

    public function up()
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('uuid');
            $table->bigInteger('customer_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }

    public function down()
    {
        Schema::dropIfExists('websites');
    }
}
