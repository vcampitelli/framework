<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Db
 * @since       2016-11-02
 */

namespace Core\Db\Sql;

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
    protected $_value = null;
    
    /**
     * Constructor
     *
     * @param string $value Expression value
     */
    public function __construct($value)
    {
        $this->_value = $value;
    }
    
    /**
     * Converts expression to string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->_value;
    }
}
