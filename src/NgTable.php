<?php

namespace Hopeter1018\AngularjsPostbackValidator;

class NgTable
{
    /** record per page @var int */
    public $count = 20;
    /**  array('fieldname'=>'searchvalue', 'fieldname2'=>'searchvalue2')
     * @var array */
    public $filter;
    /** @var int*/
    public $page;
    /** @var array array('name'=>'asc') */
    public $sorting;
    /** @var \Doctrine\ORM\QueryBuilder */
    private $dql;
    /** @var array */
    private $debugMsg;
    /** @var int */
    private $firstResult = 0;

    private $extra = null;

    /**
     * Parse $_GET to properties
     * @throws \Exception
     */
    public function __construct()
    {
        if (isset($_GET['count']) and isset($_GET['page'])) {
            $this->count = (int) $_GET['count'];
            $this->page = (int) $_GET['page'];
            $this->sorting = $_GET['sorting'];
            $this->extra = array();
            if (isset($_GET['filter']) and is_array($_GET['filter'])) {
                $this->filter = $_GET['filter'];
            }
            if (isset($_GET['sorting']) and is_array($_GET['sorting'])) {
                $this->sorting = $_GET['sorting'];
            }
        } else {
            throw new \Exception('Not a ngTable request');
        }
    }

    CONST FILTERTYPE_LIKE = 'LIKE';
    CONST FILTERTYPE_EQUAL = '=';
    CONST FILTERTYPE_LTE = '<=';
    CONST FILTERTYPE_LT = '<';
    CONST FILTERTYPE_GTE = '>=';
    CONST FILTERTYPE_GT = '>';

    CONST VALUETYPE_DATE = 'date';

    /**
     * Shortcut to create and return the new instances<br />
     * 
     * @return \self
     */
    public static function init()
    {
        return new self();
    }

    /**
     * Build the where string 
     * and bind parameter
     * 
     * @param string $field the DQL field (alias.field)
     * @param string $type self::FILTERTYPE_*
     * @param string $value the posted value
     * @param string $get the field from url
     */
    private function dqlFilterType($field, $type, $value, $get)
    {
        $searchField = $get . \Zms5\Helpers\StringHelper::randomString(8);
        $this->dql->setParameter($searchField, $value);
        APP_IS_DEV and $this->debugMsg[] = "{$field} {$type} :{$searchField} {$value}";
        switch($type) {
            case static::FILTERTYPE_GT:
            case static::FILTERTYPE_GTE:
            case static::FILTERTYPE_LT:
            case static::FILTERTYPE_LTE:
            case static::FILTERTYPE_EQUAL:
                $this->dql->andWhere("{$field} {$type} :{$searchField}");
                break;
            default :
                $this->dql->andWhere("{$field} LIKE CONCAT('%', :{$searchField}, '%')");
                break;
        }
    }

    /**
     * Apply the filter from $_GET
     * 
     * @param array $filterMapping mapping of post field(s) => filter field(s)
     * @throws \Exception
     */
    private function applyDqlFilter($filterMapping, $skipFilter)
    {
        if (is_array($this->filter) and ($skipFilter === false or is_array($skipFilter))) {
            foreach ($this->filter as $field => $value) {
                if ($skipFilter !== false and in_array($field, $skipFilter)) {
                    continue;
                }
                if (is_array($filterMapping[$field])) {
                    if (isset($filterMapping[$field][2]) and $filterMapping[$field][2] === static::VALUETYPE_DATE) {
                        $carbon = new \Carbon\Carbon($value . '+8 hours');
                        $value = $carbon->format(APP_MYSQL_DATE);
                    }

                    if ($filterMapping[$field][1] === \PDO::PARAM_INT) {
                        $this->dqlFilterType($filterMapping[$field][0], static::FILTERTYPE_EQUAL, $value, $field);
                    } elseif ($filterMapping[$field][1] === static::FILTERTYPE_GT
                        || $filterMapping[$field][1] === static::FILTERTYPE_GTE
                        || $filterMapping[$field][1] === static::FILTERTYPE_LT
                        || $filterMapping[$field][1] === static::FILTERTYPE_LTE
                        || $filterMapping[$field][1] === static::FILTERTYPE_EQUAL
                    ) {
                        $this->dqlFilterType($filterMapping[$field][0], $filterMapping[$field][1], $value, $field);
                    }
                } elseif (isset($filterMapping[$field])) {
                    $this->dqlFilterType($filterMapping[$field], static::FILTERTYPE_LIKE, $value, $field);
                } else {
                    throw new \Exception('Wrong filter passed: ' . var_export($filterMapping, true) . var_export($this->filter, true));
                }
            }
        }
    }

    /**
     * 
     * @param array $sortingMapping mapping of post field(s) => sort field(s)
     * @return type
     */
    private function applyDqlSorting($sortingMapping)
    {
        foreach ($this->sorting as $field => $value) {
            if (isset($sortingMapping[$field])) {
                $this->dql->orderBy("{$sortingMapping[$field]}", $value);
            } else {
                throw new \Exception('Wrong sorting passed: ' . var_export($this->sorting, true));
            }
        }
    }

    /**
     * Build up the debug message
     * @return array
     */
    private function buildDebugResponse()
    {
        $debugArray = array();
        if (APP_IS_DEV) {
            $params = array();
            foreach ($this->dql->getParameters() as $parameter) {
                /* @var $parameter \Doctrine\ORM\Query\Parameter */
                $params[ $parameter->getName()] = $parameter->getValue();
            }
            $debugArray['debug'] = array(
                'message' => $this->debugMsg,
                'sql' => $this->dql->select()
                    ->getQuery()->getSQL(),
                'param' => $params,
                'request' => $_GET,
            );
        }
        return $debugArray;
    }

    private function getTotal()
    {
        $dqlStr = $this->dql->getQuery()->getDQL();
        $stmt = \Zms5\Common\DoctrineBase::conn()->prepare("SELECT count(*) AS total FROM ({$this->dql->getQuery()->getSQL()}) data");
        $params = $this->dql->getQuery()->getParameters();
        $orderParam = array();
        foreach ($params as $param) {
            /* @var $param \Doctrine\ORM\Query\Parameter */
            $orderParam[ strpos($dqlStr, ":{$param->getName()}") ] = $param;
        }
        ksort($orderParam);
        $orderParamSorted = array_values($orderParam);
        foreach ($orderParamSorted as $index => $param) {
            $stmt->bindValue(1 + $index, $param->getValue(), $param->getType());
        }
        $stmt->execute();
        $row = $stmt->fetch();
        return (int) $row['total'];
    }

    /**
     * 
     * @param type $msg
     * @return type
     */
    public static function returnEmpty($msg = 'empty')
    {
        return array (
            200,
            'total' => 0,
            'result'=> array(),
            'msg' => $msg,
        );
    }

    public function addExtra($name, $value)
    {
        $this->extra[$name] = $value;
        return $this;
    }

    /**
     * @todo skipSorting
     * @param \Doctrine\ORM\QueryBuilder $dql the join DQL
     * @param array $mappings mapping of post field(s) => sort field(s)
     * @param string $select the "select" of the DQL
     * @param boolean|array $skipFilter
     * @param boolean|array $skipSorting
     * @param string $groupBy
     */
    public function applyDqlSearch($dql, $mappings, $select = 't', $skipFilter = false, $skipSorting = false, $groupBy= null)
    {
        try {
            $this->dql = $dql;
            $this->applyDqlFilter($mappings, $skipFilter);
            if ($groupBy !== null) {
                $this->dql->groupBy($groupBy);
            }

            $total = $this->getTotal();

            $this->applyDqlSorting($mappings, $skipSorting);

            $this->firstResult = ($this->page - 1) * $this->count;

            return array (
                200,
                'total' => $total,
                'result' => $dql->select($select)
                    ->setFirstResult($this->firstResult)
                    ->setMaxResults($this->count)
                    ->getQuery()->getArrayResult(),
            ) + $this->buildDebugResponse()
            + $this->extra;
        } catch (\Exception $ex) {
            return array(
                500,
                "{$ex->getFile()}",
                "{$ex->getLine()}",
                "{$ex->getMessage()}",
                $this->debugMsg,
            );
        }
    }

}