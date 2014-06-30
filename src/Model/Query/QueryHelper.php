<?php



namespace PhrestAPI\Model\Query;

use Phalcon\DI;

class QueryHelper {

  public static function prepareQuery($modelClassName, QueryOptions $options){
    $modelsManager = DI::getDefault()->get('modelsManager');
    $query = $modelsManager->createBuilder()->from($modelClassName);

    // Prepare for where
    $query->where('1 = 1');

    if($options->ids !== null)
    {
      // Get where
      $query->inWhere('id', $options->ids);
    }

    // Limit the query
    if($options->limit !== null)
    {
      $query->limit($options->limit);
    }

    // Offset the query
    if($options->offset !== null)
    {
      $query->offset($options->offset);
    }

    // Sort the query
    if($options->sortBy !== null)
    {
      $str = $options->sortBy;
      if($options->sortOrder !== null){
        $str .= ' ' . $options->sortOrder;
      }
      $query->orderBy($str);
    }

    return $query;
  }

}
