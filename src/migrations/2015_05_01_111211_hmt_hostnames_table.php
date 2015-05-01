<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HmtHostnamesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('system')->create('hostnames', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned();
            $table->string('hostname');
            $table->bigInteger('website_id')->unsigned()->nullable();
            $table->bigInteger('sub_of')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sub_of')->references('id')->on('hostnames')->onDelete('cascade');
            $table->foreign('website_id')->references('id')->on('websites')->onDelete('set null');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->index('hostname');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::connection('system')->dropIfExists('hostnames');
	}

}
