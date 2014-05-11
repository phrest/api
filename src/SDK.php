<?php


namespace PhrestAPI;

use Phalcon\Exception;
use PhrestAPI\Responses\Response;

/**
 * SDK for Phalcon REST API
 * This class can be used as a standalone client for HTTP based requests or
 * you can use it for internal API calls by calling API::setApp()
 */
class SDK
{
  const METHOD_GET = 'GET';
  const METHOD_POST = 'POST';
  const METHOD_PUT = 'PUT';
  const METHOD_DELETE = 'DELETE';

  /** @var API */
  private $app;

  /** @var string */
  private $url;

  /**
   * Set the API instance
   *
   * @param API $app
   * @return $this
   */
  public function setApp(API $app)
  {
    $this->app = $app;
    $this->app->isInternal = true;

    return $this;
  }

  /**
   * Set the URL of the API
   *
   * @param $url
   * @return $this
   */
  public function setURL($url)
  {
    $this->url = $url;

    return $this;
  }

  /**
   * Makes a GET call based on path/url
   * @param $path
   * @throws \Phalcon\Exception
   * @return Response
   */
  public function get($path)
  {
    // Get from the internal call if available
    if(isset($this->app))
    {
      return $this->getRawResponse($path);
    }

    // Get via HTTP (cURL) if available
    if(isset($this->url))
    {
      return $this->getHTTPResponse($path, self::METHOD_GET);
    }

    // todo better exception message with link
    throw new Exception(
      'No app configured for internal calls,
          and no URL supplied for HTTP based calls'
    );
  }

  private function getRawResponse($path)
  {
    // todo see if there is a better way that overriding $_REQUEST
    // Take a backup of the request array
    $request = $_REQUEST;

    // Override the request params
    if(isset($params) && count($params) > 0)
    {
      foreach($params as $key => $val)
      {
        $_REQUEST[$key] = $val;
      }
    }
    $_REQUEST['type'] = 'raw';

    $response = $this->app->handle($path);

    $_REQUEST = $request;
    return $response;
  }

  /**
   * Makes a POST call based on path/url
   * todo this is not complete
   * @param $path
   * @throws \Phalcon\Exception
   * @return Response
   */
  public function post($path)
  {
    // Get from the internal call if available
    if(isset($this->app))
    {
      return $this->app->handle($path);
    }

    // Get via HTTP (cURL) if available
    if(isset($this->url))
    {
      return $this->getHTTPResponse($path, self::METHOD_POST);
    }

    // todo better exception message with link
    throw new Exception(
      'No app configured for internal calls,
          and no URL supplied for HTTP based calls'
    );
  }

  /**
   * Makes a PUT call based on path/url
   * todo this is not complete
   * @param $path
   * @throws \Phalcon\Exception
   * @return Response
   */
  public function put($path)
  {
    // Get from the internal call if available
    if(isset($this->app))
    {
      return $this->app->handle($path);
    }

    // Get via HTTP (cURL) if available
    if(isset($this->url))
    {
      return $this->getHTTPResponse($path, self::METHOD_POST);
    }

    // todo better exception message with link
    throw new Exception(
      'No app configured for internal calls,
          and no URL supplied for HTTP based calls'
    );
  }

  /**
   * Makes a DELETE call based on path/url
   * todo this is not complete
   * @param $path
   * @throws \Phalcon\Exception
   * @return Response
   */
  public function delete($path)
  {
    // Get from the internal call if available
    if(isset($this->app))
    {
      return $this->app->handle($path);
    }

    // Get via HTTP (cURL) if available
    if(isset($this->url))
    {
      return $this->getHTTPResponse($path, self::METHOD_POST);
    }

    // todo better exception message with link
    throw new Exception(
      'No app configured for internal calls,
          and no URL supplied for HTTP based calls'
    );
  }

  /**
   * Makes a cURL HTTP request to the API and returns the response
   * todo this needs to also handle PUT, POST, DELETE
   * @param $path
   * @param string $method
   * @throws \Exception
   * @return string
   */
  private function getHTTPResponse($path, $method = self::METHOD_GET)
  {
    // Prepare curl
    $curl = curl_init($this->url . $path);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $curlResponse = curl_exec($curl);

    // Handle failed request
    if($curlResponse === false)
    {
      $info = curl_getinfo($curl);
      curl_close($curl);

      throw new \Exception('Transmission Error: ' . print_r($info, true));
    }

    // Return response
    curl_close($curl);
    return json_decode($curlResponse);
  }
}
