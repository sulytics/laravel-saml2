<?php

namespace Freegee\LaravelSaml2\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ServiceProvider
 *
 * @property int $id
 * @property string $sp_name_id_format
 * @property string $sp_x509_cert
 * @property string $sp_x509_cert_new
 * @property string $sp_private_key
 * @property string $sp_entity_id
 * @property string $sp_assertion_consumer_url
 * @property string $sp_assertion_consumer_binding
 * @property string $sp_single_logout_service_url
 * @property string sp_single_logout_service_binding
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class ServiceProvider extends \Illuminate\Database\Eloquent\Model
{
    use SoftDeletes;

    protected $table = 'saml2_service_providers';

    protected $fillable = [
        'sp_name_id_format',
        'sp_x509_cert',
        'sp_x509_cert_new',
        'sp_private_key',
        'sp_entity_id',
        'sp_assertion_consumer_url',
        'sp_assertion_consumer_binding',
        'sp_single_logout_service_url',
        'sp_single_logout_service_binding',
    ];

    protected $dates = [
        'deleted_at',
    ];


    public function identityProviders(): HasMany
    {
        return $this->hasMany(IdentityProvider::class);
    }
}