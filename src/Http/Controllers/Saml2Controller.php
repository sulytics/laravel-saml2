<?php

namespace Freegee\LaravelSaml2\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Freegee\LaravelSaml2\Auth;
use Freegee\LaravelSaml2\Events\SignedIn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;

class Saml2Controller extends Controller
{

    /**
     * Initiate a logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Freegee\LaravelSaml2\Auth  $auth
     *
     * @return void
     *
     * @throws \OneLogin\Saml2\Error
     */
    public function logout(Request $request, Auth $auth): void
    {
        $auth->logout(
            $request->query('returnTo'),
            $request->query('nameId'),
            $request->query('sessionIndex')
        );
    }

    /**
     * Initiate a login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Freegee\LaravelSaml2\Auth  $auth
     *
     * @return void
     *
     * @throws \OneLogin\Saml2\Error
     */
    public function login(Request $request, Auth $auth): void
    {
        $redirectUrl = $auth->getIdentityProvider()->idp_relay_state_url ?: config('saml2_settings.loginRoute');

        $auth->login($request->query('returnTo', $redirectUrl));
    }

    /**
     * Render the metadata.
     *
     * @param  \Freegee\LaravelSaml2\Auth  $auth
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \OneLogin\Saml2\Error
     */
    public function metadata(Auth $auth): Response
    {
        $metadata = $auth->getMetadata();
        return response($metadata, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Process the SAML Response sent by the IdP.
     *
     * Fires "SignedIn" event if a valid user is found.
     *
     * @param  \Freegee\LaravelSaml2\Auth  $auth
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * @throws \OneLogin\Saml2\Error
     * @throws \OneLogin\Saml2\ValidationError
     */
    public function acs(Auth $auth): Redirector|RedirectResponse
    {
        $errors = $auth->acs();

        if (!empty($errors)) {
            if(config('saml2_settings.debug')) {
                Log::error('There was an error while authenticatin with Saml', [
                    'error' => $auth->getLastErrorReason()]);
                session()->flash('There was an error while authenticatin with Saml', [$auth->getLastErrorReason()]);
                Log::error('There was an error while authenticatin with Saml', $errors);
                session()->flash('There was an error while authenticatin with Saml', $errors);
            }

            return redirect(config('saml2_settings.errorRoute'));
        }

        $user = $auth->getSaml2User();

        event(new SignedIn($user, $auth));

        $redirectUrl = $user->getIntendedUrl();

        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        return redirect($auth->getIdentityProvider()->idp_relay_state_url ?: config('saml2_settings.loginRoute'));
    }

    /**
     * Process the SAML Logout Response / Logout Request sent by the IdP.
     *
     * Fires 'saml2.logoutRequestReceived' event if is valid.
     *
     * This means the user logged out of the SSO infrastructure, you 'should' log him out locally too.
     *
     * @param  \Freegee\LaravelSaml2\Auth  $auth
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * @throws \OneLogin\Saml2\Error
     * @throws \Exception
     */
    public function sls(Auth $auth): Redirector|RedirectResponse
    {
        $error = $auth->sls(config('saml2_settings.retrieveParametersFromServer'));

        if (!empty($error)) {
            throw new Exception("Could not log out");
        }

        return redirect(config('saml2_settings.logoutRoute')); //may be set a configurable default
    }
}