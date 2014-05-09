<?php


namespace PhalconAPI\Responses;

class RawResponse
{
  /** @var int */
  public $code = 200;

  /** @var ResponseMeta */
  public $meta;

  /** @var array */
  public $data;

  /** @var ResponseMessage[] */
  public $messages;

  public function __construct()
  {
    $this->meta = new ResponseMeta();
  }

  public function isSent()
  {
    return true;
  }
}
