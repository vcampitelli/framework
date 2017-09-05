<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Service
 * @since       2015-10-12
 */

namespace Vcampitelli\Framework\Service;

use Vcampitelli\Framework\Config\ConfigInterface;

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
    protected $pool = [];

    /**
     * Configuration object
     *
     * @var ConfigInterface
     */
    private $config = null;

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
     * Gets a database connection from pool
     *
     * @param  string $alias Connection alias
     *
     * @return Adapter
     */
    public function get($alias)
    {
        if (!isset($this->pool[$alias])) {
            $this->pool[$alias] = $this->build($alias);
        }
        return $this->pool[$alias];
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
