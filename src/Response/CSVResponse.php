<?php
namespace Phrest\API\Response;

use Phalcon\Http\Response as HTTPResponse;

class CSVResponse extends HTTPResponse
{
  /** @var bool * */
  protected $headers = true;

  /** @var array **/
  protected $records = [];

  /**
   * @param Response $controllerResponse
   */
  public function __construct($controllerResponse)
  {
    if ($controllerResponse instanceof ResponseArray)
    {
      foreach($controllerResponse->getResponses() as $response)
      {
        $this->records[] = $response->getData();
      }
    }
    elseif ($controllerResponse instanceof Response)
    {
      $this->records[] = $controllerResponse->getData();
    }

    parent::__construct();
  }

  /**
   * @return $this
   */
  public function send()
  {
    $response = $this->getDI()->get('response');
    // Headers for a CSV
    $response->setHeader('Content-type', 'application/csv');

    // By default, filename is just a timestamp. You should probably change this.
    $response->setHeader(
      'Content-Disposition',
      'attachment; filename="' . time() . '.csv"'
    );
    $response->setHeader('Pragma', 'no-cache');
    $response->setHeader('Expires', '0');

    // We write directly to out, which means we don't ever save this file to disk.
    $handle = fopen('php://output', 'w');

    // The keys of the first result record will be the first line of the CSV (headers)
    if ($this->headers)
    {
      fputcsv($handle, array_keys($this->records[0]));
    }

    // Write each record as a csv line.
    foreach ($this->records as $line)
    {
      fputcsv($handle, $line);
    }

    fclose($handle);

    return $this;
  }

  /**
   * @param bool $headers
   *
   * @return $this
   */
  public function useHeaderRow($headers)
  {
    $this->headers = (bool)$headers;

    return $this;
  }

}
