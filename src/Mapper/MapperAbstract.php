<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Mapper
 * @since       2015-10-14
 */

namespace Core\Mapper;

use Core\Model\ModelAbstract;
use Core\Db\Adapter;

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
    private $__db = null;
    
    /**
     * Property translations into database columns
     *
     * @static
     * @var array
     */
    private static $__arrMapping = [];
    
    /**
     * Constructor
     *
     * @param Adapter $db Database object
     */
    public function __construct(Adapter $db)
    {
        $this->__db = $db;
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
        $hasId = $model->getId();
        if ((!$hasId) && ((isset($arr['data_inclusao'])) || (\array_key_exists('data_inclusao', $arr)))) {
            $arr['data_inclusao'] = new \Core\Db\Sql\Expression('NOW()');
        }
        if ((isset($arr['data_alteracao'])) || (\array_key_exists('data_alteracao', $arr))) {
            $arr['data_alteracao'] = new \Core\Db\Sql\Expression('NOW()');
        }
        
        // If $model has an ID, appends ON DUPLICATE KEY to the SQL statement
        $arrOnUpdate = [];
        if ($hasId) {
            $primaryKey = $model->getPrimaryKey();
            foreach ($arr as $column => $value) {
                if (($column !== $primaryKey) && ($column !== 'data_inclusao')) {
                    $arrOnUpdate[] = $column;
                }
            }
        }
        
        // Executes statement
        $id = $this->getDb()->execute(
            new \Core\Db\Sql\Statement\Insert($this->_tableName, $arr, $arrOnUpdate)
        );
        
        // Updates model ID
        $model->setId($id);
        
        return $model;
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
     * @return array
     */
    public function fetchAll()
    {
        // Query
        $db = $this->getDb();
        $sql = \sprintf('SELECT * FROM %s', $db->quoteIdentifier($this->_tableName));
        $query = $db->query($sql);
        
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
        $sql = \sprintf('SELECT * FROM %s', $db->quoteIdentifier($this->_tableName));
        
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
        if (!isset(self::$__arrMapping[$class])) {
            self::$__arrMapping[$class] = [];
            $reflect = new \ReflectionClass($model);
            $arrProp = $reflect->getProperties(\ReflectionProperty::IS_PROTECTED);
            foreach ($arrProp as $prop) {
                $name = $prop->getName();
                if (($name[0] === '_') && ($name !== '_primary')) {
                    $name = \substr($name, 1);
                    $index = $name;
                    $index[0] = \strtoupper($index);
                    self::$__arrMapping[$class][$index] = \Core\Filter::uncamelCase($name, '_' /* $separator */);
                }
            }
        }
        
        return self::$__arrMapping[$class];
    }
    
    /**
     * Returns current Adapter object
     *
     * @return Adapter
     */
    public function getDb()
    {
        return $this->__db;
    }
}
