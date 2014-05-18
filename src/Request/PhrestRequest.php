<?php


namespace PhrestAPI\Request;


use Phalcon\Http\Request;

class PhrestRequest extends Request
{
  const METHOD_OPTIONS = 'OPTIONS';
  const METHOD_POST = 'POST';
  const METHOD_HEAD = 'HEAD';
  const METHOD_GET = 'GET';
  const METHOD_PUT = 'PUT';
  const METHOD_PATCH = 'PATCH';
  const METHOD_DELETE = 'DELETE';

  /**
   * @return string
   */
  public function getMethod()
  {
    if(isset($_GET['method']))
    {
      return $_GET['method'];
    }

    return parent::getMethod();
  }
}
