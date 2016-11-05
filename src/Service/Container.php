<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Service
 * @since       2016-10-30
 */

namespace Core\Service;

use Core\Config\ConfigInterface;

/**
 * Application service manager
 */
class Container implements ContainerInterface
{
    /**
     * Configuration object
     *
     * @var ConfigInterface
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
     * @param ConfigInterface $config Configuration object
     */
    public function __construct(ConfigInterface $config)
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
            
            if (\is_subclass_of($class, '\Core\Mapper\MapperAbstract')) {
                $factory = new \Core\Db\Factory($this->getConfig());
                $db = $factory->build($class::CONNECTION);

                $this->_pool[$class] = new $class($db);
            } else {
                $this->_pool[$class] = new $class();
            }
        }
        return $this->_pool[$class];
    }
    
    /**
     * Gets the configuration object
     *
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->__config;
    }
}
