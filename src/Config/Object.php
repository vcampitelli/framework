<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Config
 * @since       2016-10-30
 */

namespace Vcampitelli\Framework\Config;

/**
 * Represents a object from the configuration data
 */
class Object implements ConfigInterface
{
    /**
     * Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Pool of objects
     *
     * @var Object[]
     */
    protected $arrPool = [];

    /**
     * Constructor
     *
     * @param array $data Initial data (optional)
     */
    public function __construct(array $data = null)
    {
        if (!empty($data)) {
            $this->data = $data;
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
        return (isset($this->data[$property])) ? $this->data[$property] : null;
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
        if (!isset($this->arrPool[$property])) {
            $this->arrPool[$property] = new Object($this->get($property));
        }

        return $this->arrPool[$property];
    }
}
