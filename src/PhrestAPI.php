<?php
namespace Phrest\API;

use Phalcon\DI;
use Phrest\API\Collections\Collection;
use Phalcon\Mvc\Micro\Collection as PhalconCollection;
use Phrest\API\Collections\CollectionRoute;
use Phrest\API\DI\PhrestDI;
use Phrest\API\Request\PhrestRequest;
use Phrest\API\Responses\CSVResponse;
use Phrest\API\Responses\JSONResponse;
use Phalcon\DI\FactoryDefault as DefaultDI;
use Phalcon\Exception;
use Phalcon\Mvc\Micro as MicroMVC;
use Phalcon\Http\Response;
use Phrest\SDK\PhrestSDK;

/**
 * Phalcon API Application
 */
class PhrestAPI extends MicroMVC
{
  /** @var  string */
  protected $srcDir;

  /** @var  string */
  private $collectionDir;

  /** @var bool */
  public $isInternalRequest = false;

  public function __construct(PhrestDI $di, $srcDir = null)
  {
    // Set the applications src directory
    if(!$srcDir)
    {
      // Assume the src directory based on standard structure
      $srcDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/src';
    }
    $this->srcDir = $srcDir;

    // Set the assumed collections dir
    $this->collectionDir = $this->srcDir . '/Collections/';

    // Collections are how we handler our routes
    $di->set(
      'collections',
      function ()
      {
        return $this->getPhalconCollections();
      }
    );

    // Set the Exception handler
    $this->setExceptionHandler($di);

    $this->setDI($di);

    // Handle a 404
    $this->notFound(
      function () use ($di)
      {
        // Method
        if(PhrestSDK::$method && PhrestSDK::$uri)
        {
          // Set exception message
          $message = sprintf(
            '404: Route not found: %s (via SDK) to %s',
            PhrestSDK::$method,
            PhrestSDK::$uri
          );
        }
        else
        {
          // Set exception message
          /** @var PhrestRequest $request */
          $request = $di->get('request');
          $message = sprintf(
            '404: Route not found: %s to %s',
            $request->getMethod(),
            $request->getURI()
          );
        }

        throw new \Exception($message, 404);
      }
    );

    // Mount all of the collections, which makes the routes active.
    foreach($di->get('collections') as $collection)
    {
      $this->mount($collection);
    }

    // Send the response if required
    $this->after(
      function () use ($di)
      {
        // Internal request will return the response
        if($this->isInternalRequest)
        {
          return;
        }

        $controllerResponse = $this->getReturnedValue();

        if(is_a($controllerResponse, 'Phrest\API\Responses\ResponseArray'))
        {
          /** @var $controllerResponse \Phrest\API\Responses\ResponseArray */
          $controllerResponse->setCount($controllerResponse->getCount());
        }

        //var_dump($controllerResponse);

        /** @var PhrestRequest $response */
        $request = $di->get('request');

        if($request->isJSON())
        {
          $di->set('response', new JSONResponse($controllerResponse));
        }

        /** @var Response $response */
        $response = $di->get('response');
        $response->send();
      }
    );
  }

  /**
   * Get collections
   *
   * @throws \Exception
   */
  public function getCollections()
  {
    throw new \Exception("Please implement method getCollections()");
  }

  private function getPhalconCollections()
  {
    $collections = $this->getCollections();

    $phalconCollections = [];
    foreach($collections as $collection)
    {
      $phalconCollection = new PhalconCollection();

      $phalconCollection
        // It is advised to use a version number i.e. /v1/ in the URL
        ->setPrefix($collection->prefix)
        // Must be a string in order to support lazy loading
        ->setHandler($collection->controller)
        ->setLazy(true);

      foreach($collection->routes as $route)
      {
        // Switch should be quicker
        switch($route->type)
        {
          case PhrestRequest::METHOD_GET:
            $phalconCollection->get(
              $route->routePattern,
              $route->controllerAction
            );
            break;
          case PhrestRequest::METHOD_POST:
            $phalconCollection->post(
              $route->routePattern,
              $route->controllerAction
            );
            break;
          case PhrestRequest::METHOD_PUT:
            $phalconCollection->put(
              $route->routePattern,
              $route->controllerAction
            );
            break;
          case PhrestRequest::METHOD_PATCH:
            $phalconCollection->patch(
              $route->routePattern,
              $route->controllerAction
            );
            break;
          case PhrestRequest::METHOD_DELETE:
            $phalconCollection->delete(
              $route->routePattern,
              $route->controllerAction
            );
            break;
          default:
            throw new \Exception('Invalid CollectionRoute');
            break;
        }

        $phalconCollections[] = $phalconCollection;
      }
    }

    return $phalconCollections;
  }

  /**
   * Override the standard "Collection" directory path
   *
   * @param $collectionDir
   *
   * @return $this
   */
  public function setCollectionDir($collectionDir)
  {
    $this->collectionDir = $collectionDir;

    return $this;
  }

  /**
   * If the application throws an HTTPException, respond correctly (json etc.)
   * todo this was not working as the try catch blocks in controllers
   * was catching the exception before it would be handled, need
   * to come back to this
   */
  public function setExceptionHandler(DI $di)
  {
    return $this;

    set_exception_handler(
      function ($exception) use ($di)
      {
        /** @var $exception Exception */

        // Handled exceptions
        if(is_a($exception, 'Phrest\API\\Exceptions\\HandledException'))
        {
          /** @var Response $response */
          $response = $di->get('response');

          //Set the content of the response
          $response->setContent($exception->getMessage());

          return $response->send();
        }

        // Log the exception
        error_log($exception);
        error_log($exception->getTraceAsString());

        // Throw unhandled exceptions
        throw $exception;
      }
    );
  }
}
