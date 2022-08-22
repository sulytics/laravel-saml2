<?php

namespace Freegee\LaravelSaml2\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class IdentityProvider
 *
 * @property int $id
 * @property int $service_provider_id
 * @property string $idp_key
 * @property string $idp_entity_id
 * @property string $idp_login_url
 * @property string $idp_login_binding
 * @property string $idp_logout_url
 * @property string $idp_logout_binding
 * @property string|null $idp_relay_state_url
 * @property string $idp_x509_cert
 * @property array $idp_metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @@property-read \Freegee\LaravelSaml2\Models\ServiceProvider $serviceProvider
 */
class IdentityProvider extends Model
{
    use SoftDeletes;

    protected $table = 'saml2_identity_providers';

    protected $fillable = [
        'service_provider_id',
        'idp_key',
        'idp_entity_id',
        'idp_login_url',
        'idp_login_binding',
        'idp_logout_url',
        'idp_logout_binding',
        'idp_relay_state_url',
        'idp_x509_cert',
        'idp_metadata',
    ];

    protected $casts = [
        'idp_metadata' => 'array',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}