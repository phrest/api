<?php
namespace PhrestAPI;

use Phalcon\DI;
use PhrestAPI\Collections\Collection;
use Phalcon\Mvc\Micro\Collection as PhalconCollection;
use PhrestAPI\Collections\CollectionRoute;
use PhrestAPI\DI\PhrestDI;
use PhrestAPI\Request\PhrestRequest;
use PhrestAPI\Responses\CSVResponse;
use PhrestAPI\Responses\JSONResponse;
use Phalcon\DI\FactoryDefault as DefaultDI;
use Phalcon\Exception;
use Phalcon\Mvc\Micro as MicroMVC;
//use PhrestAPI\Responses\Response;
use Phalcon\Http\Response;

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
        throw new \Exception('Route not matched');
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
   */
  public function setExceptionHandler(DI $di)
  {
    set_exception_handler(
      function ($exception) use ($di)
      {
        /** @var $exception Exception */

        // Handled exceptions
        if(is_a($exception, 'PhrestAPI\\Exceptions\\HandledException'))
        {
          /** @var Response $response */
          $response = $di->get('response');
          return $response->sendException($exception);
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
