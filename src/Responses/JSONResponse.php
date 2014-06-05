<?php
namespace PhrestAPI\Responses;

use Phalcon\Http\Response as HTTPResponse;
use PhrestAPI\Request\PhrestRequest;

class JSONResponse extends HTTPResponse
{
  protected $envelope = true;

  public $meta;
  public $messages;
  public $data;


  public function __construct(Response $response)
  {
    parent::__construct();


    $this->data = $response->getData();
    $this->meta = $response->getMeta();
    $this->messages = $response->getMessages();
  }

  /**
   * Send the response
   *
   * @return \Phalcon\Http\ResponseInterface
   */
  public function send()
  {
    // Set headers
    $this->setContentType('application/json');

    /** @var PhrestRequest $request */
    $request = $this->getDI()->get('request');

    // Set content
    if(!$request->isHead())
    {
      $this->setJsonContent($this);
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
