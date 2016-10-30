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
        
        $db = $this->getDb();
        
        // Builds INSERT
        $sql = \sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $db->quoteIdentifier($this->_tableName),
            \implode(', ', \array_keys($arr)),
            \rtrim(\str_repeat('?, ', \count($arr)), ', ')
        );
        
        // If $model has an ID, appends ON DUPLICATE KEY to the SQL statement
        if ($model->getId()) {
            $sql .= ' ON DUPLICATE KEY UPDATE ';
            $primaryKey = $model->getPrimaryKey();
            foreach ($arr as $column => $value) {
                if ($column !== $primaryKey) {
                    $column = $db->quoteIdentifier($column);
                    $sql .= "{$column} = VALUES({$column}), ";
                }
            }
            $sql = \rtrim($sql, ', ');
        }
        
        $stmt = $db->prepare($sql);
        if (!$stmt->execute(array_values($arr))) {
            throw new \RuntimeException(\sprintf('[%s] %s', $this->_tableName, \implode(' - ', $stmt->errorInfo())));
        }
        
        $model->setId($db->lastInsertId());
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
}
