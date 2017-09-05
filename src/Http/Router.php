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
    protected $app = null;

    /**
     * Application URL
     *
     * @var string
     */
    protected $baseUrl = '';

    /**
     * Routing data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @param Application $app Application object
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Process the request
     *
     * @param Request $request Request object
     *
     * @return Response\ResponseAbstract
     */
    public function run(Request $request)
    {
        $url = $request->getAttr('REQUEST_URI');

        // Base URL
        $baseUrl = $this->getBaseUrl();
        if (!empty($baseUrl)) {
            $url = \substr($url, \strlen($baseUrl));
        }

        try {
            // Verifies that route exists
            $route = $this->getRouteByUrl($url);
            if (!$route) {
                throw new \Exception('Route not found.', 404);
            }

            $response = $this->buildResponse($route['controller'], $route['action'], $request);
        } catch (\Exception $e) {
            $response = Response\ResponseAbstract::fromRequest($request);
            $response->withError($e->getMessage(), ($e->getCode()) ?: 500);
        }

        if ($response instanceof Response\ViewResponse) {
            $response->dispatch($route['controller'], $route['action']);
            return $response;
        }

        $response->dispatch();
        return $response;
    }

    /**
     * Returns the corresponding route for the specified URL
     *
     * @param  string $url URL to be matched against
     *
     * @return array Route data
     */
    protected function getRouteByUrl($url)
    {
        $url = \str_replace('..', '', $url);
        $arr = parse_url($url);

        if (empty($arr['path'])) {
            return false;
        }

        $url = $arr['path'];
        if (empty($this->data)) {
            return false;
        }

        foreach ($this->data as $row) {
            if ((!empty($row['url'])) && ($url == $row['url'])) {
                return $row;
            }
        }

        return false;
    }

    /**
     * Dispatches current route and returns the related response object
     *
     * @param  Request $request Request object (optional)
     *
     * @return ResponseAbstract
     */
    protected function buildResponse($controller, $action, Request $request)
    {
        // 404 error
        if (empty($controller)) {
            throw new \Exception('Controller not found.', 404);
        }
        if (empty($action)) {
            throw new \Exception('Action not found.', 404);
        }
        if (!\is_subclass_of($controller, '\Core\Controller\ControllerInterface')) {
            throw new \Exception('Invalid controller.', 404);
        }

        // Dispatchs request to controller
        $controller = new $controller($this->getApplication()->getContainer());
        $return = $controller->{$action}($request);
        if ($return instanceof Response\ResponseAbstract) {
            return $return;
        }

        // Default response
        $response = Response\ResponseAbstract::fromRequest($request);
        return $response->withSuccess($return);
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
        $this->data = $param;

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
        $this->baseUrl = (string) $baseUrl;

        return $this;
    }

    /**
     * Returns Application URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Returns the application object
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->app;
    }
}
