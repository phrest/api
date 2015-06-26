<?php


namespace Phrest\API\Exceptions;

class UnauthorizedException extends HandledException
{
  protected $code = 401;
  protected $message = 'Unauthorized. Authentication is required and has failed or has not yet been provided.';
}
