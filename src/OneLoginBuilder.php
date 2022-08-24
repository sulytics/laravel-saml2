<?php

namespace Freegee\LaravelSaml2;

use Freegee\LaravelSaml2\Models\IdentityProvider;
use Freegee\LaravelSaml2\Models\ServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Constants;
use OneLogin\Saml2\Utils as OneLoginUtils;

class OneLoginBuilder
{
    /**
     * @var Container
     */
    protected Container $app;

    /**
     * The resolved identity provider
     *
     * @var \Freegee\LaravelSaml2\Models\IdentityProvider
     */
    protected IdentityProvider $identityProvider;

    /**
     * The service Provider of the resolved identity provider
     *
     * @var \Freegee\LaravelSaml2\Models\ServiceProvider
     */
    protected ServiceProvider $serviceProvider;

    /**
     * OneLoginBuilder constructor.
     *
     * @param  \Illuminate\Container\Container  $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Set a identity provider.
     *
     * @param IdentityProvider $identityProvider
     *
     * @return $this
     */
    public function withIdentityProvider(IdentityProvider $identityProvider): static
    {
        $this->identityProvider = $identityProvider;
        $this->serviceProvider = $identityProvider->serviceProvider;
        return $this;
    }

    /**
     * Bootstrap the OneLogin toolkit.
     *
     * @return void
     */
    public function bootstrap(): void
    {

        if ($this->app['config']->get('saml2_settings.proxyVars', false)) {
            OneLoginUtils::setProxyVars(true);
        }

        $this->app->singleton(OneLoginAuth::class, function ($app) {

            // Get the genera, security and contact settings of the settings file.
            $settings = $app['config']['saml2_settings'];

            $this->setServiceProviderSettings($settings);
            $this->setIdentityProviderSettings($settings);

            return new OneLoginAuth($settings);
        });

        $this->app->singleton(Auth::class, function ($app) {
            return new Auth(App::make(OneLoginAuth::class), $this->identityProvider);
        });
    }

    /**
     * @param  array  $settings
     * @return void
     * @throws \Exception
     */
    protected function setServiceProviderSettings(array &$settings): void
    {
        if(!isset($settings['sp'])) {
            $settings['sp'] = [];
        }

        $settings['sp']['entityId'] = $this->serviceProvider->sp_entity_id ?: $settings['sp']['entityId'] ?? URL::route('saml2_metadata', ['idpKey' => $this->identityProvider->idp_key]);
        $settings['sp']['assertionConsumerService']  = $settings['sp']['assertionConsumerService'] ?? [];
        $settings['sp']['assertionConsumerService']['url'] = $this->serviceProvider->sp_assertion_consumer_url ?: $settings['sp']['assertionConsumerService']['url'] ?? URL::route('saml2_acs', ['idpKey' => $this->identityProvider->idp_key]);
        $settings['sp']['assertionConsumerService']['binding'] = $this->serviceProvider->sp_assertion_consumer_binding ?: $settings['sp']['assertionConsumerService']['binding'] ?? Constants::BINDING_HTTP_POST;
        $settings['sp']['singleLogoutService'] = $settings['sp']['singleLogoutService'] ?? [];
        $settings['sp']['singleLogoutService']['url'] = $this->serviceProvider->sp_single_logout_service_url ?: $settings['sp']['singleLogoutService']['url'] ?? URL::route('saml2_sls', ['idpKey' => $this->identityProvider->idp_key]);
        $settings['sp']['singleLogoutService']['binding'] = $this->serviceProvider->sp_single_logout_service_binding;
        $settings['sp']['NameIDFormat'] = $this->serviceProvider->sp_single_logout_service_binding;
        $settings['sp']['x509cert'] = $this->isFileUri($this->serviceProvider->sp_x509_cert) ? $this->extractCertFromFile($this->serviceProvider->sp_x509_cert) : $this->serviceProvider->sp_x509_cert;
        $settings['sp']['x509certNew'] = $this->isFileUri($this->serviceProvider->sp_x509_cert_new) ? $this->extractCertFromFile($this->serviceProvider->sp_x509_cert_new) : $this->serviceProvider->sp_x509_cert_new;
        $settings['sp']['privateKey'] = $this->isFileUri($this->serviceProvider->sp_private_key) ? $this->extractPkeyFromFile($this->serviceProvider->sp_private_key) : $this->serviceProvider->sp_private_key;

    }

    /**
     * @param  array  $settings
     * @return void
     * @throws \Exception
     */
    protected function setIdentityProviderSettings(array &$settings): void
    {
        if(!isset($settings['idp'])) {
            $settings['idp'] = [];
        }

        $settings['idp']['entityId'] = $this->identityProvider->idp_entity_id;
        $settings['idp']['singleSignOnService'] = [
            'url' => $this->identityProvider->idp_login_url,
            'binding' => $this->identityProvider->idp_login_binding,
        ];
        $settings['idp']['singleLogoutService'] = [
            'url' => $this->identityProvider->idp_logout_url,
            'binding' => $this->identityProvider->idp_logout_binding,
        ];
        $settings['idp']['x509certNew'] = $this->isFileUri($this->identityProvider->idp_x509_cert) ? $this->extractCertFromFile($this->identityProvider->idp_x509_cert) : $this->identityProvider->idp_x509_cert;
    }


    protected function isFileUri(?string $value): bool
    {
        return !empty($value) && Str::startsWith('file://', $value);
    }


    /**
     * @param $path
     * @return mixed|string
     * @throws \Exception
     */
    protected function extractCertFromFile($path)
    {
        $opensslCertificate = openssl_x509_read(file_get_contents($path));
        if (empty($opensslCertificate)) {
            throw new \Exception('Could not read X509 certificate-file at path \'' . $path . '\'');
        }
        openssl_x509_export($opensslCertificate, $cert);
        return $this->extractOpensslString($cert, 'CERTIFICATE');
    }


    /**
     * @param $path
     * @return mixed|string
     * @throws \Exception
     */
    protected function extractPkeyFromFile($path) {
        $opensslCertificate = openssl_get_privatekey($path);
        if (empty($opensslCertificate)) {
            throw new \Exception('Could not read private key-file at path \'' . $path . '\'');
        }
        openssl_pkey_export($opensslCertificate, $pkey);
        return $this->extractOpensslString($pkey, 'PRIVATE KEY');
    }

    /**
     * @param $keyString
     * @param $delimiter
     * @return mixed|string
     */
    protected  function extractOpensslString($keyString, $delimiter): mixed
    {
        $keyString = str_replace(["\r", "\n"], "", $keyString);
        $regex = '/-{5}BEGIN(?:\s|\w)+' . $delimiter . '-{5}\s*(.+?)\s*-{5}END(?:\s|\w)+' . $delimiter . '-{5}/m';
        preg_match($regex, $keyString, $matches);
        return empty($matches[1]) ? '' : $matches[1];
    }

}