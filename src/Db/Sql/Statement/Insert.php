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
    protected $table = null;

    /**
     * Data to be inserted
     *
     * @var array
     */
    protected $data = [];

    /**
     * ON UPDATE clause
     *
     * @var array
     */
    protected $onUpdate = [];

    /**
     * Constructor
     *
     * @param string $table       Table name
     * @param array  $arr         Data to be inserted
     * @param array  $arrOnUpdate ON UPDATE clause (optional)
     */
    public function __construct($table, array $arr, array $arrOnUpdate = null)
    {
        $this->table = $table;
        $this->data = $arr;
        $this->onUpdate = $arrOnUpdate;
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
        if (empty($this->data)) {
            throw new \RuntimeException('No insert data was specified.');
        }

        // Initializing query
        $sql = \sprintf(
            'INSERT INTO %s (%s) VALUES (',
            $db->quoteIdentifier($this->table),
            \implode(', ', \array_keys($this->data)),
            \rtrim(\str_repeat('?, ', \count($this->data)), ', ')
        );

        // Placeholders
        $arrColumn = $arrValue = [];
        foreach ($this->data as $value) {
            if ($value instanceof Expression) {
                $arrColumn[] = $value;
                continue;
            }

            $arrColumn[] = '?';
            $arrValue[] = $value;
        }
        $sql .= \implode(', ', $arrColumn) . ')';

        // Checks ON DUPLICATE KEY
        if (!empty($this->onUpdate)) {
            $sql .= ' ON DUPLICATE KEY UPDATE ';
            foreach ($this->onUpdate as $column => $value) {
                if (\is_numeric($column)) {
                    $column = $db->quoteIdentifier($value);
                    $sql .= "{$column} = VALUES({$column}), ";
                    continue;
                }

                $sql .= $db->quoteIdentifier($column) . ' = ' . $db->quote($value) . ', ';
            }
            $sql = \rtrim($sql, ', ');
        }

        // Executes statement
        $stmt = $db->prepare($sql);
        if (!$stmt->execute($arrValue)) {
            throw new \RuntimeException(\sprintf('[%s] %s', $this->table, \implode(' - ', $stmt->errorInfo())));
        }

        return $db->lastInsertId();
    }
}
