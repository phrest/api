<?php
namespace PhrestAPI\Model\Query;

class QueryOptions {
  public $limit;
  public $offset;
  public $ids;
  public $sortBy;
  public $sortOrder;

  private function __construct()
  {
  }

  /**
   * @return QueryOptions
   */
  public static function create() {
    return new QueryOptions();
  }

  /**
   * @param $limit
   *
   * @return $this
   */
  public function setLimit($limit) {
    $this->limit = $limit;
    return $this;
  }

  /**
   * @param $offset
   *
   * @return $this
   */
  public function setOffset($offset){
    $this->offset = $offset;
    return $this;
  }

  /**
   * @param $ids
   *
   * @return $this
   */
  public function filterByIds($ids){
    $this->ids = $ids;
    return $this;
  }

  /**
   * @param $sortBy
   *
   * @return $this
   */
  public function sortBy($sortBy){
    $this->sortBy = $sortBy;
    return $this;
  }

  /**
   * @param $sortOrder
   *
   * @return $this
   */
  public function sortOrder($sortOrder){
    $this->sortOrder = $sortOrder;
    return $this;
  }

}
