<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Model
 * @since       2015-10-14
 */

namespace Core\Model;

/**
 * Abstract model
 * 
 * @abstract
 */
abstract class ModelAbstract
{
    /**
     * Magic call for getters and setters
     *
     * @throws \BadMethodCallException If it's an invalid property
     * 
     * @param  string $method Method name
     * @param  array  $args   Arguments
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        $prefix = \strtolower(\substr($method, 0, 3));
        $isGet = $prefix === 'get';
        
        if (($isGet) || ($prefix === 'set')) {
            $property = \substr($method, 3);
            $property[0] = \strtolower($property[0]);
            if ($property === 'id') {
                if (empty($this->_primary)) {
                    if ($isGet) {
                        return null;
                    } else {
                        // let the rest of the code throw an exception
                        $property = null;
                    }
                } else {
                    $property = \Core\Filter::camelCase($this->_primary);
                }
            }
            if ($property) {
                $property = "_{$property}";
                if (\property_exists($this, $property)) {
                    // get
                    if ($isGet) {
                        return $this->$property;
                    }
                    
                    // set
                    $this->$property = $args[0];
                    return $this;
                }
            }
        }
        
        throw new \BadMethodCallException("Invalid method: {$method}");
    }
    
    /**
     * Returns this model's primary key
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->_primary;
    }
}
