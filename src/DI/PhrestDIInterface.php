<?php

namespace Phrest\API\DI;

use Phalcon\DiInterface;
use Phalcon\Http\RequestInterface;
use Phalcon\Mvc\RouterInterface;
use Phuse\Framework\Module\Config\Config;

interface PhrestDIInterface extends DiInterface
{
  /**
   * @param $config
   */
  public function setCollectionConfig(Config $config);

  /**
   * @param RequestInterface $request
   */
  public function setRequest(RequestInterface $request);

  /**
   * @param RouterInterface $router
   */
  public function setRouter(RouterInterface $router);
}
