<?php

namespace Phrest\API\Response;

use Phalcon\DI;

class ResponseArray extends Response
{
  /** @var Response[] */
  private $responses = [];

  /**
   * Add a Response
   *
   * @param Response $response
   *
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
   * @param          $key
   * @param Response $response
   *
   * @return $this
   */
  public function addResponseWithKey($key, Response $response)
  {
    $this->responses[$key] = $response;

    return $this;
  }

  /**
   * Use getResponses
   *
   * @return Response[]
   * @deprecated
   */
  public function getData()
  {
    return $this->responses;
  }

  /**
   * Get the responses
   *
   * @return Response[]
   */
  public function getResponses()
  {
    return $this->responses;
  }

  /**
   * Get a response by array key
   *
   * @param $key
   *
   * @return null|Response
   */
  public function getResponseByKey($key)
  {
    return isset($this->responses[$key]) ? $this->responses[$key] : null;
  }

  /**
   * Set the responses array
   *
   * @param array $responses
   *
   * @return $this
   */
  public function setResponses(array $responses)
  {
    $this->responses = $responses;

    return $this;
  }

  /**
   * Get the count of responses
   * Count the responses if not already set
   *
   * @return int
   */
  public function getCount()
  {
    if (isset($this->meta->count))
    {
      return $this->meta->count;
    }

    return $this->meta->count = count($this->responses);
  }
}
