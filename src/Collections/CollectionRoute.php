<?php


namespace PhrestAPI\Collections;

use PhrestAPI\Request\PhrestRequest;

class CollectionRoute
{
  public $type;
  public $routePattern;
  public $controllerAction;

  public function __construct($type, $routePattern, $controllerAction)
  {
    $this->type = $type;
    $this->routePattern = $routePattern;
    $this->controllerAction = $controllerAction;
  }

  /**
   * Adds a GET
   *
   * @param $routePattern
   * @param $controllerAction
   *
   * @return CollectionRoute
   */
  public static function get($routePattern, $controllerAction)
  {
    return new self(
      PhrestRequest::METHOD_GET, $routePattern, $controllerAction
    );
  }

  /**
   * Adds a POST
   *
   * @param $routePattern
   * @param $controllerAction
   *
   * @return CollectionRoute
   */
  public static function post($routePattern, $controllerAction)
  {
    return new self(PhrestRequest::METHOD_POST, $routePattern, $controllerAction);
  }

  /**
   * Adds a PUT
   *
   * @param $routePattern
   * @param $controllerAction
   *
   * @return CollectionRoute
   */
  public static function put($routePattern, $controllerAction)
  {
    return new self(PhrestRequest::METHOD_PUT, $routePattern, $controllerAction);
  }

  /**
   * Adds a DELETE
   *
   * @param $routePattern
   * @param $controllerAction
   *
   * @return CollectionRoute
   */
  public static function delete($routePattern, $controllerAction)
  {
    return new self(PhrestRequest::METHOD_DELETE, $routePattern, $controllerAction);
  }
}
