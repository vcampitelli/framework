<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Service
 * @since       2016-10-30
 */

namespace Core\Service;

use Core\Config;

/**
 * Application service manager
 */
class Container
{
    /**
     * Configuration object
     *
     * @var Config
     */
    private $__config = null;
    
    /**
     * Object pool
     *
     * @var array
     */
    protected $_pool = [];
    
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
     * Builds a new object
     *
     * @throws \InvalidArgumentException If the class doesn't exist
     * 
     * @param  string $class Class name
     *
     * @return object
     */
    public function get($class)
    {
        // Checks if we already built this class before
        if (!isset($this->_pool[$class])) {
            // Checks if class exists
            if (!\class_exists($class)) {
                throw new \InvalidArgumentException("Não foi possível encontrar a classe {$class}");
            }
            
            if (\is_subclass_of($class, \Core\Mapper\MapperAbstract::class)) {
                $factory = new \Core\Db\Factory($this->getConfig());

                $this->_pool[$class] = $factory->build($class::CONNECTION);
            } else {
                $this->_pool[$class] = new $class();
            }
        }
        return $this->_pool[$class];
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
}
