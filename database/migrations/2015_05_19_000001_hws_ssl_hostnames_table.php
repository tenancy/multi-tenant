<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Hyn\MultiTenant\Tenant\DatabaseConnection;

class HwsSslHostnamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(DatabaseConnection::systemConnectionName())->create('ssl_hostnames', function (Blueprint $table) {
            $table->bigIncrements('id');
            // certificate id
            $table->bigInteger('ssl_certificate_id')->unsigned();
            // domain relation
            $table->bigInteger('hostname_id')->unsigned();

            // certificate
            $table->string('hostname');

            // timestaps
            $table->timestamps();
            $table->softDeletes();

            // index
            $table->index('hostname');
            $table->index('hostname_id');
            $table->index('ssl_certificate_id');
            // relations
            $table->foreign('ssl_certificate_id')->references('id')->on('ssl_certificates')->onDelete('cascade');
            // the set null constraint does not work on mariadb; let's just ignore that for now
            // @TODO but fix support for set null in the future somehow
//            $table->foreign('hostname_id')->references('id')->on('hostnames')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(DatabaseConnection::systemConnectionName())->dropIfExists('ssl_hostnames');
    }
}
