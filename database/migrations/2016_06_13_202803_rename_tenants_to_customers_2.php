<?php

use Hyn\Tenancy\Tenant\DatabaseConnection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTenantsToCustomers2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection(DatabaseConnection::systemConnectionName())->hasColumn('websites', 'tenant_id')) {
            Schema::connection(DatabaseConnection::systemConnectionName())
                ->table('websites', function (Blueprint $table) {
                    $table->renameColumn('tenant_id', 'customer_id');
                    $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                });
            Schema::connection(DatabaseConnection::systemConnectionName())
                ->table('hostnames', function (Blueprint $table) {
                    $table->renameColumn('tenant_id', 'customer_id');
                    $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
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
        Schema::connection(DatabaseConnection::systemConnectionName())
            ->table('websites', function (Blueprint $table) {
                $table->dropForeign('websites_customer_id_foreign');
                $table->renameColumn('customer_id', 'tenant_id');
            });
        Schema::connection(DatabaseConnection::systemConnectionName())
            ->table('hostnames', function (Blueprint $table) {
                $table->dropForeign('hostnames_customer_id_foreign');
                $table->renameColumn('customer_id', 'tenant_id');
            });
    }
}
