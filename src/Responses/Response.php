<?php

namespace PhrestAPI\Responses;

use Phalcon\DI;

class Response
{

  /** @var ResponseMeta */
  protected $meta;

  /** @var ResponseMessage[] */
  protected $messages;

  /** @var bool Is a head request */
  protected $isHEAD = false;

  public function __construct()
  {
    $this->meta = new ResponseMeta();
  }

  /**
   * Called by Phalcon, todo see if can get rid of it
   */
  protected function isSent(){}

  /**
   * @return ResponseMessage[]
   */
  public function getMessages()
  {
    return $this->messages;
  }

  /**
   * @return ResponseMeta
   */
  public function getMeta()
  {
    return $this->meta;
  }

  /**
   * Set teh Response Count
   *
   * @param int $count
   *
   * @return $this
   */
  public function setCount($count)
  {
    $this->meta->count = (int)$count;

    return $this;
  }

  /**
   * Get the Response Count
   *
   * @return int
   */
  public function getCount()
  {
    return (int)$this->meta->count;
  }

  /**
   * Set the status code
   *
   * @param int    $code
   * @param string $message
   *
   * @return \Phalcon\Http\ResponseInterface
   */
  public function setStatusCode($code, $message)
  {
    $this->meta->statusCode = $code;
    $this->meta->statusMessage = $message;

    return parent::setStatusCode($code, $message);
  }

  /**
   * Get the status code
   *
   * @return int
   */
  public function getStatusCode()
  {
    return $this->meta->statusCode;
  }

  /**
   * Add a message to the response object
   *
   * @param        $text
   * @param string $type
   *
   * @return $this
   */
  public function addMessage($text, $type = ResponseMessage::TYPE_SUCCESS)
  {
    $this->messages[] = new ResponseMessage(
      $text,
      $type
    );

    return $this;
  }

  /**
   * Get the status message
   *
   * @return int
   */
  public function getStatusMessage()
  {
    return $this->meta->statusMessage;
  }

  /**
   * @param \Exception $exception
   *
   * @throws \Exception
   */
  public function sendException(\Exception $exception)
  {
    throw $exception;
  }

  /**
   * Get the object data (public properties)
   *
   * @return mixed
   */
  public function getData()
  {
    // todo return json_decode(json_encode($this)); may be quicker

    // Return public properties
    return call_user_func('get_object_vars', $this);
  }

}
