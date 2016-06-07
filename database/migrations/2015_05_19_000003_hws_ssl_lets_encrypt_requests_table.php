<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Hyn\MultiTenant\Tenant\DatabaseConnection;

class HwsSslLetsEncryptRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(DatabaseConnection::systemConnectionName())->create('ssl_lets_encrypt_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            // domain relation
            $table->bigInteger('hostname_id')->unsigned();

            // let's encrypt token
            $table->string('token');

            // the solver of the challenge
            $table->string('solved_by')->nullable();

            // timestaps
            $table->timestamps();
            $table->timestamp('solved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();

            // index
            $table->index('hostname_id');
            // relations
            $table->foreign('hostname_id')->references('id')->on('hostnames')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(DatabaseConnection::systemConnectionName())->dropIfExists('ssl_lets_encrypt_requests');
    }
}
