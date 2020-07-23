<?php
namespace Phrest\API\Response;

use Phalcon\Http\Response as HTTPResponse;
use Phrest\API\Enums\AbstractEnum;
use Phrest\API\Request\PhrestRequest;

class JSONResponse extends HTTPResponse
{
  /** @var bool */
  protected $envelope = true;

  /** @var ResponseMeta */
  public $meta;

  /** @var ResponseMessage[] */
  public $messages;

  /** @var array */
  public $data;

  /**
   * @param Response $response
   */
  public function __construct(Response $response)
  {
    parent::__construct();

    $this->setStatusCode(
      $response->getStatusCode(),
      $response->getStatusMessage()
    );

    $this->data = $this->getDataFromResponse($response);
    $this->meta = $response->getMeta();
    $this->messages = $response->getMessages();
  }

  /**
   * @param Response $response
   *
   * @return mixed|Response[]
   */
  private function getDataFromResponse(Response $response)
  {
    if ($response instanceof ResponseArray)
    {
      return $response->getResponses();
    }
    else
    {
      // Return public properties
      $data = call_user_func('get_object_vars', $response);

      if (count($data) > 0)
      {
        array_walk_recursive(
          $data,
          function (&$value)
          {
            if ($value instanceof AbstractEnum)
            {
              /** @var $value AbstractEnum */
              $value = $value->getValue();
            }
            elseif ($value instanceof Response)
            {
              $value = $this->getDataFromResponse($value);
            }
            elseif ($value instanceof ResponseArray)
            {
              $value = $value->getResponses();
            }
          }
        );
      }

      return $data;
    }
  }

  /**
   * Send the response
   *
   * @return \Phalcon\Http\ResponseInterface
   */
  public function send(): \Phalcon\Http\ResponseInterface
  {
    // Set headers
    $this->setContentType('application/json');

    /** @var PhrestRequest $request */
    $request = $this->getDI()->get('request');

    // Set content
    if (!$request->isHead())
    {
      if (empty($this->messages))
      {
        $this->messages = null;
      }
      if (empty($this->data))
      {
        $this->data = null;
      }

      if ($request->isPretty())
      {
        $this->setContent(json_encode($this, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK));
      }
      else
      {
        $this->setContent(json_encode($this, JSON_NUMERIC_CHECK));
      }
    }

    // Send content
    return parent::send();
  }

  /**
   * @param \Exception $exception
   *
   * @return \Phalcon\Http\ResponseInterface|void
   */
  public function sendException(\Exception $exception)
  {
    $this->setStatusCode($exception->getCode(), $exception->getMessage());

    $this->setJsonContent($this);

    return parent::send();
  }
}
