<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Config
 * @since       2016-11-02
 */

namespace Vcampitelli\Framework\Config;

/**
 * Configuration interface
 */
interface ConfigInterface
{
    /**
     * Constructor
     *
     * @param array $data Initial data (optional)
     */
    public function __construct(array $data = null);

    /**
     * Magic getter for the properties
     *
     * @param  string $property Property name
     *
     * @return mixed            Value
     */
    public function __get($property);

    /**
     * Gets a the property
     *
     * @param  string $property Property name
     *
     * @return mixed            Value
     */
    public function get($property);

    /**
     * Magic call to get objects
     *
     * @param  string $property Desired property
     * @param  array  $args     Arguments (unused)
     *
     * @return Object
     */
    public function __call($property, $args);
}
