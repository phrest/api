<?php

namespace Phrest\API\Response;

class ResponseMeta
{
  /** @var int */
  public $statusCode = 200;

  /** @var string */
  public $statusMessage;

  /** @var int */
  public $count;
}
