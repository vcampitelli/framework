<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Config
 * @since       2016-10-30
 */

namespace Core\Config;

/**
 * Represents a object from the configuration data
 */
class Object
{
    /**
     * Data
     *
     * @var array
     */
    protected $_data = [];
    
    /**
     * Pool of objects
     *
     * @var Object[]
     */
    protected $_arrPool = [];
    
    /**
     * Constructor
     *
     * @param array $data Initial data (optional)
     */
    public function __construct(array $data = null)
    {
        if (!empty($data)) {
            $this->_data = $data;
        }
    }
    
    /**
     * Magic getter for the properties
     *
     * @param  string $property Property name
     *
     * @return mixed            Value
     */
    public function __get($property)
    {
        return $this->get($property);
    }
    
    /**
     * Gets a the property
     *
     * @param  string $property Property name
     *
     * @return mixed            Value
     */
    public function get($property)
    {
        return (isset($this->_data[$property])) ? $this->_data[$property] : null;
    }
    
    /**
     * Magic call to get objects
     *
     * @param  string $property Desired property
     * @param  array  $args     Arguments (unused)
     *
     * @return Object
     */
    public function __call($property, $args)
    {
        if (!isset($this->_arrPool[$property])) {
            $this->_arrPool[$property] = new Object($this->get($property));
        }
        
        return $this->_arrPool[$property];
    }
}
