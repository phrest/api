<?php
namespace PhrestAPI;

use Phalcon\DI;
use PhrestAPI\Collections\Collection;
use Phalcon\Mvc\Micro\Collection as PhalconCollection;
use PhrestAPI\Collections\CollectionRoute;
use PhrestAPI\Responses\CSVResponse;
use PhrestAPI\Responses\JSONResponse;
use Phalcon\DI\FactoryDefault as DefaultDI;
use Phalcon\Exception;
use Phalcon\Mvc\Micro as MicroMVC;
use PhrestAPI\Responses\Response;

/**
 * Phalcon API Application
 */
class PhrestAPI extends MicroMVC
{
  const METHOD_OPTIONS = 'OPTIONS';
  const METHOD_POST = 'POST';
  const METHOD_HEAD = 'HEAD';
  const METHOD_GET = 'GET';
  const METHOD_PUT = 'PUT';
  const METHOD_DELETE = 'DELETE';

  /** @var  string */
  private $srcDir;

  /** @var  string */
  private $collectionDir;

  /** @var bool */
  public $isInternal = false;

  public function __construct(DefaultDI $di, $srcDir = null)
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

    // Set the Exception handler
    $this->setExceptionHandler($di);

    // Collections are how we handler our routes
    $di->set(
      'collections',
      function ()
      {
        return $this->getPhalconCollections();
      }
    );

    // Prepare the Response object
    $di->set(
      'response',
      function ()
      {
        // Prepare relevant response object
        switch($this->request->get('type'))
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
    );

    // Handle the response type
    /*$this->after(
      function ()
      {

        echo 2; die;
        // An internal call simply returns the Response object
        if($this->isInternal)
        {
          return;
        }

        // OPTIONS have no body, send the headers & exit
        if($this->request->getMethod() == self::METHOD_OPTIONS)
        {
          $this->response->setStatusCode('200', 'OK');
          $this->response->send();
          return;
        }

        // Set data in the response

        $response = $this->di->get('response');
        $response->setData($this->getReturnedValue());

        var_dump($this->getReturnedValue()); die;

        return;

        // Respond by default as JSON
        if(
          !$this->request->get('type')
          || $this->request->get('type') == Response::TYPE_JSON
        )
        {

          // Results returned from the route's controller.
          // All Controllers should return an array
          //$data = $this->getReturnedValue();

          var_dump($this->di->get('response'));
          die;



          return;
        }
        else if($this->request->get('type') == self::RESPONSE_TYPE_CSV)
        {

          $data = $this->getReturnedValue();
          $response = new CSVResponse();
          $response->useHeaderRow(true)->send($data);

          return;
        }
        else
        {
          throw new HTTPException(
            'Could not return results in specified format',
            403,
            array(
              'dev' => 'Could not understand type specified by type paramter in query string.',
              'internalCode' => 'NF1000',
              'more' => 'Type may not be implemented. Choose either "csv" or "json"'
            )
          );
        }
      }
    );*/

    // todo
    $di->set(
      'modelsCache',
      function ()
      {

        //Cache data for one day by default
        $frontCache = new \Phalcon\Cache\Frontend\Data(
          array(
            'lifetime' => 3600
          )
        );

        //File cache settings
        $cache = new \Phalcon\Cache\Backend\File(
          $frontCache, array(
            'cacheDir' => DIR . '/cache/'
          )
        );

        return $cache;
      }
    );

    /**
     * If our request contains a body, it has to be valid JSON.  This parses the
     * body into a standard Object and makes that vailable from the DI.  If this service
     * is called from a function, and the request body is nto valid JSON or is empty,
     * the program will throw an Exception.
     */
    $di->setShared(
      'requestBody',
      function ()
      {
        $in = file_get_contents('php://input');
        $in = json_decode($in, FALSE);

        // JSON body could not be parsed, throw exception
        if($in === null)
        {
          throw new \Exception(
            'There was a problem understanding the data sent to the server by the application.',
            409,
            array(
              'dev' => 'The JSON body sent to the server was unable to be parsed.',
              'internalCode' => 'REQ1000',
              'more' => ''
            )
          );
        }

        return $in;
      }
    );

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
  }

  /**
   * @return Collection[]
   * @throws \Exception
   */
  protected function getCollections()
  {
    throw new \Exception('No collections defined');
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
          case CollectionRoute::TYPE_GET:
            $phalconCollection->get($route->route, $route->action);
            break;
          case CollectionRoute::TYPE_POST:
            $phalconCollection->post($route->route, $route->action);
            break;
          case CollectionRoute::TYPE_PUT:
            $phalconCollection->put($route->route, $route->action);
            break;
          case CollectionRoute::TYPE_DELETE:
            $phalconCollection->delete($route->route, $route->action);
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
   * @param $collectionDir
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
