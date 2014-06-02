<?php

namespace PhrestAPI\Responses;

use Phalcon\DI;

class ResponseArray extends Response
{
  public $responses = [];

  /**
   * Add a Response
   *
   * @param Response $response
   * @return $this
   */
  public function addResponse(Response $response)
  {
    $this->responses[] = $response;

    return $this;
  }

  /**
   * @return Response[]
   */
  public function getData()
  {
    return $this->responses;
  }
}
