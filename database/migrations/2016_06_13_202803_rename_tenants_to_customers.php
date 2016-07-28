<?php

use Hyn\Tenancy\Tenant\DatabaseConnection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTenantsToCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection(DatabaseConnection::systemConnectionName())->hasTable('tenants')) {
            Schema::connection(DatabaseConnection::systemConnectionName())
                ->rename('tenants', 'customers');
            Schema::connection(DatabaseConnection::systemConnectionName())
                ->table('websites', function (Blueprint $table) {
                    $table->dropForeign('websites_tenant_id_foreign');
                    $table->renameColumn('tenant_id', 'customer_id');
                    $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                });
            Schema::connection(DatabaseConnection::systemConnectionName())
                ->table('hostnames', function (Blueprint $table) {
                    $table->dropForeign('hostnames_tenant_id_foreign');
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
        if (Schema::connection(DatabaseConnection::systemConnectionName())->hasTable('customers')) {
            Schema::connection(DatabaseConnection::systemConnectionName())
                ->rename('customers', 'tenants');

            Schema::connection(DatabaseConnection::systemConnectionName())
                ->table('websites', function (Blueprint $table) {
                    $table->dropForeign('websites_tenant_id_foreign');
                    $table->renameColumn('customer_id', 'tenant_id');
                    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                });
            Schema::connection(DatabaseConnection::systemConnectionName())
                ->table('hostnames', function (Blueprint $table) {
                    $table->dropForeign('hostnames_tenant_id_foreign');
                    $table->renameColumn('customer_id', 'tenant_id');
                    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                });
        }
    }
}
