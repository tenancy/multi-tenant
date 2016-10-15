<?php

use Hyn\Tenancy\Tenant\DatabaseConnection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class HwsSslHostnamesDropHostnameId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection(DatabaseConnection::systemConnectionName())->hasColumn('ssl_hostnames', 'hostname_id')) {
            Schema::connection(DatabaseConnection::systemConnectionName())->table('ssl_hostnames', function (Blueprint $table) {
                // domain relation
                $table->dropColumn('hostname_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(DatabaseConnection::systemConnectionName())->table('ssl_hostnames', function (Blueprint $table) {
            // domain relation
            $table->bigInteger('hostname_id')->unsigned();
        });
    }
}
