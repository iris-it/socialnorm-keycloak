<?php namespace SocialNorm\Keycloak;

use SocialNorm\Exceptions\InvalidAuthorizationCodeException;
use SocialNorm\Providers\OAuth2Provider;

class KeycloakProvider extends OAuth2Provider
{

    protected $auth_server;
    protected $auth_realm;

    /**
     * KeycloakProvider constructor.
     *
     * Really ugly i know ... but i needed to get args from config !
     * TODO Fix this crap
     */
    public function __construct()
    {
        $args = func_get_args();
        call_user_func_array(array('parent', '__construct'), $args);

        $this->auth_server = $args[0]['auth_server'];
        $this->auth_realm = $args[0]['auth_realm'];
    }

    protected $scope = [
        'view-profile',
        'manage-account',
    ];

    protected $headers = [
        'authorize' => [],
        'access_token' => [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ],
        'user_details' => [],
    ];

    protected function compileScopes()
    {
        return implode(' ', $this->scope);
    }

    protected function getAuthorizeUrl()
    {
        return $this->auth_server . "/realms/" . $this->auth_realm . "/protocol/openid-connect/auth";
    }

    protected function getAccessTokenBaseUrl()
    {
        return $this->auth_server . "/realms/" . $this->auth_realm . "/protocol/openid-connect/token";
    }

    protected function getUserDataUrl()
    {
        return $this->auth_server . "/realms/" . $this->auth_realm . "/protocol/openid-connect/userinfo";
    }

    protected function parseTokenResponse($response)
    {
        return $this->parseJsonTokenResponse($response);
    }

    protected function requestUserData()
    {

        $url = $this->buildUserDataUrl();
        $response = $this->httpClient->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ]
        ]);
        return $this->parseUserDataResponse((string)$response->getBody());
    }

    protected function parseUserDataResponse($response)
    {
        return json_decode($response, true);
    }

    protected function userId()
    {
        return $this->getProviderUserData('sub');
    }

    protected function nickname()
    {
        return $this->getProviderUserData('preferred_username');
    }

    protected function fullName()
    {
        return $this->getProviderUserData('given_name') . ' ' . $this->getProviderUserData('family_name');
    }

    protected function avatar()
    {
        return "";
    }

    protected function email()
    {
        return $this->getProviderUserData('email');
    }
}
