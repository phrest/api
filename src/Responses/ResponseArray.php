<?php

namespace PhrestAPI\Responses;

use Phalcon\DI;

class ResponseArray extends Response
{
  private $responses = [];

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
   * Add a Response
   *
   * @param Response $response
   * @return $this
   */
  public function addResponseWithKey($key, Response $response)
  {
    $this->responses[$key] = $response;

    return $this;
  }

  /**
   * Return the responses array as data
   *
   * @return Response[]
   */
  public function getData()
  {
    return $this->responses;
  }

  /**
   * Get the count of responses
   * Count the responses if not already set
   *
   * @return int
   */
  public function getCount()
  {
    if(isset($this->meta->count))
    {
      return $this->meta->count;
    }

    return $this->meta->count = count($this->responses);
  }
}
