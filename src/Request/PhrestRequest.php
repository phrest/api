<?php

namespace Phrest\API\Request;

use Phalcon\Http\Request;

class PhrestRequest extends Request
{
  /**
   * @var array
   */
  public static $responseFormats
    = [
      'json',
      'csv',
    ];

  /**
   * @var bool|string
   */
  protected $format = false;

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

    if ($authorization = $this->getHeader('AUTHORIZATION'))
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

  /**
   * @return bool
   */
  public function hasLimit()
  {
    return $this->hasQuery('limit');
  }

  /**
   * @return bool
   */
  public function hasOffset()
  {
    return $this->hasQuery('offset');
  }

  /**
   * @return bool
   */
  public function getLimit()
  {
    return (int)$this->getQuery('limit');
  }

  /**
   * @return bool
   */
  public function getOffset()
  {
    return (int)$this->getQuery('offset');
  }

  /**
   * @return String
   * @throws \Exception
   */
  public function getSortOrder()
  {
    $possible = [
      'ASC',
      'DESC',
    ];

    $sortOrder = strtoupper($this->getQuery('sortOrder'));

    if (!in_array($sortOrder, $possible))
    {
      throw new \Exception("Invalid sort order " . $sortOrder);
    }

    return $sortOrder;
  }

  /**
   * @return bool
   */
  public function hasSortOrder()
  {
    return $this->has('sortOrder');
  }

  /**
   * @return string
   */
  public function getSortBy()
  {
    return $this->getQuery('sortBy');
  }

  /**
   * @return bool
   */
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
  public function getRequestMethod()
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
    if ($this->format == 'json')
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Check if this is a CSV request
   * todo
   *
   * @return bool
   */
  public function isCSV()
  {
    if ($this->format == 'csv')
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * @return bool
   */
  public function isPretty()
  {
    return $this->hasQuery('pretty');
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

  /**
   * @param string $format
   *
   * @return PhrestRequest
   */
  public function setFormat($format)
  {
    $this->format = $format;

    return $this;
  }

  /**
   * @return bool|string
   */
  public function getFormat()
  {
    return $this->format;
  }
}
