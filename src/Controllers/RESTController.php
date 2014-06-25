<?php
namespace PhrestAPI\Controllers;

use Phalcon\DI;
use Phalcon\Exception;
use Phalcon\Mvc\Model;
use PhrestAPI\Exceptions\HTTPException;
use Phalcon\Mvc\Model\ResultsetInterface;
use PhrestAPI\Responses\CSVResponse;
use PhrestAPI\Responses\JSONResponse;
use PhrestAPI\Responses\Response;
use PhrestAPI\Responses\ResponseMessage;
use PhrestAPI\Request\PhrestRequest;
use WZCore\Filter\Filter;
use WZCore\Validate\Validate;

/**
 * Base RESTful Controller.
 * Supports queries with the following paramters:
 *   Searching:
 *     q=(searchField1:value1,searchField2:value2)
 *   Partial Responses:
 *     fields=(field1,field2,field3)
 *   Limits:
 *     limit=10
 *   Partials:
 *     offset=20
 *
 * @property \League\OAuth2\Server\Authorization $oauth2
 * @property Response $response
 * @property PhrestRequest $request
 */
class RESTController extends BaseController
{

  /**
   * If query string contains 'q' parameter.
   * This indicates the request is searching an entity
   * @var boolean
   */
  protected $isSearch = false;

  /**
   * If query contains 'fields' parameter.
   * This indicates the request wants back only certain fields from a record
   * @var boolean
   */
  protected $isPartial = false;

  /**
   *
   * If query contains an 'expand' parameter.
   * This indicates the request wants to expand a related entity
   * @var boolean
   */
  protected $isExpand = false;

  /**
   * Set when there is a 'limit' query parameter
   * @var integer
   */
  protected $limit;

  /**
   * Set when there is an 'offset' query parameter
   * @var integer
   */
  protected $offset;

  /**
   * Array of fields requested to be searched against
   * @var array
   */
  protected $searchFields;

  /**
   * Array of fields requested to be returned
   * @var array
   */
  protected $partialFields;

  /**
   * Expand related entities
   * @var null
   */
  protected $expandFields;

  /**
   * Sets which fields may be searched against, and which fields are allowed to be returned in
   * partial responses.  This will be overridden in child Controllers that support searching
   * and partial responses.
   * @var array
   * todo remove
   */
  protected $allowedFields
    = array(
      'search' => array(),
      'partials' => array()
    );

  protected $allowedPartialFields;

  /**
   * Constructor, calls the parse method for the query string by default.
   *
   * @param boolean $parseQueryString true Can be set to false if a controller needs to be called
   *        from a different controller, bypassing the $allowedFields parse
   * @throws \PhrestAPI\Exceptions\HTTPException
   * @return \PhrestAPI\Controllers\RESTController
   */
  public function __construct($parseQueryString = true)
  {
   // parent::__construct();
    if($parseQueryString)
    {
      $this->parseRequest($this->allowedFields);
    }
  }

  /**
   * Parses out the search parameters from a request.
   * Unparsed, they will look like this:
   *    (name:Benjamin Framklin,location:Philadelphia)
   * todo
   * Parsed:
   *     array('name'=>'Benjamin Franklin', 'location'=>'Philadelphia')
   * @param  string $unparsed Unparsed search string
   * @return array            An array of fieldname=>value search parameters
   */
  protected function parseSearchParameters($unparsed)
  {
    // todo
    return [];

    // Strip parens that come with the request string
    $unparsed = trim($unparsed, '()');

    // Now we have an array of "key:value" strings.
    $splitFields = explode(',', $unparsed);
    $mapped = array();

    // Split the strings at their colon, set left to key, and right to value.
    foreach($splitFields as $field)
    {
      $splitField = explode(':', $field);
      $mapped[$splitField[0]] = $splitField[1];
    }

    return $mapped;
  }

  protected function parseExpandedFields($unparsed)
  {
    return explode(',', trim($unparsed, '()'));
  }

  /**
   * Parses out partial fields to return in the response.
   * Unparsed:
   *     (id,name,location)
   * Parsed:
   *     array('id', 'name', 'location')
   * @param  string $unparsed Unparsed string of fields to return in partial response
   * @return array            Array of fields to return in partial response
   */
  protected function parsePartialFields($unparsed)
  {
    return explode(',', trim($unparsed, '()'));

    // todo - allow parsing of related models etc.
    $requestedFields = explode(',', trim($unparsed, '()'));

    $models = [];
    foreach($requestedFields as $key => $fieldName)
    {
      // Related model: emails.email,email.id etc.
      $dotPos = strpos($fieldName, '.');
      if($dotPos !== false)
      {
        $modelName = substr($fieldName, 0, $dotPos);
        $fieldName = substr($fieldName, $dotPos + 1);

        // Add to list of fields for related model
        if(isset($models[$modelName]))
        {
          $models[$modelName][] = $fieldName;
        }
        else
        {
          $models[$modelName] = [$fieldName];
        }
      }
      // Same model, fields only
      else
      {
        if(!isset($models['currentModel']))
        {
          $models['currentModel'] = [];
        }
        $models['currentModel'][] = $fieldName;
      }
    }

    return $models;
  }

  /**
   * Main method for parsing a query string.
   * Finds search paramters, partial response fields, limits, and offsets.
   * Sets Controller fields for these variables.
   *
   * @param  array $allowedFields Allowed fields array for search and partials
   * @throws \PhrestAPI\Exceptions\HTTPException
   * @return boolean              Always true if no exception is thrown
   */
  protected function parseRequest($allowedFields)
  {
    $request = $this->di->get('request');
    $method = $request->get('method', null, null);

    $di = DI::getDefault();
    $di->set('apiRequestMethod', $method);

    //echo $method; die;
    //var_dump($method); die;
    //$this->
    $searchParams = $request->get('q', null, null);
    $fields = $request->get('fields', null, null);
    $expandFields = $request->get('expand', null, null);

    // Set limits and offset, elsewise allow them to have defaults set in the Controller
    $this->limit = ($request->get('limit', null, null)) ? : $this->limit;
    $this->offset = ($request->get('offset', null, null)) ? : $this->offset;

    // If there's a 'q' parameter, parse the fields, then determine that all the fields in the search
    // are allowed to be searched from $allowedFields['search']
    if($searchParams)
    {
      $this->isSearch = true;
      $this->searchFields = $this->parseSearchParameters($searchParams);

      // This handly snippet determines if searchFields is a strict subset of allowedFields['search']
      if(array_diff(
        array_keys($this->searchFields),
        $this->allowedFields['search']
      )
      )
      {
        throw new HTTPException(
          "The fields you specified cannot be searched.",
          401,
          array(
            'dev' => 'You requested to search fields that are not available to be searched.',
            'internalCode' => 'S1000',
            'more' => '' // Could have link to documentation here.
          )
        );
      }
    }

    // Expanded fields
    if($expandFields)
    {
      $this->isExpand = true;
      $this->expandFields = $this->parseExpandedFields($expandFields);
    }

    // If there's a 'fields' paramter, this is a partial request.  Ensures all the requested fields
    // are allowed in partial responses.
    if($fields)
    {
      $this->isPartial = true;
      $this->partialFields = $this->parsePartialFields($fields);
    }

    return true;
  }

  /**
   * Provides a base CORS policy for routes like '/users' that represent a Resource's base url
   * Origin is allowed from all urls.  Setting it here using the Origin header from the request
   * allows multiple Origins to be served.  It is done this way instead of with a wildcard '*'
   * because wildcard requests are not supported when a request needs credentials.
   *
   * @return true
   */
  public function optionsBase()
  {
    $response = $this->di->get('response');
    $response->setHeader(
      'Access-Control-Allow-Methods',
      'GET, POST, OPTIONS, HEAD'
    );
    $response->setHeader(
      'Access-Control-Allow-Origin',
      $this->di->get('request')->header('Origin')
    );
    $response->setHeader('Access-Control-Allow-Credentials', 'true');
    $response->setHeader(
      'Access-Control-Allow-Headers',
      "origin, x-requested-with, content-type"
    );
    $response->setHeader('Access-Control-Max-Age', '86400');
    return true;
  }

  /**
   * Provides a CORS policy for routes like '/users/123' that represent a specific resource
   *
   * @return true
   */
  public function optionsOne()
  {
    $response = $this->di->get('response');
    $response->setHeader(
      'Access-Control-Allow-Methods',
      'GET, PUT, PATCH, DELETE, OPTIONS, HEAD'
    );
    $response->setHeader(
      'Access-Control-Allow-Origin',
      $this->di->get('request')->header('Origin')
    );
    $response->setHeader('Access-Control-Allow-Credentials', 'true');
    $response->setHeader(
      'Access-Control-Allow-Headers',
      "origin, x-requested-with, content-type"
    );
    $response->setHeader('Access-Control-Max-Age', '86400');
    return true;
  }

  /**
   * Respond with a single model, pass function name
   */
  protected function respondWithModel(Model $model, $functionName = null)
  {
    // Return a partial response
    if($functionName && isset($this->partialFields))
    {
      // Validate that there are fields set for this method
      if(!isset($this->allowedPartialFields[$functionName]))
      {
        throw new Exception(
          'Partial fields not specified for ' . $functionName
        );
      }

      // Determines if fields is a strict subset of allowed fields
      if(array_diff(
        $this->partialFields,
        $this->allowedPartialFields[$functionName]
      )
      )
      {
        // todo rework exception
        throw new HTTPException(
          "The fields you asked for cannot be returned.",
          401,
          array(
            'dev' => 'You requested to return fields that are not available to be returned in partial responses.',
            'internalCode' => 'P1000',
            'more' => '' // Could have link to documentation here.
          )
        );
      }
      $this->response->data = $model->toArray($this->partialFields);
    }
    // Get the whole record
    else
    {
      $this->response->data = (object)$model->toArray();
      $this->response->meta->count = count($this->response->data);
    }

    // Expand related models
    if($this->isExpand)
    {
      // todo allow for parsed related fields, model.field
      foreach($this->expandFields as $modelField)
      {
        //$this->response[$modelField] = $model->getRelated($modelField)->toArray();
      }
    }

    return $this->response;
  }

  /**
   * Respond with multiple models, a result set
   */
  protected function respondWithModels(ResultsetInterface $models)
  {

    if(count($models) == 0)
    {
      $this->response->data = [];
    }
    else
    {
      foreach($models as $model)
      {
        $this->response->data[] = (object)$model->toArray();
      }
      $this->response->meta->count = count($models);
    }

    return $this->response;
  }

  /**
   * Get a query based on the current request
   * Searching is custom logic
   *
   * @param $modelClassName
   *
   * @return Model\Query\BuilderInterface
   */
  protected function getQueryBuilder($modelClassName)
  {
    $query = $this->modelsManager->createBuilder()->from($modelClassName);

    // Prepare for where
    $query->where('1 = 1');

    // Get only certain IDs
    if($this->request->hasQuery('ids'))
    {
      // Filter input
      $ids = Filter::arrayOfInts($this->request->getQuery('ids'));

      // Validate input
      Validate::arrayHasValues($ids);

      // Get where
      $query->inWhere('id', $ids);
    }

    // Limit the query
    if($this->request->hasLimit())
    {
      $query->limit($this->request->getLimit());
    }

    // Offset the query
    if($this->request->hasOffset())
    {
      $query->offset($this->request->getOffset());
    }

    // Sort the query
    if($this->request->hasSortBy())
    {
      $query->orderBy(
        $this->request->getSortBy() . ' ' . $this->request->getSortOrder()
      );
    }



    return $query;
  }
}
