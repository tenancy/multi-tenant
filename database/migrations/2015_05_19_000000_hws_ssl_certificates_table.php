<?php

use Hyn\Tenancy\Tenant\DatabaseConnection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class HwsSslCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection(DatabaseConnection::systemConnectionName())->hasTable('ssl_certificates')) {
            Schema::connection(DatabaseConnection::systemConnectionName())->create('ssl_certificates',
                function (Blueprint $table) {
                    $table->bigIncrements('id');
                    // tenant owner
                    $table->bigInteger('tenant_id')->unsigned();

                    // certificate
                    $table->text('certificate');
                    // bundles
                    $table->text('authority_bundle');
                    // key
                    $table->text('key');

                    $table->boolean('wildcard')->default(false);

                    // date when certificate becomes usable as read from certificate
                    $table->timestamp('validates_at')->nullable();
                    // date of expiry as read from certificate
                    $table->timestamp('invalidates_at')->nullable();

                    // timestaps
                    $table->timestamps();
                    $table->softDeletes();

                    // relations
                    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

                    // index
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
        if (Schema::connection(DatabaseConnection::systemConnectionName())->hasTable('ssl_certificates')) {
            Schema::connection(DatabaseConnection::systemConnectionName())->dropIfExists('ssl_certificates');
        }
    }
}
