<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Service
 * @since       2016-10-30
 */

namespace Vcampitelli\Framework\Service;

use Vcampitelli\Framework\Config\ConfigInterface;

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
    private $config = null;

    /**
     * Object pool
     *
     * @var array
     */
    protected $pool = [];

    /**
     * Constructor
     *
     * @param ConfigInterface $config Configuration object
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
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
        if (!isset($this->pool[$class])) {
            // Checks if class exists
            if (!\class_exists($class)) {
                throw new \InvalidArgumentException("Não foi possível encontrar a classe {$class}");
            }

            if (\is_subclass_of($class, '\Core\Mapper\MapperAbstract')) {
                $factory = new \Core\Db\Factory($this->getConfig());
                $db = $factory->build($class::CONNECTION);

                $this->pool[$class] = new $class($db);
            } else {
                $this->pool[$class] = new $class();
            }
        }
        return $this->pool[$class];
    }

    /**
     * Gets the configuration object
     *
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }
}
