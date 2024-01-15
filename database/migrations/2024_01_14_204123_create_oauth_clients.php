<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->string('client_id', 80);
            $table->string('client_secret', 80)->nullable();
            $table->string('redirect_uri', 2000)->nullable();
            $table->string('grant_types', 80)->nullable();
            $table->string('scope', 4000)->nullable();
            $table->string('user_id', 80)->nullable();

            $table->primary('client_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oauth_clients');
    }
}
