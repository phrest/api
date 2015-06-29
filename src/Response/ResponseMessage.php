<?php

namespace Phrest\API\Response;

class ResponseMessage
{
  const TYPE_SUCCESS = 'success';
  const TYPE_WARNING = 'warning';
  const TYPE_INFO = 'info';
  const TYPE_ALERT = 'alert';
  const TYPE_SECONDARY = 'secondary';
  const TYPE_ERROR = 'error';

  public $text;
  public $type;

  public function __construct($text, $type = self::TYPE_INFO)
  {
    $this->text = $text;
    $this->type = $type;
  }
}
