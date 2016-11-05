<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Http
 * @since       2016-10-30
 */

namespace Core\Http;

use Core\Application;

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
     * Routing data
     *
     * @var array
     */
    protected $_data = [];
    
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
     * @param  Request $request Request object (optional)
     * 
     * @return void
     */
    public function dispatch(Request $request = null)
    {
        $url = $_SERVER['REQUEST_URI'];
        
        // Base URL
        $baseUrl = $this->getBaseUrl();
        if (!empty($baseUrl)) {
            $url = \substr($url, \strlen($baseUrl));
        }
        
        // Verifies that file exists
        $url = \str_replace('..', '', $url);
        $arr = parse_url($url);
        $route = null;
        if (!empty($arr['path'])) {
            $url = $arr['path'];
            if (!empty($this->_data)) {
                foreach ($this->_data as $row) {
                    if ((!empty($row['url'])) && ($url == $row['url'])) {
                        $route = $row;
                        break;
                    }
                }
            }
        }
        
        // Creates request object
        if ($request === null) {
            $request = Request::fromGlobals();
        }
        $response = Response::fromRequest($request);
        
        try {
            if ($route === null) {
                throw new \Exception('Route not found.');
            }
            
            if (empty($route['controller'])) {
                throw new \Exception('Controller not found.');
            }
            if (empty($route['action'])) {
                throw new \Exception('Action not found.');
            }
            
            if (!\is_subclass_of($route['controller'], '\Core\Controller\ControllerInterface')) {
                throw new \Exception('Invalid controller.');
            }
        } catch (\Exception $e) {
            return $response->withError($e->getMessage(), 404);
        }
        
        try {
            // Dispatchs request to controller
            $controller = new $route['controller']($this->getApplication()->getContainer());
            $return = $controller->{$route['action']}($request, $response);
            if (!$return instanceof Response) {
                $return = $response->withSuccess($return);
            }
            return $return;
        } catch (\Exception $e) {
            return $response->withError($e->getMessage(), 400);
        }
    }
    
    /**
     * Loads routing parameters
     *
     * @throws \DomainException If the router can't parse the parameter
     * 
     * @param  mixed $param Array or file path
     *
     * @return self
     */
    public function load($param)
    {
        // Is it a file?
        if (\is_file($param)) {
            $param = include $param;
        } elseif (!\is_array($param)) {
            throw new \DomainException('Couldn\'t load routes parameter');
        }
        
        // @TODO check data integrity
        $this->_data = $param;
        
        return $this;
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
