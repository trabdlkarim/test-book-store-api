<?php

namespace App\OAuth2;

use Luracast\Restler\iAuthenticate;
use OAuth2\GrantType\UserCredentials;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\RefreshToken;
use OAuth2\Server as OAuth2Server;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\RequestInterface;
use OAuth2\Request;
use OAuth2\Response;

/**
 * Class Server
 *
 * @package OAuth2
 *
 */
class Server implements iAuthenticate
{
    /**
     * @var OAuth2Server
     */
    protected static $server;

    /**
     * @var Pdo
     */
    protected static $storage;

    /**
     * @var RequestInterface
     */
    protected static $request;


    public function __construct()
    {
        static::$storage = new MySQLStorage();
        $grantTypes = array(
            'authorization_code' => new AuthorizationCode(static::$storage),
            'user_credentials'   => new UserCredentials(static::$storage),
            'client_credentials' => new ClientCredentials(static::$storage),
            'refresh_token' => new RefreshToken(static::$storage),
        );
        static::$request = Request::createFromGlobals();
        static::$server = new OAuth2Server(
            static::$storage,
            array('enforce_state' => true, 'allow_implicit' => true, 'always_issue_new_refresh_token' => true),
            $grantTypes
        );
    }

    /**
     * Stage 1: Client sends the user to this page
     *
     * User responds by accepting or denying
     *
     */
    public function authorize()
    {
        static::$server->getResponse(static::$request);
        if (!static::$server->validateAuthorizeRequest(static::$request)) {
          return  static::$server->getResponse()->send();
        }
        return array('queryString' => $_SERVER['QUERY_STRING']);
    }

    /**
     * Stage 2: User response is captured here
     *
     * Success or failure is communicated back to the Client using the redirect
     * url provided by the client
     *
     * On success authorization code is sent along
     *
     *
     * @param bool $authorize
     *
     * @return \OAuth2\Response
     *
     * @format JsonFormat,UploadFormat
     */
    public function postAuthorize($authorize = false)
    {
       return static::$server->handleAuthorizeRequest(
            static::$request,
            new Response(),
            (bool)$authorize
        )->send();
    }

    /**
     * Stage 3: Client directly calls this api to exchange access token
     *
     * It can then use this access token to make calls to protected api
     *
     * @format JsonFormat,UploadFormat
     */
    public function postToken()
    {
        static::$server->handleTokenRequest(static::$request)->send();
        exit;
    }

    /**
     * Access verification method.
     *
     * API access will be denied when this method returns false
     *
     * @return boolean true when api access is allowed; false otherwise
     */
    public function __isAllowed()
    {
        return self::$server->verifyResourceRequest(static::$request);
    }

    public function __getWWWAuthenticateString()
    {
        return 'Bearer realm="example"';
    }
}
