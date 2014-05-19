<?php
namespace PhrestAPI\Responses;

class JSONResponse extends Response
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

    // Set content
    if(!$this->isHEAD)
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
