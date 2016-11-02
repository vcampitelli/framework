<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Service
 * @since       2016-11-31
 */

namespace Core\Service;

use Core\Config\ConfigInterface;

/**
 * Application service manager
 */
interface ContainerInterface
{
    /**
     * Constructor
     * 
     * @param ConfigInterface $config Configuration object
     */
    public function __construct(ConfigInterface $config);
    
    /**
     * Builds a new object
     *
     * @param  string $class Class name
     *
     * @return object
     */
    public function get($class);
    
    /**
     * Gets the configuration object
     *
     * @return ConfigInterface
     */
    public function getConfig();
}
