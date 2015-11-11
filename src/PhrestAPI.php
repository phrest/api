<?php
namespace Phrest\API;

use Phalcon\DiInterface;
use Phalcon\Http\Client\Request;
use Phalcon\Mvc\Micro\Collection as PhalconCollection;
use Phrest\API\DI\PhrestDI;
use Phrest\API\DI\PhrestDIInterface;
use Phrest\API\Enums\RequestMethodEnum;
use Phrest\API\Exceptions\HandledException;
use Phrest\API\Request\PhrestRequest;
use Phrest\API\Response\CSVResponse;
use Phrest\API\Response\JSONResponse;
use Phalcon\DI\FactoryDefault as DefaultDI;
use Phalcon\Exception;
use Phalcon\Mvc\Micro as MicroMVC;
use Phalcon\Http\Response as HttpResponse;
use Phrest\API\Response\Response;
use Phrest\API\Response\ResponseMessage;
use Phrest\SDK\PhrestSDK;
use Phuse\Framework\Module\Config\Config;

/**
 * Phalcon API Application
 */
class PhrestAPI extends MicroMVC
{
  /** @var  string */
  protected $srcDir;

  /** @var bool */
  public $isInternalRequest = false;

  /**
   * @param PhrestDIInterface $di
   * @param null|string       $srcDir
   */
  public function __construct(PhrestDIInterface $di, $srcDir = null)
  {
    if (!$srcDir)
    {
      $srcDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/src';
    }
    $this->srcDir = $srcDir;

    $di->set(
      'collections',
      function ()
      {
        return $this->getCollections();
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
        if (PhrestSDK::$method && PhrestSDK::$uri)
        {
          // Set exception message
          $message = sprintf(
            'Route not found: %s (via SDK) to %s',
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
            'Route not found: %s to %s',
            $request->getMethod(),
            $request->getURI()
          );
        }

        throw new HandledException($message, 404);
      }
    );

    // Mount all of the collections, which makes the routes active.
    foreach ($di->get('collections') as $collection)
    {
      $this->mount($collection);
    }

    $this->getResponseType();

    // Check unauthorized 401 access
    $this->before(
      function () use ($di)
      {
        // If the access is unauthorized:
        //throw new Exceptions\UnauthorizedException;
      }
    );

    // Send the response if required
    $this->after(
      function () use ($di)
      {
        // Internal request will return the response
        if ($this->isInternalRequest)
        {
          return;
        }

        $controllerResponse = $this->getReturnedValue();

        if (is_a($controllerResponse, 'Phrest\API\Responses\ResponseArray'))
        {
          /** @var $controllerResponse \Phrest\API\Response\ResponseArray */
          $controllerResponse->setCount($controllerResponse->getCount());
        }

        /** @var PhrestRequest $request */
        $request = $di->get('request');

        if ($request->isJSON() || !$request->getFormat())
        {
          $di->set('response', new JSONResponse($controllerResponse));
        }
        elseif ($request->isCSV())
        {
          $di->set('response', new CSVResponse($controllerResponse));
        }

        /** @var HttpResponse $response */
        $response = $di->get('response');
        $response->send();
      }
    );
  }

  /**
   * @return array
   * @throws \Exception
   */
  public function getCollections()
  {
    /** @var Config $collectionConfig */
    $collectionConfig = $this->getDI()->get('collectionConfig');
    $collections = [];
    if (!$collectionConfig)
    {
      return [];
    }
    foreach ($collectionConfig->getRequiredArray('versions') as $version => $entitys)
    {
      foreach ($entitys as $entityName => $entity)
      {
        $collection = new PhalconCollection();
        $collection->setPrefix(
          sprintf(
            '/%s/%s',
            strtolower($version),
            strtolower($entityName)
          ));

        $collection->setHandler(
          sprintf(
            '\\%s\\%s\\Controllers\\%s\\%sController',
            $collectionConfig->getRequiredString('namespace'),
            $version,
            $entityName,
            $entityName
          ));

        $collection->setLazy(true);

        foreach ($entity as $requestMethod => $actions)
        {
          foreach ($actions as $actionName => $action)
          {
            $validMethod = in_array(
              strtoupper($requestMethod),
              RequestMethodEnum::getConstants()
            );

            if (!$validMethod)
            {
              throw new \Exception(
                "Invalid request method in the config file: '{$requestMethod}'"
              );
            }
            $requestMethod = strtolower($requestMethod);

            $collection->$requestMethod(
              $action,
              $actionName
            );
          }
        }
        $collections[] = $collection;
      }
    }

    return $collections;
  }

  /**
   *
   */
  public function getResponseType()
  {
    if (!$this->isInternalRequest)
    {
      $extension = strtolower(pathinfo($_GET['_url'], PATHINFO_EXTENSION));
      if (!strlen($extension))
      {
        return;
      }

      if (in_array($extension, PhrestRequest::$responseFormats))
      {
        /** @var PhrestRequest $request */
        $request = $this->getDI()->get('request');
        $request->setFormat($extension);
      }

      $_GET['_url'] = str_replace('.' . $extension, '', $_GET['_url']);
    }
  }

  /**
   * If the application throws an HTTPException, respond correctly (json etc.)
   * todo this was not working as the try catch blocks in controllers
   * was catching the exception before it would be handled, need
   * to come back to this
   */
  public function setExceptionHandler(DiInterface $di)
  {
    set_exception_handler(
      function ($exception) use ($di)
      {
        /** @var $exception Exception */

        // Handled exceptions
        if (is_a($exception, 'Phrest\API\\Exceptions\\HandledException'))
        {
          $response = new Response();

          $response->setStatusCode(
            $exception->getCode(),
            $exception->getMessage()
          );

          $response->addMessage(
            $exception->getMessage(),
            ResponseMessage::TYPE_WARNING
          );

          return (new JSONResponse($response))->send();
        }
        else
        {
          $response = new Response();

          $response->setStatusCode(500, 'Internal Server Error');

          $response->addMessage(
            'Internal Server Error',
            ResponseMessage::TYPE_WARNING
          );

          (new JSONResponse($response))->send();
        }

        // Log the exception
        error_log($exception);
        error_log($exception->getTraceAsString());

        return true;
      }
    );
  }
}
