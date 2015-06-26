<?php

namespace Phrest\API\Request;

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
   * Get oAuth access token
   * todo tidy up, standardize
   *
   * @return mixed|null|string
   */
  public function getToken()
  {

    if ($this->has('token'))
    {
      return $this->get('token');
    }

    if ($this->has('access_token'))
    {
      return $this->get('access_token');
    }

    if ($this->has('accessToken'))
    {
      return $this->get('accessToken');
    }

    if (
    $authorization = $this->getHeader('AUTHORIZATION')
    )
    {
      return $authorization;
    }

    return null;
  }

  /**
   * Check if the request has a search query
   *
   * @return bool
   */
  public function isSearch()
  {
    return $this->hasQuery('q');
  }

  /**
   * Get the search query
   *
   * @throws \Exception
   * @return string
   */
  public function getSearchQuery()
  {
    if (!$this->isSearch())
    {
      throw new \Exception('Cannot get search query for non search request');
    }

    return $this->getQuery('q');
  }

  public function hasLimit()
  {
    return $this->hasQuery('limit');
  }

  public function hasOffset()
  {
    return $this->hasQuery('offset');
  }

  public function getLimit()
  {
    return (int)$this->getQuery('limit');
  }

  public function getOffset()
  {
    return (int)$this->getQuery('offset');
  }

  public function getSortOrder()
  {
    $possible = [
      'ASC',
      'DESC'
    ];

    $sortOrder = strtoupper($this->getQuery('sortOrder'));

    if (!in_array($sortOrder, $possible))
    {
      throw new \Exception("Invalid sort order " . $sortOrder);
    }

    return $sortOrder;
  }

  public function hasSortOrder()
  {
    return $this->has('sortOrder');
  }

  public function getSortBy()
  {
    return $this->getQuery('sortBy');
  }

  public function hasSortBy()
  {
    return $this->hasQuery('sortBy');
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
    if (!$this->isPartial())
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
    if (!$this->isExpand())
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
    if (isset($_GET['method']))
    {
      return $_GET['method'];
    }

    $method = parent::getMethod();

    return $method;
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
