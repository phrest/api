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
   * Check if the request has a search
   * todo
   *
   * @return bool
   */
  public function isSearch()
  {
    return false;
  }

  /**
   * Get the search term/query
   * todo
   *
   * @throws \Exception
   * @return string
   */
  public function getSearchQuery()
  {
    if(!$this->isSearch())
    {
      throw new \Exception('Cannot get search query for non search request');
    }

    return 'foo';
  }

  /**
   * Check if the request is for pagination
   * todo
   *
   * @return bool
   */
  public function isPaginated()
  {
    return false;
  }

  /**
   * Get the pagination limit
   * todo
   *
   * @return int
   */
  public function getPaginationLimit()
  {
    return 0;
  }

  /**
   * Get the pagination offset
   * todo
   *
   * @return int
   */
  public function getPaginationOffset()
  {
    return 0;
  }

  /**
   * Check if the request is for a partial response
   * todo
   *
   * @return bool
   */
  public function isPartial()
  {
    return false;
  }

  /**
   * Get the partial fields
   * todo
   *
   * @throws \Exception
   * @return array
   */
  public function getPartialFields()
  {
    if(!$this->isPartial())
    {
      throw new \Exception('Cannot get partial fields for non partial request');
    }

    return [];
  }

  /**
   * Check if the request is for an expanded entities response
   * todo
   *
   * @return bool
   */
  public function isExpand()
  {
    return false;
  }

  /**
   * Get the expand entities
   * todo
   *
   * @return array
   * @throws \Exception
   */
  public function getExpandEntities()
  {
    if(!$this->isExpand())
    {
      throw new \Exception('Cannot get Expand Entities for non expand request');
    }

    return [];
  }

  /**
   * Get the HTTP Method
   *
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

  /**
   * Check if this is a JSON request
   * todo
   *
   * @return bool
   */
  public function isJSON()
  {
    return true;
  }

  /**
   * Check if this is a CSV request
   * todo
   *
   * @return bool
   */
  public function isCSV()
  {
    return false;
  }

  /**
   * Check if this is an internal request
   * todo
   *
   * @return bool
   */
  public function isInternal()
  {
    return false;
  }
}
