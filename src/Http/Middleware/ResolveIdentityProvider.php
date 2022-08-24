<?php

namespace Freegee\LaravelSaml2\Http\Middleware;

use Closure;
use Freegee\LaravelSaml2\Models\IdentityProvider;
use Freegee\LaravelSaml2\OneLoginBuilder;
use Freegee\LaravelSaml2\Repositories\IdentityProviderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveIdentityProvider
{

    /**
     * @var \Freegee\LaravelSaml2\Repositories\IdentityProviderRepository
     */
    protected IdentityProviderRepository $identityProviderRepository;

    /**
     * @var \Freegee\LaravelSaml2\OneLoginBuilder
     */
    protected OneLoginBuilder $oneLoginBuilder;

    /**
     * @param  \Freegee\LaravelSaml2\Repositories\IdentityProviderRepository  $identityProviderRepository
     * @param  \Freegee\LaravelSaml2\OneLoginBuilder  $oneLoginBuilder
     */
    public function __construct(IdentityProviderRepository $identityProviderRepository, OneLoginBuilder $oneLoginBuilder)
    {
        $this->identityProviderRepository = $identityProviderRepository;
        $this->oneLoginBuilder = $oneLoginBuilder;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @throws NotFoundHttpException
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $identityProvider = $this->resolveIdentityProvider($request);
        if(is_null($identityProvider)) {
            throw new NotFoundHttpException();
        }

        if(config('saml2_setting.debug')) {
            Log::debug('[Saml2] Identity provider resolved', [
                'id' => $identityProvider->id,
                'IdP key' => $identityProvider->idp_key
            ]);
        }

        session()->flash('saml2.identityProvider.key', $identityProvider->idp_key);

        $this->oneLoginBuilder
            ->withIdentityProvider($identityProvider)
            ->bootstrap();

        return $next($request);
    }


    /**
     * Resolve a identity provider by a request.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Freegee\LaravelSaml2\Models\IdentityProvider|null
     */
    protected function resolveIdentityProvider(Request $request): ?IdentityProvider
    {
        $idpKey = $request->route('idpKey');
        if(empty($idpKey)) {
            if(config('saml2_setting.debug')) {
                Log::debug('[Saml2] Identity Provider Key (idpKey) is not present in the URL so cannot be resolved', [
                    'url' => $request->fullUrl()
                ]);
            }

            return null;
        }

        $identityProvider = $this->identityProviderRepository->findByKey($idpKey);
        if(empty($identityProvider)) {
            if(config('saml2_setting.debug')) {
                Log::debug('[Saml2] Identity Provider doesn\'t exist', [
                    'idpKey' => $idpKey
                ]);
            }

            return null;
        }

        if($identityProvider->trashed()) {
            if (config('saml2_setting.debug')) {
                Log::debug('[Saml2] Identity Provider #' . $identityProvider->id. ' resolved but marked as deleted', [
                    'id' => $identityProvider->id,
                    'IdP Key' => $idpKey,
                    'deleted_at' => $identityProvider->deleted_at->toDateTimeString()
                ]);
            }

            return null;
        }

        return $identityProvider;
    }
}