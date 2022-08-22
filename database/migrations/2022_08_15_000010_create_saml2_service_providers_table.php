<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSaml2ServiceProvidersTable extends Migration
{

    public function up()
    {
        Schema::create('saml2_service_providers', function (Blueprint $table) {
            $table->id('id');
            $table->string('sp_name_id_format')->default(\OneLogin\Saml2\Constants::NAMEID_EMAIL_ADDRESS);
            $table->text('sp_x509_cert');
            $table->text('sp_x509_cert_new');
            $table->text('sp_private_key');
            $table->string('sp_entity_id')->default('');
            $table->string('sp_assertion_consumer_url')->default('');
            $table->string('sp_assertion_consumer_binding')->default('');
            $table->string('sp_single_logout_service_url')->default('');
            $table->string('sp_single_logout_service_binding')->default(\OneLogin\Saml2\Constants::BINDING_HTTP_POST);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('saml2_service_providers');
    }
}