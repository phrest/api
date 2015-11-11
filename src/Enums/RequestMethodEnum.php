<?php

namespace Phrest\API\Enums;

class RequestMethodEnum extends AbstractEnum
{
  const OPTIONS = 'OPTIONS';
  const POST    = 'POST';
  const HEAD    = 'HEAD';
  const GET     = 'GET';
  const PUT     = 'PUT';
  const PATCH   = 'PATCH';
  const DELETE  = 'DELETE';
}
