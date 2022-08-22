<?php

namespace Freegee\LaravelSaml2\Repositories;

use Freegee\LaravelSaml2\Models\IdentityProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class IdentityProviderRepository
{
    /**
     * Create a new query.
     *
     * @param bool $withTrashed Whether to include safely deleted records.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(bool $withTrashed = false): Builder
    {
        $query = IdentityProvider::query();

        if($withTrashed) {
            $query->withTrashed();
        }

        return $query;
    }

    /**
     * Find all identity providers.
     *
     * @param bool $withTrashed Whether to include safely deleted records.
     *
     * @return IdentityProvider[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all(bool $withTrashed = true): Collection|array
    {
        return $this->query($withTrashed)->get();
    }

    /**
     * Find a identity provider by any identifier.
     *
     * @param int|string $key ID or key
     * @param bool $withTrashed Whether to include safely deleted records.
     *
     * @return IdentityProvider[]|\Illuminate\Database\Eloquent\Collection
     */
    public function findByAnyIdentifier($key, bool $withTrashed = true): Collection|array
    {
        return $this->query($withTrashed)
            ->where('id', $key)
            ->orWhere('idp_key', $key)
            ->get();
    }

    /**
     * Find a identity provider by the key.
     *
     * @param string $key
     * @param bool $withTrashed
     *
     * @return IdentityProvider|\Illuminate\Database\Eloquent\Model|null
     */
    public function findByKey(string $key, bool $withTrashed = true): Model|IdentityProvider|null
    {
        return $this->query($withTrashed)
            ->where('idp_key', $key)
            ->first();
    }

    /**
     * Find a tenant by ID.
     *
     * @param int $id
     * @param bool $withTrashed
     *
     * @return IdentityProvider|\Illuminate\Database\Eloquent\Model|null
     */
    public function findById(int $id, bool $withTrashed = true): Model|IdentityProvider|null
    {
        return $this->query($withTrashed)
            ->where('id', $id)
            ->first();
    }

}