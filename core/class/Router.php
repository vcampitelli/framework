<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Config
 * @since       2016-10-30
 */

namespace Core;

/**
 * URL routing
 */
class Router
{
    /**
     * Application object
     *
     * @var Application
     */
    protected $_app = null;
    
    /**
     * Application URL
     *
     * @var string
     */
    protected $_baseUrl = '';
    
    /**
     * Constructor
     *
     * @param Application $app Application object
     */
    public function __construct(Application $app)
    {
        $this->_app = $app;
    }
    
    /**
     * Dispatches current route
     *
     * @return void
     */
    public function dispatch()
    {
        $url = $_SERVER['REQUEST_URI'];
        
        // Base URL
        $baseUrl = $this->getBaseUrl();
        if (!empty($baseUrl)) {
            $url = \substr($url, \strlen($baseUrl));
        }
        
        // Verifies that file exists
        $url = \str_replace('..', '', $url);
        $path = APPLICATION_PATH . "/app/scripts/{$url}.php";
        if (!\is_file($path)) {
            http_response_code(404);
            
            // @TODO: custom 404 page
            echo 'Página não encontrada';
            die(1);
        }
        
        $app = $this->getApplication();
        $container = $app->getContainer();
        include $path;
    }

    /**
     * Sets Application URL
     *
     * @param  string $baseUrl Base URL to be set
     *
     * @return self
     */
    public function setBaseUrl($baseUrl)
    {
        $this->_baseUrl = (string) $baseUrl;

        return $this;
    }

    /**
     * Returns Application URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    /**
     * Returns the application object
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->_app;
    }
}
