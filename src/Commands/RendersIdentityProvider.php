<?php

namespace Sulytics\Saml2\Commands;

use FreeGee\LaravelSaml2\Models\IdentityProvider;
use Illuminate\Support\Str;

trait RendersIdentityProvider
{
    /**
     * Render tenants in a table.
     *
     * @param \FreeGee\LaravelSaml2\Models\IdentityProvider|\Illuminate\Support\Collection $identityProviders
     * @param string|null $title
     *
     * @return void
     */
    protected function renderIdentityProviders($identityProviders, string $title = null)
    {
        /** @var \FreeGee\LaravelSaml2\Models\IdentityProvider[]|\Illuminate\Database\Eloquent\Collection $tenants */
        $identityProviders = $identityProviders instanceof IdentityProvider
            ? collect([$identityProviders])
            : $identityProviders;

        $headers = ['Column', 'Value'];
        $columns = [];

        foreach ($tenants as $tenant) {
            foreach ($this->getIdentityProviderColumns($tenant) as $column => $value) {
                $columns[] = [$column, $value ?: '(empty)'];
            }

            if($tenants->last()->id !== $tenant->id) {
                $columns[] = new \Symfony\Component\Console\Helper\TableSeparator();
            }
        }

        if($title) {
            $this->getOutput()->title($title);
        }

        $this->table($headers, $columns);
    }

    /**
     * Get a columns of the Tenant.
     *
     * @param  \FreeGee\LaravelSaml2\Models\IdentityProvider  $identityProviders
     * @return array
     */
    protected function getIdentityProviderColumns(IdentityProvider $identityProviders): array
    {
        return [
            'ID' => $identityProviders->id,
            'Key' => $identityProviders->idp_key,
            'Entity ID' => $identityProviders->idp_entity_id,
            'Login URL' => $identityProviders->idp_login_url,
            'Logout URL' => $identityProviders->idp_logout_url,
            'Relay State URL' => $identityProviders->relay_state_url,
            'Name ID format' => $identityProviders->name_id_format,
            'x509 cert' => Str::limit($identityProviders->idp_x509_cert, 50),
            'Metadata' => $this->renderArray($identityProviders->metadata ?: []),
            'Created' => $identityProviders->created_at->toDateTimeString(),
            'Updated' => $identityProviders->updated_at->toDateTimeString(),
            'Deleted' => $identityProviders->deleted_at ? $identityProviders->deleted_at->toDateTimeString() : null
        ];
    }

    /**
     * Render a tenant credentials.
     *
     * @param \FreeGee\LaravelSaml2\Models\IdentityProvider $identityProviders
     *
     * @return void
     */
    protected function renderIdentityProvidersCredentials(IdentityProvider $identityProviders): void
    {
        $this->output->section('Credentials for the tenant');

        $identifier = route('saml.metadata', ['idpKey' => $identityProviders->idp_key]);

        $this->getOutput()->text([
            'Identifier (Entity ID): <comment>' . $identifier . '</comment>',
            'Reply URL (Assertion Consumer Service URL): <comment>' . route('saml.acs', ['idpKey' =>$identityProviders->idp_key]) . '</comment>',
            'Sign on URL: <comment>' . route('saml.login', ['idpKey' => $identityProviders->idp_key]) . '</comment>',
            'Logout URL: <comment>' . route('saml.logout', ['idpKey' => $identityProviders->idp_key]) . '</comment>',
            'Relay State: <comment>' . ($identityProviders->relay_state_url ?: config('saml2.loginRoute')) . ' (optional)</comment>'
        ]);
    }

    /**
     * Print an array to a string.
     *
     * @param array $array
     *
     * @return string
     */
    protected function renderArray(array $array)
    {
        $lines = [];

        foreach ($array as $key => $value) {
            $lines[] = "$key: $value";
        }

        return implode(PHP_EOL, $lines);
    }
}