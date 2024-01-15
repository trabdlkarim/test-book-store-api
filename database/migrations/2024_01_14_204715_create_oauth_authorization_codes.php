<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOauthAuthorizationCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_authorization_codes', function (Blueprint $table) {
            $table->string('authorization_code', 40);
            $table->string('client_id', 80);
            $table->string('redirect_uri', 2000)->nullable();
            
            $table->string('scope', 4000)->nullable();
            $table->string('user_id', 80)->nullable();
            $table->timestamp('expires');
            $table->string('id_token', 1000)->nullable();
            
            $table->primary('authorization_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oauth_authorization_codes');
    }
}
