<?php

use Hyn\Tenancy\Tenant\DatabaseConnection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HmtTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection(DatabaseConnection::systemConnectionName())->hasTable('tenants')) {
            Schema::connection(DatabaseConnection::systemConnectionName())->create(
                'tenants',
                function (Blueprint $table) {
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
        if (Schema::connection(DatabaseConnection::systemConnectionName())->hasTable('tenants')) {
            Schema::connection(DatabaseConnection::systemConnectionName())->dropIfExists('tenants');
        }
    }
}
