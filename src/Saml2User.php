<?php

namespace Sulytics\Saml2;

use Sulytics\Saml2\Models\IdentityProvider;
use OneLogin\Saml2\Auth as OneLoginAuth;

class Saml2User
{
    /**
     * @var \OneLogin\Saml2\Auth
     */
    protected OneLoginAuth $oneLoginAuth;

    /**
     * @var \Sulytics\Saml2\Models\IdentityProvider
     */
    protected IdentityProvider $identityProvider;


    public function __construct(OneLoginAuth $oneLoginAuth, IdentityProvider $identityProvider)
    {
        $this->oneLoginAuth = $oneLoginAuth;
        $this->identityProvider = $identityProvider;
    }

    /**
     * @return \OneLogin\Saml2\Auth
     */
    public function getOneLoginAuth(): OneLoginAuth
    {
        return $this->oneLoginAuth;
    }

    /**
     * @param  \OneLogin\Saml2\Auth  $oneLoginAuth
     * @return Saml2User
     */
    public function setOneLoginAuth(OneLoginAuth $oneLoginAuth): Saml2User
    {
        $this->oneLoginAuth = $oneLoginAuth;

        return $this;
    }

    /**
     * @return \Sulytics\Saml2\Models\IdentityProvider
     */
    public function getIdentityProvider(): IdentityProvider
    {
        return $this->identityProvider;
    }

    /**
     * @param  \Sulytics\Saml2\Models\IdentityProvider  $identityProvider
     * @return Saml2User
     */
    public function setIdentityProvider(IdentityProvider $identityProvider): Saml2User
    {
        $this->identityProvider = $identityProvider;

        return $this;
    }

    /**
     * Get the user ID retrieved from assertion processed this request.
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->oneLoginAuth->getNameId();
    }

    /**
     * Get the attributes retrieved from assertion processed this request
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->oneLoginAuth->getAttributes();
    }

    /**
     * Returns the requested SAML attribute
     *
     * @param  string  $name The requested attribute of the user.
     *
     * @return array|null Requested SAML attribute ($name).
     */
    public function getAttribute(string $name): ?array
    {
        return $this->oneLoginAuth->getAttribute($name);
    }

    /**
     * The attributes retrieved from assertion processed this request.
     *
     * @return array
     */
    public function getAttributesWithFriendlyName(): array
    {
        return $this->oneLoginAuth->getAttributesWithFriendlyName();
    }

    /**
     * The SAML assertion processed this request.
     *
     * @return string
     */
    public function getRawSamlAssertion(): string
    {
        return app('request')->input('SAMLResponse'); //just this request
    }

    /**
     * Get the intended URL.
     *
     * @return mixed
     */
    public function getIntendedUrl(): mixed
    {
        $relayState = app('request')->input('RelayState');

        $url = app('Illuminate\Contracts\Routing\UrlGenerator');

        if ($relayState && $url->full() != $relayState) {
            return $relayState;
        }

        return null;
    }

    /**
     * Parses a SAML property and adds this property to this user or returns the value.
     *
     * @param  string|null  $samlAttribute
     * @param  string|null  $propertyName
     *
     * @return array|null
     */
    public function parseUserAttribute(string $samlAttribute = null, string $propertyName = null): ?array
    {
        if(empty($samlAttribute)) {
            return null;
        }

        if(empty($propertyName)) {
            return $this->getAttribute($samlAttribute);
        }

        return $this->{$propertyName} = $this->getAttribute($samlAttribute);
    }

    /**
     * Parse the SAML attributes and add them to this user.
     *
     * @param  array  $attributes Array of properties which need to be parsed, like ['email' => 'urn:oid:0.9.2342.19200300.100.1.3']
     *
     * @return void
     */
    public function parseAttributes(array $attributes = []): void
    {
        foreach($attributes as $propertyName => $samlAttribute) {
            $this->parseUserAttribute($samlAttribute, $propertyName);
        }
    }

    /**
     * Get user's session index.
     *
     * @return null|string
     */
    public function getSessionIndex(): ?string
    {
        return $this->oneLoginAuth->getSessionIndex();
    }

    /**
     * Get user's name ID.
     *
     * @return string
     */
    public function getNameId(): string
    {
        return $this->oneLoginAuth->getNameId();
    }
}