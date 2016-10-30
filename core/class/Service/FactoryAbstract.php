<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Service
 * @since       2015-10-12
 */

namespace Core\Service;

use Core\Config;

/**
 * Abstract factory
 *
 * @abstract
 */
abstract class FactoryAbstract
{
    /**
     * Object pool
     *
     * @var array
     */
    protected $_pool = [];
    
    /**
     * Configuration object
     *
     * @var Config
     */
    private $__config = null;

    /**
     * Constructor
     * 
     * @param Config $config Configuration object
     */
    public function __construct(Config $config)
    {
        $this->__config = $config;
    }
    
    /**
     * Gets a database connection from pool
     * 
     * @param  string $alias Connection alias
     *
     * @return Adapter
     */
    public function get($alias)
    {
        if (!isset($this->_pool[$alias])) {
            $this->_pool[$alias] = $this->build($alias);
        }
        return $this->_pool[$alias];
    }
    
    /**
     * Gets the configuration object
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->__config;
    }
    
    /**
     * Builds a new object
     *
     * @abstract
     * 
     * @param  string $alias Service type
     * 
     * @return object
     */
    abstract public function build($alias);
}
