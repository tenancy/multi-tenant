<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class HmtTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('hyn')->create('tenants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customer_no')->nullable();
            $table->string('name');
            $table->string('email');
            $table->boolean('administrator')->default(false);

            $table->bigInteger('reseller_id')->unsigned()->nullable();
            $table->bigInteger('referer_id')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_no', 'name']);

            $table->foreign('reseller_id')->references('id')->on('tenants')->onDelete('set null');
            $table->foreign('referer_id')->references('id')->on('tenants')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('hyn')->dropIfExists('tenants');
    }
}
