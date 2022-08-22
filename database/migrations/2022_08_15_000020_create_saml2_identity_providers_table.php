<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSaml2IdentityProvidersTable extends Migration
{

    public function up()
    {
        Schema::create('saml2_identity_providers', function (Blueprint $table) {
            $table->id('id');
            $table->foreignIdFor(\Freegee\LaravelSaml2\Models\ServiceProvider::class);
            $table->string('idp_key')->unique();
            $table->string('idp_entity_id');
            $table->string('idp_login_url');
            $table->string('idp_login_binding')->default(\OneLogin\Saml2\Constants::BINDING_HTTP_POST);
            $table->string('idp_logout_url');
            $table->string('idp_logout_binding')->default('');
            $table->string('idp_relay_state_url')->nullable();
            $table->text('idp_x509_cert');
            $table->json('idp_metadata');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('saml2_identity_providers');
    }
}