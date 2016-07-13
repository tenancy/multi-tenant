<?php

use Hyn\Tenancy\Tenant\DatabaseConnection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HmtHostnamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection(DatabaseConnection::systemConnectionName())->hasTable('hostnames')) {
            Schema::connection(DatabaseConnection::systemConnectionName())->create(
                'hostnames',
                function (Blueprint $table) {
                    $table->bigIncrements('id');
                    // tenant owner
                    $table->bigInteger('tenant_id')->unsigned();
                    // hostname
                    $table->string('hostname');
                    // related to website x
                    $table->bigInteger('website_id')->unsigned()->nullable();
                    // related to certificate
                    $table->bigInteger('ssl_certificate_id')->unsigned()->nullable();
                    // subdomain of another hostname
                    $table->bigInteger('sub_of')->unsigned()->nullable();
                    // redirect to a different hostname
                    $table->bigInteger('redirect_to')->unsigned()->nullable();
                    // redirect standard to https if certificate available
                    $table->boolean('prefer_https')->default(false);

                    // timestaps
                    $table->timestamps();
                    $table->softDeletes();

                    // relations
                    $table->foreign('redirect_to')->references('id')->on('hostnames')->onDelete('set null');
                    $table->foreign('sub_of')->references('id')->on('hostnames')->onDelete('cascade');
                    $table->foreign('website_id')->references('id')->on('websites')->onDelete('set null');
                    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

                    // index
                    $table->index('hostname');
                }
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::connection(DatabaseConnection::systemConnectionName())->hasTable('hostnames')) {
            Schema::connection(DatabaseConnection::systemConnectionName())->dropIfExists('hostnames');
        }
    }
}
