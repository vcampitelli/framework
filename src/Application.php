<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Config
 * @since       2016-10-30
 */

namespace Core;

use Core\Http\Router;

/**
 * Application bootstrap
 */
class Application
{
    /**
     * Configuration object
     *
     * @var Config\ConfigInterface
     */
    protected $_config = null;
    
    /**
     * Container object
     *
     * @var Service\Container
     */
    protected $_container = null;
    
    /**
     * Router object
     *
     * @var Router
     */
    protected $_router = null;
    
    /**
     * Constructor
     * 
     * @param Config\ConfigInterface $config Configuration object
     */
    public function __construct(Config\ConfigInterface $config)
    {
        $this->_config = $config;
    }
    
    /**
     * Bootstraps application
     *
     * @return self
     */
    public function run()
    {    
        // Dispatches current route
        $this->getRouter()->dispatch();
        
        return $this;
    }
    
    /**
     * Gets the configuration object
     *
     * @return Config\ConfigInterface
     */
    public function getConfig()
    {
        return $this->_config;
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
    
    /**
     * Returns Router object
     *
     * @return Router
     */
    public function getRouter()
    {
        if ($this->_router === null) {
            // Iniatilizing router
            $this->_router = new Router($this);
            
            // Checks base URL
            $baseUrl = $this->getConfig()->app()->baseUrl;
            if (!empty($baseUrl)) {
                $this->_router->setBaseUrl($baseUrl);
            }
        }
        
        return $this->_router;
    }
}
