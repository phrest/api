<?php



namespace PhrestAPI\Model\Query;

use Phalcon\DI;
use Phalcon\Mvc\Model\Query\Builder;

class QueryHelper {

  public static function prepareQuery($modelClassName, QueryOptions $options){
    $modelsManager = DI::getDefault()->get('modelsManager');
    /** @var Builder $query */
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

    if($options->isDeleted !== null){
      if($options->isDeleted)
      {
        $query->andWhere(
          'deleted = :deleted:',
          ['deleted' => 'NULL']
        );
      }
      else
      {
        $query->andWhere(
          'deleted != :deleted:',
          ['deleted' => 'NULL']
        );
      }
    }

    return $query;
  }

}
