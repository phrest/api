<?php


namespace PhrestAPI\Responses;

use Phalcon\DI;
use Phalcon\Http\Response as PhalconResponse;
use PhrestAPI\API;

class Response extends PhalconResponse
{
  const TYPE_RAW = 'raw';
  const TYPE_JSON = 'json';
  const TYPE_CSV = 'csv';

  /** @var ResponseMeta */
  public $meta;

  /** @var array */
  public $data;

  /** @var ResponseMessage[] */
  public $messages;

  /** @var bool Is a head request */
  protected $isHEAD = false;

  public function __construct()
  {
    // Prepare required response data
    $this->meta = new ResponseMeta();

    // Set this object in the DI container
    // todo might not need this
    //$di = DI::getDefault();
    //$this->setDI($di);
    //if(strtolower($di->get('request')->getMethod()) === API::METHOD_HEAD)
    {
      // $this->isHEAD = true;
    }
  }

  /**
   * Set the status code
   *
   * @param int $code
   * @param string $message
   * @return \Phalcon\Http\ResponseInterface
   */
  public function setStatusCode($code, $message)
  {
    $this->meta->statusCode = $code;
    $this->meta->statusMessage = $message;

    return parent::setStatusCode($code, $message);
  }

  /**
   * Add a message to the response object
   * @param $text
   * @param string $type
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
   * @param \Exception $exception
   * @throws \Exception
   */
  public function sendException(\Exception $exception)
  {
    throw $exception;
  }
}
