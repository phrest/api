<?php


namespace Phrest\API\Collections;

class Collection
{
  /** @var string */
  public $controller;

  /** @var string */
  public $prefix;

  /** @var CollectionRoute[] */
  public $routes;
}
