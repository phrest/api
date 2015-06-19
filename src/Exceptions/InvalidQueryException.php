<?php

namespace Phrest\API\Exceptions;

/**
 * InvalidQueryException
 * This exception should be throws in controller actions that require a
 * query type to be passed i.e. get=getExamplesWhereX
 */
class InvalidQueryException extends HandledException
{
  protected $code = 400;

  public function __construct(array $queryTypes)
  {
    $availableTypes = array_keys($queryTypes);
    $this->message = sprintf(
      'Invalid Query: %s, Available: (%s)',
      $_GET['get'],
      implode(',', $availableTypes)
    );
  }
}
