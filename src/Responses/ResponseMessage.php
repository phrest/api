<?php


namespace PhrestAPI\Responses;


class ResponseMessage
{
  const TYPE_SUCCESS = 'success';
  const TYPE_WARNING = 'warning';
  const TYPE_INFO = 'info';
  const TYPE_ALERT = 'alert';
  const TYPE_SECONDARY = 'secondary';

  public $text;
  public $type;

  public function __construct($text, $type = self::TYPE_INFO)
  {
    $this->text = $text;
    $this->type = $type;
  }
}
