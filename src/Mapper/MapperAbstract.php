<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Mapper
 * @since       2015-10-14
 */

namespace Vcampitelli\Framework\Mapper;

use Vcampitelli\Framework\Model\ModelAbstract;
use Vcampitelli\Framework\Db\Adapter;
use Vcampitelli\Framework\Db\Sql\Expression;
use Vcampitelli\Framework\Filter;

/**
 * Abstract mapper
 *
 * @abstract
 */
abstract class MapperAbstract
{
    /**
     * Default connection name
     *
     * @var string
     */
    const CONNECTION = 'default';

    /**
     * Database connection
     *
     * @var App_Db_Adapter
     */
    private $db = null;

    /**
     * Property translations into database columns
     *
     * @static
     * @var array
     */
    private static $arrMapping = [];

    /**
     * Constructor
     *
     * @param Adapter $db Database object
     */
    public function __construct(Adapter $db)
    {
        $this->db = $db;
    }

    /**
     * Saves a record
     *
     * @param  ModelAbstract $model Model to save
     *
     * @return boolean|int               Record ID on success or false otherwise
     */
    public function save(ModelAbstract $model)
    {
        // Gets model property -> database column mapping
        $arr = [];
        foreach (self::getModelMapping($model) as $property => $column) {
            $method = "get{$property}";
            $arr[$column] = $model->$method();
        }

        // Default fields
        $arr = $this->parseDefaultFields($model, $arr);

        // If $model has an ID, appends ON DUPLICATE KEY to the SQL statement
        $arrOnUpdate = [];
        if ($model->getId()) {
            $primaryKey = $model->getPrimaryKey();
            foreach ($arr as $column => $value) {
                if (($column !== $primaryKey) && ($column !== 'data_inclusao')) {
                    $arrOnUpdate[] = $column;
                }
            }
        }

        // Executes statement
        $id = $this->getDb()->execute(
            new \Vcampitelli\Framework\Db\Sql\Statement\Insert(static::TABLENAME, $arr, $arrOnUpdate)
        );

        // Updates model ID
        $model->setId($id);

        return $model;
    }

    /**
     * Parses default model fields
     *
     * @param  ModelAbstract $model Model
     * @param  array         $arr   Data to be loaded
     *
     * @return array        New data
     */
    protected function parseDefaultFields(ModelAbstract $model, array $arr)
    {
        if ((!$model->getId()) && ((isset($arr['data_inclusao'])) || (\array_key_exists('data_inclusao', $arr)))) {
            $arr['data_inclusao'] = new Expression('NOW()');
        }
        if ((isset($arr['data_alteracao'])) || (\array_key_exists('data_alteracao', $arr))) {
            $arr['data_alteracao'] = new Expression('NOW()');
        }

        return $arr;
    }

    /**
     * Loads data into model
     *
     * @param  ModelAbstract $model Model
     * @param  array              $data  Data to be loaded
     *
     * @return ModelAbstract
     */
    public function load(ModelAbstract $model, array $data)
    {
        $arrMapping = self::getModelMapping($model);
        if (!empty($arrMapping)) {
            $arrMapping = \array_flip($arrMapping);
            foreach ($arrMapping as $field => $property) {
                // To use isset() performance
                if ((isset($data[$field])) && (\array_key_exists($field, $data))) {
                    $method = "set{$property}";
                    $model->$method($data[$field]);
                }
            }
        }

        return $model;
    }

    /**
     * Fetchs all data from table
     *
     * @param  array $where WHERE clauses (optional)
     *
     * @return array
     */
    public function fetchAll(array $where = null)
    {
        // Query
        $query = $this->buildQuery($where);

        // Iterates over data
        $arr = [];
        $class = static::MODEL;
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            // Indexes by primary key
            $model = new $class($row);
            $arr[$model->getId()] = $model;
        }
        return $arr;
    }

    /**
     * Builds a query from the specified values
     *
     * @param array  $where WHERE clauses (optional)
     *
     * @return \PDOStatement
     */
    protected function buildQuery(array $where = null)
    {
        $sql = \sprintf('SELECT * FROM %s', $this->getDb()->quoteIdentifier(static::TABLENAME));

        if (empty($where)) {
            return $this->getDb()->query($sql);
        }

        $arrWhere = $arrBind = [];
        foreach ($where as $key => $value) {
            if (\is_numeric($key)) {
                $arrWhere[] = $value;
                continue;
            }

            if (\is_array($value)) {
                $arrWhere[] = \str_replace('?', \rtrim(\str_repeat('?,', \count($value)), ','), $key);
                $arrBind = \array_merge($arrBind, $value);
                continue;
            }

            $arrWhere[] = $key;
            $arrBind[] = $value;
        }
        $sql .= ' WHERE ' . \implode(' AND ', $arrWhere);

        $query = $this->getDb()->prepare($sql);
        $query->execute($arrBind);

        return $query;
    }

    /**
     * Finds a row optionally filtered by $where caluses
     *
     * @param  array $where WHERE clauses
     *
     * @return ModelAbstract
     */
    public function fetchRow(array $where = null)
    {
        // Query
        $db = $this->getDb();
        $sql = \sprintf('SELECT * FROM %s', $db->quoteIdentifier(static::TABLENAME));

        // WHERE
        if (!empty($where)) {
            $arr = [];
            foreach ($where as $key => $value) {
                $arr[] = $db->quoteIdentifier($key) . ' = ' . $db->quote($value);
            }
            $sql .= ' WHERE ' . \implode(' AND ', $arr);
        }
        $sql .= ' LIMIT 1';

        // Executes statement
        $query = $db->query($sql);
        if ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $class = static::MODEL;
            return new $class($row);
        }

        return null;
    }

    /**
     * Finds a record by its id
     *
     * @param  mixed $id ID
     *
     * @return ModelAbstract
     */
    public function find($id)
    {
        $model = static::MODEL;
        return $this->fetchRow([
            $model::PRIMARY => $id
        ]);
    }

    /**
     * Returns a model translation from object properties to database columns
     *
     * @param  ModelAbstract $model Model to translate
     *
     * @return array
     */
    public static function getModelMapping(ModelAbstract $model)
    {
        $class = get_class($model);
        if (!isset(self::$arrMapping[$class])) {
            self::$arrMapping[$class] = [];
            $reflect = new \ReflectionClass($model);
            $arrProp = $reflect->getProperties(\ReflectionProperty::IS_PROTECTED);
            foreach ($arrProp as $prop) {
                $name = $prop->getName();
                $index = $name;
                $index[0] = \strtoupper($index);
                self::$arrMapping[$class][$index] = Filter::uncamelCase($name, '_' /* $separator */);
            }
        }

        return self::$arrMapping[$class];
    }

    /**
     * Returns current Adapter object
     *
     * @return Adapter
     */
    public function getDb()
    {
        return $this->db;
    }
}
