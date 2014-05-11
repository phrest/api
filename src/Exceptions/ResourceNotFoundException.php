<?php


namespace PhrestAPI\Exceptions;

class ResourceNotFoundException extends HandledException
{
  protected $code = 404;
  protected $message = 'The resource you requested could not be found';
}
