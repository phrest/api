<?php

namespace PhrestAPI\DI;

use Phalcon\DI\FactoryDefault as PhalconDI;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Http\Request;
use Phalcon\Mvc\Dispatcher as MVCDispatcher;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Registry;
use PhrestAPI\Request\PhrestRequest;
use PhrestAPI\Responses\CSVResponse;
use PhrestAPI\Responses\JSONResponse;
use PhrestAPI\Responses\Response;
use WZSDK\SDK;

/**
 * PhrestDI
 */
class PhrestDI extends PhalconDI
{
  /**
   * Construct all of the dependencies for the API
   */
  public function __construct()
  {
    parent::__construct();

    // Prepare the request object
    $this->set(
      'request',
      function()
      {
        return new PhrestRequest();
      }
    );

    // Prepare the Response object
    /*$this->set(
      'response',
      function ()
      {
        // Prepare relevant response object
        switch($this->get('request')->get('type'))
        {
          case Response::TYPE_RAW:
            return new Response();
            break;
          case Response::TYPE_CSV:
            return new CSVResponse();
            break;
          case Response::TYPE_JSON:
          default:
            return new JSONResponse();
            break;
        }
      },
      true
    );*/
  }
}
