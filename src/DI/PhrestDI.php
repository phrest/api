<?php

namespace Phrest\API\DI;

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
use Phrest\API\Oauth2\Server\Storage\Pdo\Mysql\Client;
use Phrest\API\Oauth2\Server\Storage\Pdo\Mysql\Scope;
use Phrest\API\Oauth2\Server\Storage\Pdo\Mysql\Session;
use Phrest\API\Request\PhrestRequest;
use Phrest\API\Responses\CSVResponse;
use Phrest\API\Responses\JSONResponse;
use Phrest\API\Responses\Response;

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
    parent::__construct();

    $this->setShared('request',
      function ()
      {
        return new PhrestRequest();
      }
    );

    $this->setShared('oauth2',
      function ()
      {
        return false;
      }
    );


    $this->setShared('router',
      function ()
      {
        return new Router;
      }
    );
  }
}
