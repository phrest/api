<?php


namespace PhrestAPI\Collections;


class CollectionRoute
{
  const TYPE_GET = 'get';
  const TYPE_PUT = 'put';
  const TYPE_POST = 'post';
  const TYPE_DELETE = 'delete';

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
   * @return CollectionRoute
   */
  public static function get($routePattern, $controllerAction)
  {
    return new self(self::TYPE_GET, $routePattern, $controllerAction);
  }

  /**
   * Adds a POST
   *
   * @param $routePattern
   * @param $controllerAction
   * @return CollectionRoute
   */
  public static function post($routePattern, $controllerAction)
  {
    return new self(self::TYPE_POST, $routePattern, $controllerAction);
  }

  /**
   * Adds a PUT
   *
   * @param $routePattern
   * @param $controllerAction
   * @return CollectionRoute
   */
  public static function put($routePattern, $controllerAction)
  {
    return new self(self::TYPE_PUT, $routePattern, $controllerAction);
  }

  /**
   * Adds a DELETE
   *
   * @param $routePattern
   * @param $controllerAction
   * @return CollectionRoute
   */
  public static function delete($routePattern, $controllerAction)
  {
    return new self(self::TYPE_DELETE, $routePattern, $controllerAction);
  }
}
