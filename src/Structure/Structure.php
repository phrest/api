<?php

namespace Phrest\API\Structure;

use Phrest\API\PhrestAPI;
use Phrest\API\Structure\Collection;
use Phrest\API\Structure\Collection\Call;
use Phrest\API\Structure\Collection\Call\Method;
use Phrest\API\Collections\Collection as PhCollection;
use Phrest\API\Collections\CollectionRoute;
use Phrest\API\Responses\Response;
use Zend\Code\Generator\DocBlock\Tag;
use Phalcon\Annotations\Adapter\Memory as AnnotationReader;

class Structure
{

  const CLASS_TYPE_REQUEST = 'Request';

  const DOC_ACTION_DESCRIPTION = 'description';
  const DOC_ACTION_METHOD_PARAM = 'methodParam';
  const DOC_ACTION_POST_PARAM = 'postParam';
  const DOC_ACTION_URI = 'uri';
  const DOC_ACTION_RESPONSE = 'response';

  public $api;

  public $collections;

  public function __construct(PhrestAPI $api)
  {
    $this->api = $api;
  }

  private function buildStructure($collections)
  {
    $structureCollections = array();
    foreach($collections as $c)
    {
      $collection = new Collection();
      $collRouteArray = explode("/", $c->prefix);
      $collection->name = ucfirst($collRouteArray[2]);
      $calls = array();
      foreach($c->routes as $route)
      {
        $call = new Call();
        $call->route = $c->prefix . $route->routePattern;
        $call->name = ucfirst($route->controllerAction);
        $call->httpMethod = new Method();
        $call->httpMethod->type = $route->type;
        $call->httpMethod->methodParams = $this->getActionMethodParams(
          $c,
          $route
        );
        $call->httpMethod->postParams = $this->getActionPostParams($c, $route);
        $call->response = $this->getActionResponse($c, $route);
        array_push($calls, $call);
      }
      $collection->calls = $calls;
      array_push($structureCollections, $collection);
    }
    return $structureCollections;
  }

  /**
   * Get the structure
   *
   * @return $this
   */
  public function getCollections()
  {
    if(!$this->collections)
    {
      $this->collections = $this->buildStructure($this->api->getCollections());
    }
    return $this->collections;
  }

  /**
   * @param PhCollection    $collection
   * @param CollectionRoute $route
   *
   * @return array|bool
   */
  public function getActionMethodParams(
    PhCollection $collection,
    CollectionRoute $route
  )
  {
    return $this->getActionAnnotations(
      $collection,
      $route,
      self::DOC_ACTION_METHOD_PARAM
    );
  }

  /**
   * Get action post parameters
   *
   * @param PhCollection    $collection
   * @param CollectionRoute $route
   *
   * @return array|bool
   */
  public function getActionPostParams(
    PhCollection $collection,
    CollectionRoute $route
  )
  {
    return $this->getActionAnnotations(
      $collection,
      $route,
      self::DOC_ACTION_POST_PARAM
    );
  }

  /**
   * @param PhCollection    $collection
   * @param CollectionRoute $route
   *
   * @return bool|string
   */
  private function getActionResponse(
    PhCollection $collection,
    CollectionRoute $route
  )
  {
    $response = $this->getActionAnnotation(
      $collection,
      $route,
      self::DOC_ACTION_RESPONSE
    );

    if(!$response)
    {
      // Default value
      return '\\' . Response::class;
    }

    return $response;
  }

  /**
   * Get an action annotation
   *
   * @param PhCollection    $collection
   * @param CollectionRoute $route
   * @param                 $annotationType
   *
   * @return string|bool
   */
  private function getActionAnnotation(
    PhCollection $collection,
    CollectionRoute $route,
    $annotationType
  )
  {
    $annotations = $this->getActionAnnotations(
      $collection,
      $route,
      $annotationType
    );

    if(!$annotations)
    {
      return false;
    }

    return $annotations[0];
  }

  /**
   * Get an array of action annotations by type
   *
   * @param PhCollection    $collection
   * @param CollectionRoute $route
   * @param                 $annotationType
   *
   * @return array|bool
   */
  private function getActionAnnotations(
    PhCollection $collection,
    CollectionRoute $route,
    $annotationType
  )
  {
    $classReader = $this->getClassAnnotationReader($collection->controller);
    $methodParams = $classReader->getMethodsAnnotations();

    if(!isset($methodParams[$route->controllerAction]))
    {
      return false;
    }

    try
    {
      $annotations = [];
      $actionAnnotations = $methodParams[$route->controllerAction]->getAll(
        $annotationType
      );

      foreach($actionAnnotations as $annotation)
      {
        $annotations[] = $annotation->getArgument(0);
      }
      return $annotations;
    }
    catch(\Exception $e)
    {
      return false;
    }
  }

  /**
   * Get Annotation Reader for class
   *
   * @param $class
   *
   * @return \Phalcon\Annotations\Reflection
   */
  private function getClassAnnotationReader($class)
  {
    return (new AnnotationReader())->get($class);
  }
}
