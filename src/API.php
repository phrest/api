<?php
namespace PhalconAPI;

use API\Responses\CSVResponse;
use API\Responses\JSONResponse;
use Phalcon\DI\FactoryDefault as DefaultDI;
use Phalcon\Exception;
use Phalcon\Mvc\Collection;
use Phalcon\Mvc\Micro as MicroMVC;
use PhalconAPI\Exceptions\HTTPException;

/**
 * Phalcon API Application
 */
class API extends MicroMVC
{
  const METHOD_OPTIONS = 'OPTIONS';
  const RESPONSE_TYPE_CSV = 'csv';
  const RESPONSE_TYPE_JSON = 'json';
  const RESPONSE_TYPE_RAW = 'raw';

  /** @var  string */
  private $srcDir;

  /** @var  string */
  private $collectionDir;

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
    $this->setExceptionHandler();

    // Collections are how we handler our routes
    $di->set(
      'collections',
      function ()
      {
        return $this->getCollections();
      }
    );

    // Handle the response type
    $this->after(
      function ()
      {
        // OPTIONS have no body, send the headers & exit
        if($this->request->getMethod() == self::METHOD_OPTIONS)
        {
          $this->response->setStatusCode('200', 'OK');
          $this->response->send();
          return;
        }

        // Respond by default as JSON
        if(
          !$this->request->get('type')
          || $this->request->get('type') == self::RESPONSE_TYPE_JSON
        )
        {

          // Results returned from the route's controller.  All Controllers should return an array
          $records = $this->getReturnedValue();

          $response = new JSONResponse();
          $response->useEnvelope(true) //this is default behavior
          //->convertSnakeCase(true) //this is also default behavior
          ->send($records);

          return;
        }
        else if($this->request->get('type') == self::RESPONSE_TYPE_CSV)
        {

          $records = $this->getReturnedValue();
          $response = new CSVResponse();
          $response->useHeaderRow(true)->send($records);

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
    );

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
          throw new HTTPException(
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
      function ()
      {
        throw new HTTPException(
          '404 Not Found.',
          404,
          array(
            'dev' => 'That route was not found on the server.',
            'internalCode' => 'NF1000',
            'more' => 'Check route for mispellings.'
          )
        );
      }
    );

    // Mount all of the collections, which makes the routes active.
    foreach($di->get('collections') as $collection)
    {
      $this->mount($collection);
    }
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
  public function setExceptionHandler()
  {
    set_exception_handler(
      function ($exception)
      {
        /** @var $exception Exception */
        error_log($exception);
        error_log($exception->getTraceAsString());

        //HTTPException's send method provides the correct response headers and body
        if(is_a($exception, 'PhalconAPI\\Exceptions\\HTTPException'))
        {
          $exception->send();
        }
        else
        {
          throw $exception;
        }
      }
    );
  }

  /**
   * Checks in the collections folder for files to build the routes from
   *
   * @throws \Phalcon\Exception
   * @return Collection[]
   */
  private function getCollections()
  {
    $collections = array();

    // todo implement caching here, its slow
    $collectionFiles = scandir($this->collectionDir);

    foreach($collectionFiles as $collectionFile)
    {
      $pathinfo = pathinfo($collectionFile);

      //Only include php files
      if($pathinfo['extension'] === 'php')
      {

        // The collection files return their collection objects, so mount
        // them directly into the router.
        $collections[] = include($this->collectionDir . '/' . $collectionFile);
      }
    }

    if(!count($collections))
    {
      throw new Exception('No collection files found');
    }

    return $collections;
  }
}
