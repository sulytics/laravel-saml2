<?php

use OneLogin\Saml2\Constants;

return $settings = array(

    /*
   |--------------------------------------------------------------------------
   | Use built-in routes
   |--------------------------------------------------------------------------
   |
   | If 'useRoutes' is set to true, the package defines five new routes for reach entry in idpKey:
   |
   | Method | URI                                | Name
   | -------|------------------------------------|------------------
   | POST   | {routesPrefix}/{idpKey}/acs       | saml_acs
   | GET    | {routesPrefix}/{idpKey}/login     | saml_login
   | GET    | {routesPrefix}/{idpKey}/logout    | saml_logout
   | GET    | {routesPrefix}/{idpKey}/metadata  | saml_metadata
   | GET    | {routesPrefix}/{idpKey}/sls       | saml_sls
   |
   */

    'useRoutes' => true,

    /*
    |--------------------------------------------------------------------------
    | Built-in routes prefix
    |--------------------------------------------------------------------------
    |
    | Here you may define the prefix for built-in routes.
    | Optional, , leave empty if you want the defined routes to be top level, i.e. "/{idpKey}/*"
    |
    */

    'routesPrefix' => '/saml2',

    /*
    |--------------------------------------------------------------------------
    | Middle groups to use for the SAML routes
    |--------------------------------------------------------------------------
    |
    | Note, Laravel 5.2 requires a group which includes StartSession
    |
    */

    'routesMiddleware' => ['saml'],

    /*
    |--------------------------------------------------------------------------
    | Signature validation
    |--------------------------------------------------------------------------
    |
    | Set to true if you want to use parameters from $_SERVER to validate the signature.
    |
    */
    'retrieveParametersFromServer' => false,

    /*
    |--------------------------------------------------------------------------
    | Login redirection URL.
    |--------------------------------------------------------------------------
    |
    | The redirection URL after successful login.
    |
    */
    'loginRoute' => env('SAML2_AFTER_LOGIN_URL', '/'),

    /*
    |--------------------------------------------------------------------------
    | Logout redirection URL.
    |--------------------------------------------------------------------------
    |
    | The redirection URL after successful logout.
    |
    */
    'logoutRoute' => env('SAML2_AFTER_LOGOUT_URL', '/'),



    /*
    |--------------------------------------------------------------------------
    | Login error redirection URL.
    |--------------------------------------------------------------------------
    |
    | The redirection URL after login failing.
    |
    */
    'errorRoute' => env('SAML2_AFTER_ERROR_URL', '/'),


    /*
    |--------------------------------------------------------------------------
    | Strict mode.
    |--------------------------------------------------------------------------
    |
    | If 'strict' is True, then the PHP Toolkit will reject unsigned
    | or unencrypted messages if it expects them signed or encrypted
    | Also will reject the messages if not strictly follow the SAML
    | standard: Destination, NameId, Conditions... are validated too.
    |
    */

    'strict' => true,

    /*
    |--------------------------------------------------------------------------
    | Debug mode.
    |--------------------------------------------------------------------------
    |
    | When enabled, errors must be printed.
    |
    */

    'debug' => env('SAML2_DEBUG', env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Whether to use `X-Forwarded-*` headers to determine port/domain/protocol.
    |--------------------------------------------------------------------------
    |
    | If 'proxyVars' is True, then the Saml lib will trust proxy headers
    | e.g X-Forwarded-Proto / HTTP_X_FORWARDED_PROTO. This is useful if
    | your application is running behind a load balancer which terminates SSL.
    |
    */

    'proxyVars' => false,

    /**
     * (Optional) Which class implements the route functions.
     * If commented out, defaults to this lib's controller (Aacotroneo\Saml2\Http\Controllers\Saml2Controller).
     * If you need to extend Saml2Controller (e.g. to override the `login()` function to pass
     * a `$returnTo` argument), this value allows you to pass your own controller, and have
     * it used in the routes definition.
     */
     //'saml2_controller' => \App\Http\Controllers\Auth\RumbaSamlController::class,

    /*
    |--------------------------------------------------------------------------
    | OneLogin security settings.
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    'security' => [

        /*
        |--------------------------------------------------------------------------
        | NameId encryption
        |--------------------------------------------------------------------------
        |
        | Indicates that the nameID of the <samlp:logoutRequest> sent by this SP
        | will be encrypted.
        |
        */

        'nameIdEncrypted' => false,

        /*
        |--------------------------------------------------------------------------
        | AuthnRequest signage
        |--------------------------------------------------------------------------
        |
        | Indicates whether the <samlp:AuthnRequest> messages sent by
        | this SP will be signed. The Metadata of the SP will offer this info
        |
        */

        'authnRequestsSigned' => true,

        /*
        |--------------------------------------------------------------------------
        | Logout request signage
        |--------------------------------------------------------------------------
        |
        | Indicates whether the <samlp:logoutRequest> messages sent by this SP
        | will be signed.
        |
        */

        'logoutRequestSigned' => true,

        /*
        |--------------------------------------------------------------------------
        | Logout response signage
        |--------------------------------------------------------------------------
        |
        | Indicates whether the <samlp:logoutResponse> messages sent by this SP
        | will be signed.
        |
        */

        'logoutResponseSigned' => true,

        /*
        |--------------------------------------------------------------------------
        | Whether need to sign metadata.
        |--------------------------------------------------------------------------
        |
        | The possible values:
        | - false
        | - true (use certs)
        | - array:
        |   ```
        |   [
        |       'keyFileName' => 'metadata.key',
        |       'certFileName' => 'metadata.crt'
        |   ]
        |   ```
        |
        */

        'signMetadata' => false,

        /*
        |--------------------------------------------------------------------------
        | Requirement to sign messages.
        |--------------------------------------------------------------------------
        |
        | Indicates a requirement for the <samlp:Response>, <samlp:LogoutRequest> and
        | <samlp:LogoutResponse> elements received by this SP to be signed.
        |
        */

        'wantMessagesSigned' => false,

        /*
        |--------------------------------------------------------------------------
        | Requirement to sign assertion elements.
        |--------------------------------------------------------------------------
        |
        | Indicates a requirement for the <saml:Assertion> elements received by
        | this SP to be signed.
        |
        */

        'wantAssertionsSigned' => false,

        /*
        |--------------------------------------------------------------------------
        | Requirement to encrypt NameID.
        |--------------------------------------------------------------------------
        |
        | Indicates a requirement for the NameID received by this SP to be encrypted.
        |
        */

        'wantNameIdEncrypted' => false,

        /*
        |--------------------------------------------------------------------------
        | Authentication context.
        |--------------------------------------------------------------------------
        |
        | Set to false and no AuthContext will be sent in the AuthNRequest,
        |
        | Set true or don't present this parameter and you will get an
        | AuthContext 'exact' 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport'
        |
        | Set an array with the possible auth context values:
        | ['urn:oasis:names:tc:SAML:2.0:ac:classes:Password', 'urn:oasis:names:tc:SAML:2.0:ac:classes:X509']
        |
        */

//        'requestedAuthnContext' => true,
        'requestedAuthnContext' => [
            Constants::AC_UNSPECIFIED,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact information.
    |--------------------------------------------------------------------------
    |
    | It is recommended to supply a technical and support contacts.
    |
    */

    'contactPerson' => [
        'technical' => [
            'givenName' => env('SAML2_CONTACT_TECHNICAL_NAME', 'name'),
            'emailAddress' => env('SAML2_CONTACT_TECHNICAL_EMAIL', 'no@reply.com')
        ],
        'support' => [
            'givenName' => env('SAML2_CONTACT_SUPPORT_NAME', 'Support'),
            'emailAddress' => env('SAML2_CONTACT_SUPPORT_EMAIL', 'no@reply.com')
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization information.
    |--------------------------------------------------------------------------
    |
    | The info in en_US lang is recommended, add more if required.
    |
    */

    'organization' => [
        'en-US' => [
            'name' => env('SAML2_ORGANIZATION_NAME', 'Name'),
            'displayname' => env('SAML2_ORGANIZATION_NAME', 'Display Name'),
            'url' => env('SAML2_ORGANIZATION_URL', 'http://url')
        ],
    ],
);
