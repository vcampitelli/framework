<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Config
 * @since       2016-10-30
 */

namespace Core;

/**
 * Application bootstrap
 */
class Application
{
    /**
     * Configuration object
     *
     * @var Config
     */
    private $__config = null;
    
    /**
     * Container object
     *
     * @var Service\Container
     */
    protected $_container = null;
    
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
     * Bootstraps application
     *
     * @return self
     */
    public function run()
    {
        $router = new Router($this);
        
        // Checks base URL
        $baseUrl = $this->getConfig()->app()->baseUrl;
        if (!empty($baseUrl)) {
            $router->setBaseUrl($baseUrl);
        }
        
        // Dispatches current route
        $router->dispatch();
        
        return $this;
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
     * Returns Container object
     *
     * @return Service\Container
     */
    public function getContainer()
    {
        if (!isset($this->_container)) {
            $this->_container = new Service\Container($this->getConfig());
        }
        return $this->_container;
    }
}
