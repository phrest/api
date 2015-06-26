<?php

namespace Phrest\API\Exceptions;

class BadRequestException extends HandledException
{
  protected $code = 400;
  protected $message = 'The Request was invalid';
}
