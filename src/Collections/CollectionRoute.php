<?php


namespace PhrestAPI\Collections;


class CollectionRoute
{
  const TYPE_GET = 'get';
  const TYPE_PUT = 'put';
  const TYPE_POST = 'post';
  const TYPE_DELETE = 'delete';

  public $type;
  public $route;
  public $action;

  public function __construct($type, $route, $action)
  {
    $this->type = $type;
    $this->route = $route;
    $this->action = $action;
  }

  /**
   * Adds a GET
   *
   * @param $route
   * @param $action
   * @return CollectionRoute
   */
  public static function get($route, $action)
  {
    return new self(self::TYPE_GET, $route, $action);
  }

  /**
   * Adds a POST
   *
   * @param $route
   * @param $action
   * @return CollectionRoute
   */
  public static function post($route, $action)
  {
    return new self(self::TYPE_POST, $route, $action);
  }

  /**
   * Adds a PUT
   *
   * @param $route
   * @param $action
   * @return CollectionRoute
   */
  public static function put($route, $action)
  {
    return new self(self::TYPE_PUT, $route, $action);
  }

  /**
   * Adds a DELETE
   *
   * @param $route
   * @param $action
   * @return CollectionRoute
   */
  public static function delete($route, $action)
  {
    return new self(self::TYPE_DELETE, $route, $action);
  }
}
