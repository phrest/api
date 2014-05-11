<?php
namespace PhrestAPI\Responses;

class JSONResponse extends Response
{
  protected $envelope = true;

  public function __construct()
  {
    parent::__construct();

    // Set headers
    $this->setContentType('application/json');
  }

  /**
   * Send the response
   *
   * @return \Phalcon\Http\ResponseInterface
   */
  public function send()
  {

    // Set content
    if(!$this->isHEAD)
    {
      $this->setJsonContent($this);
    }

    // Send content
    return parent::send();
  }

  public function sendException(\Exception $exception)
  {
    $this->setStatusCode($exception->getCode(), $exception->getMessage());

    $this->setJsonContent($this);

    return parent::send();
  }
}
