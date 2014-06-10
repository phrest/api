<?php

namespace PhrestAPI\DI;

use League\OAuth2\Server\Authorization;
use League\OAuth2\Server\Grant\ClientCredentials;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\DI\FactoryDefault as PhalconDI;
use Phalcon\DI;
use Phalcon\DI\FactoryDefault;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Http\Request;
use Phalcon\Mvc\Dispatcher as MVCDispatcher;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router;
use Phalcon\Registry;
use PhrestAPI\Oauth2\Server\Storage\Pdo\Mysql\Client;
use PhrestAPI\Oauth2\Server\Storage\Pdo\Mysql\Scope;
use PhrestAPI\Oauth2\Server\Storage\Pdo\Mysql\Session;
use PhrestAPI\Request\PhrestRequest;
use PhrestAPI\Responses\CSVResponse;
use PhrestAPI\Responses\JSONResponse;
use PhrestAPI\Responses\Response;

/**
 * PhrestDI
 */
class PhrestDI extends FactoryDefault
{
  /**
   * Construct all of the dependencies for the API
   */
  public function __construct()
  {
    // Prepare the request object
    $this['request'] = function ()
    {
      return new PhrestRequest();
    };

    // Set up oAuth2 service
    $this['oauth2'] = function ()
    {
      $config = $this->get('config');
      $oauth2DB = new Mysql($config->oauth2DB->toArray());

      // Create oAuth2 server
      $server = new Authorization(
        new Client($oauth2DB),
        new Session($oauth2DB),
        new Scope($oauth2DB)
      );

      # Not required as it called directly from original code
      # $request = new \League\OAuth2\Server\Util\Request();

      # add these 2 lines code if you want to use my own Request otherwise comment it
      // todo required?
      //$request = new \PhrestAPI\Oauth2\Server\Request();
      //$server->setRequest($request);

      $server->setAccessTokenTTL(86400);
      $server->addGrantType(new ClientCredentials());

      return $server;
    };

    // Default router
    $this['router'] = function ()
    {
      return new Router;
    };


    return parent::__construct();
  }
}
