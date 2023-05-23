<?php

namespace Sulytics\Saml2\Http\Middleware;

use Closure;
use Sulytics\Saml2\Models\IdentityProvider;
use Sulytics\Saml2\OneLoginBuilder;
use Sulytics\Saml2\Repositories\IdentityProviderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveIdentityProvider
{

    /**
     * @var \Sulytics\Saml2\Repositories\IdentityProviderRepository
     */
    protected IdentityProviderRepository $identityProviderRepository;

    /**
     * @var \Sulytics\Saml2\OneLoginBuilder
     */
    protected OneLoginBuilder $oneLoginBuilder;

    /**
     * @param  \Sulytics\Saml2\Repositories\IdentityProviderRepository  $identityProviderRepository
     * @param  \Sulytics\Saml2\OneLoginBuilder  $oneLoginBuilder
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

        if(config('saml2_settings.debug')) {
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
     * @return \Sulytics\Saml2\Models\IdentityProvider|null
     */
    protected function resolveIdentityProvider(Request $request): ?IdentityProvider
    {
        $idpKey = $request->route('idpKey');
        if(empty($idpKey)) {
            if(config('saml2_settings.debug')) {
                Log::debug('[Saml2] Identity Provider Key (idpKey) is not present in the URL so cannot be resolved', [
                    'url' => $request->fullUrl()
                ]);
            }

            return null;
        }

        $identityProvider = $this->identityProviderRepository->findByKey($idpKey);
        if(empty($identityProvider)) {
            if(config('saml2_settings.debug')) {
                Log::debug('[Saml2] Identity Provider doesn\'t exist', [
                    'idpKey' => $idpKey
                ]);
            }

            return null;
        }

        if($identityProvider->trashed()) {
            if (config('saml2_settings.debug')) {
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