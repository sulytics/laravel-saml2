<?php

use Freegee\LaravelSaml2\Http\Controllers\Saml2Controller;
use Illuminate\Support\Facades\Route;

Route::name('saml.')
    ->middleware(array_merge(config('saml2_settings.routesMiddleware'), ['saml2.resolveIdentityProvider']))
    ->prefix(config('saml2_settings.routesPrefix'))
    ->group(function () {
        $saml2_controller = config('saml2_settings.saml2_controller', Saml2Controller::class);

        Route::get('{idpKey}/logout', [$saml2_controller, 'logout'])
            ->name('logout');

        Route::get('{idpKey}/login', [$saml2_controller, 'login'])
            ->name('login');

        Route::get('{idpKey}/metadata', [$saml2_controller, 'metadata'])
            ->name('metadata');

        Route::post('{idpKey}/acs', [$saml2_controller, 'acs'])
            ->name('acs');

        Route::get('{idpKey}/sls', [$saml2_controller, 'sls'])
            ->name('sls');
    });
