<?php


namespace PhalconAPI\Responses;

class ResponseStub
{
  /** @var ResponseMetaStub */
  public $meta;
  public $response;

  /** @var ResponseMessageStub[] */
  public $messages;
}

class ResponseMetaStub
{
  /** @var int */
  public $status;

  /** @var int */
  public $count;
}

class ResponseMessageStub
{
  const TYPE_SUCCESS = 'success';
  const TYPE_WARNING = 'warning';
  const TYPE_INFO = 'info';
  const TYPE_ALERT = 'alert';
  const TYPE_SECONDARY = 'secondary';

  /** @var string */
  public $message;

  /** @var  */
  public $type;
}
