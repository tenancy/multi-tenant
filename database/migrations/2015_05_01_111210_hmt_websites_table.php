<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Laraflock\MultiTenant\Tenant\DatabaseConnection;

class HmtWebsitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(DatabaseConnection::systemConnectionName())->create('websites', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tenant_id')->unsigned();
            $table->string('identifier');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->index('identifier');
            $table->unique('identifier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(DatabaseConnection::systemConnectionName())->dropIfExists('websites');
    }
}
