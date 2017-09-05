<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Db
 * @since       2016-11-02
 */

namespace Vcampitelli\Framework\Db\Sql;

/**
 * SQL expression
 */
class Expression
{
    /**
     * Expression value
     *
     * @var string
     */
    protected $value = null;

    /**
     * Constructor
     *
     * @param string $value Expression value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Converts expression to string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}
