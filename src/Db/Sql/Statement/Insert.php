<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Db
 * @since       2016-11-02
 */

namespace Core\Db\Sql\Statement;

use Core\Db\Adapter;
use Core\Db\Sql\Expression;

/**
 * Insert expression
 */
class Insert implements StatementInterface
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_table = null;
    
    /**
     * Data to be inserted
     *
     * @var array
     */
    protected $_data = [];
    
    /**
     * ON UPDATE clause
     *
     * @var array
     */
    protected $_onUpdate = [];
    
    /**
     * Constructor
     *
     * @param string $table       Table name
     * @param array  $arr         Data to be inserted
     * @param array  $arrOnUpdate ON UPDATE clause (optional)
     */
    public function __construct($table, array $arr, array $arrOnUpdate = null)
    {
        $this->_table = $table;
        $this->_data = $arr;
        $this->_onUpdate = $arrOnUpdate;
    }
    
    /**
     * Executes statement
     *
     * @throws \RuntimeException If no data was specified
     * 
     * @param  Adapter $db DB adapter
     *
     * @return int Last inserted ID
     */
    public function execute(Adapter $db)
    {
        // Checks input data
        if (empty($this->_data)) {
            throw new \RuntimeException('No insert data was specified.');
        }
        
        // Initializing query
        $sql = \sprintf(
            'INSERT INTO %s (%s) VALUES (',
            $db->quoteIdentifier($this->_table),
            \implode(', ', \array_keys($this->_data)),
            \rtrim(\str_repeat('?, ', \count($this->_data)), ', ')
        );
        
        // Placeholders
        $arrColumn = $arrValue = [];
        foreach ($this->_data as $key => $value) {
            if ($value instanceof Expression) {
                $arrColumn[] = $value;
            } else {
                $arrColumn[] = '?';
                $arrValue[] = $value;
            }
        }
        $sql .= \implode(', ', $arrColumn) . ')';
        
        // Checks ON DUPLICATE KEY
        if (!empty($this->_onUpdate)) {
            $sql .= ' ON DUPLICATE KEY UPDATE ';
            foreach ($this->_onUpdate as $column => $value) {
                if (\is_numeric($column)) {
                    $column = $db->quoteIdentifier($value);
                    $sql .= "{$column} = VALUES({$column}), ";
                } else {
                    $sql .= $db->quoteIdentifier($column) . ' = ' . $db->quote($value) . ', ';
                }
            }
            $sql = \rtrim($sql, ', ');
        }
        
        // Executes statement
        $stmt = $db->prepare($sql);
        if (!$stmt->execute($arrValue)) {
            throw new \RuntimeException(\sprintf('[%s] %s', $this->_table, \implode(' - ', $stmt->errorInfo())));
        }
        
        return $db->lastInsertId();
    }
}
