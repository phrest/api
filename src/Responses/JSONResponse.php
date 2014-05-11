<?php
namespace PhrestAPI\Responses;

class JSONResponse extends Response
{
  protected $envelope = true;

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
}
