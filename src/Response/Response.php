<?php

namespace Phrest\API\Response;

use Phalcon\DI;
use Phrest\API\Enums\AbstractEnum;

class Response
{

  /** @var ResponseMeta */
  protected $meta;

  /** @var ResponseMessage[] */
  protected $messages;

  /** @var bool Is a head request */
  protected $isHEAD = false;

  /**
   *
   */
  public function __construct()
  {
    $this->meta = new ResponseMeta();
  }

  /**
   * Called by Phalcon, todo see if can get rid of it
   */
  protected function isSent() { }

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
    // Return public properties
    $dataVars = call_user_func('get_object_vars', $this);

    if (count($dataVars) > 0)
    {
      array_walk_recursive(
        $dataVars,
        function (&$value)
        {
          if (is_a($value, '\Phrest\API\Enums\AbstractEnum'))
          {
            /** @var $value AbstractEnum */
            $value = $value->getValue();
          }
          elseif ($value instanceof \Phrest\API\Response\ResponseArray)
          {
            $value = $value->getData();
          }
        }
      );
    }

    return $dataVars;
  }
}
