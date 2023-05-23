<?php

namespace Sulytics\Saml2\Commands;

use Sulytics\Saml2\Helpers\ConsoleHelper;
use Sulytics\Saml2\Models\IdentityProvider;
use Sulytics\Saml2\Models\ServiceProvider;
use Sulytics\Saml2\Repositories\IdentityProviderRepository;
use Illuminate\Console\Command;

class CreateIdentityProvider extends Command
{
    use RendersIdentityProvider;
    /**
     * The name and signature of the console command.
     * E.g.: php artisan saml2:createIdentityProvider --spId=1 --key=beta-sulytics-tool --x509cert=MIIC8DCCAdigAwIBAgIQGCzAKK1ZN5RP2JjZ/gp7ezANBgkqhkiG9w0BAQsFADA0MTIwMAYDVQQDEylNaWNyb3NvZnQgQXp1cmUgRmVkZXJhdGVkIFNTTyBDZXJ0aWZpY2F0ZTAeFw0yMjA4MTkxMzIwMTlaFw0yNTA4MTkxMzIwMTlaMDQxMjAwBgNVBAMTKU1pY3Jvc29mdCBBenVyZSBGZWRlcmF0ZWQgU1NPIENlcnRpZmljYXRlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqAbtNHkh678mWFtptvmA+ZRlDuB7zve6lF9engwP109vTKoVovBbVy7pqTLvh4jEKoR+Y6V/Slne1xBKBJcY2KNH+6sCT8X24FnJauAMFOb29S1ftfXOSO9ukCWXvtBhlG8xKEQPXfIQxuxe0tK78AMgUWLiZh9bRzQEE4Nr76qBsndjHENu3dJ9ng7xHJwwTNSAKgndZUdj1f2gEFb2AQjvxETLNcMaaTXrCz1DOhS0fj1kDV+GgJmiptZvfOog0InO6f3Y+/6TwuM8AObFxcvQ0kfabMNp7OFYqFPEmCXRgrljNObcCqomgfel4PU4kpW6V371LzagcJuQ/mJ/cQIDAQABMA0GCSqGSIb3DQEBCwUAA4IBAQBIrJRJ3MKs0YqjOOOSGPvBcN1cTj3QREa2ulprsVorF9pFh1qmk0PQQc00z/ha/LuFBXiJpU1RhH+TPZ2/9Kn8hJrFdW2BashRvG2QCLjwddVGtkwzlLsogoN+7TIKhRUQde+zTGktinbWoD9sbLMYg9o5ZcKHMopyksMQUG1F1POEYvw4dXXzYOmG9vWPa6klSjlpv0ZH3GzOo33gY6q4RzoGmlhdkTb/vwKyp1oCJehMLmYYRZ64ZQ7B2CWEl/gLl8rfke2Wa7eCN2Onqe4WrwDVtLTeamKhCQ4+WIO+AMt6DNQtc8Uscim5NePDOe0HHDIq3RPVcKZgWq6NZ5Dz --entityId=https://sts.windows.net/11fcca5e-e46b-4ae7-b94e-ffe78b73e61c/ --loginUrl=https://login.microsoftonline.com/11fcca5e-e46b-4ae7-b94e-ffe78b73e61c/saml2 --logoutUrl=https://login.microsoftonline.com/11fcca5e-e46b-4ae7-b94e-ffe78b73e61c/saml2
     *
     * @var string
     */
    protected $signature = 'saml2:createIdentityProvider
                            { --spId= : A SP Id }
                            { --k|key= : A IdP custom key }
                            { --entityId= : IdP Issuer URL }
                            { --loginUrl= : IdP Sign on URL }
                            { --loginBinding= : IdP Sign on binding }
                            { --logoutUrl= : IdP Logout URL }
                            { --logoutBinding= : IdP Logout binding }
                            { --relayStateUrl= : Redirection URL after successful login }
                            { --nameIdFormat= : Name ID Format ("persistent" by default) }
                            { --x509cert= : x509 certificate (base64) }
                            { --metadata= : A custom metadata }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a identity provider entity (relying identity provider)';

    public function handle(IdentityProviderRepository $identityProviderRepository)
    {
        $spId = $this->option('spId');
        $this->info($spId);
        if (empty($spId) || !ServiceProvider::whereId($spId)->exists()) {
            $this->error('SP ID must be passed as an option --spId and must exist in saml2_service_providers table.');
            return;
        }

        if (!$key = $this->option('key')) {
            $this->error('Entity ID must be passed as an option --key');
            return;
        }

        if (!$entityId = $this->option('entityId')) {
            $this->error('Entity ID must be passed as an option --entityId');
            return;
        }

        if (!$loginUrl = $this->option('loginUrl')) {
            $this->error('Login URL must be passed as an option --loginUrl');
            return;
        }

        if (!$logoutUrl = $this->option('logoutUrl')) {
            $this->error('Logout URL must be passed as an option --logoutUrl');
            return;
        }

        if (!$x509cert = $this->option('x509cert')) {
            $this->error('x509 certificate (base64) must be passed as an option --x509cert');
            return;
        }

        $key = $this->option('key');
        $metadata = ConsoleHelper::stringToArray($this->option('metadata'));

        if($key && ($identityProvider = $identityProviderRepository->findByKey($key))) {
            $this->renderIdentityProvider($identityProvider, 'Already found identity provider(s) using this key');
            $this->error(
                'Cannot create a identity provider because the key is already being associated with other identity provider(s).'
                . PHP_EOL . 'Firstly, delete identity provider(s) or try to create with another with another key.'
            );

            return;
        }

        $identityProviderAttributes = [
            'service_provider_id' => $spId,
            'idp_key' => $key,
            'idp_entity_id' => $entityId,
            'idp_login_url' => $loginUrl,
            'idp_logout_url' => $logoutUrl,
            'idp_x509_cert' => $x509cert,
            'idp_relay_state_url' => $this->option('relayStateUrl'),
            'idp_metadata' => $metadata,
        ];

        if(!empty($this->option('loginBinding'))) {
            $identityProviderAttributes['idp_login_binding'] = $this->option('loginBinding');
        }

        if(!empty($this->option('logoutBinding'))) {
            $identityProviderAttributes['idp_logout_binding'] = $this->option('logoutBinding');
        }

        $identityProvider = new IdentityProvider($identityProviderAttributes);

        if(!$identityProvider->save()) {
            $this->error('Identity provider cannot be saved.');
            return;
        }

        $this->info("The identity provider #{$identityProvider->id} ({$identityProvider->idp_key}) was successfully created.");

        $this->renderIdentityProvidersCredentials($identityProvider);

        $this->output->newLine();
    }
}