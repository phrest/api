<?php

namespace Phrest\API\DI;

use Phalcon\DI;
use Phalcon\DI\FactoryDefault;
use Phalcon\Http\Request;
use Phalcon\Mvc\Dispatcher as MVCDispatcher;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router;
use Phrest\API\Request\PhrestRequest;

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
