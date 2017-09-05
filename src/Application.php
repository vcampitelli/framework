<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Config
 * @since       2016-10-30
 */

namespace Vcampitelli\Framework;

use Vcampitelli\Framework\Http\Router;
use Vcampitelli\Framework\Http\Request;

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
    protected $config = null;

    /**
     * Container object
     *
     * @var Service\Container
     */
    protected $container = null;

    /**
     * Router object
     *
     * @var Router
     */
    protected $router = null;

    /**
     * Constructor
     *
     * @param Config\ConfigInterface $config Configuration object
     */
    public function __construct(Config\ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Bootstraps application
     *
     * @param Request $request Request object
     *
     * @return self
     */
    public function run(Request $request)
    {
        // Executes current route and dispatches its response
        $this->getRouter()->run();

        return $this;
    }

    /**
     * Gets the configuration object
     *
     * @return Config\ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns Container object
     *
     * @return Service\Container
     */
    public function getContainer()
    {
        if (!isset($this->container)) {
            $this->container = new Service\Container($this->getConfig());
        }
        return $this->container;
    }

    /**
     * Returns Router object
     *
     * @return Router
     */
    public function getRouter()
    {
        if ($this->router === null) {
            // Iniatilizing router
            $this->router = new Router($this);

            // Checks base URL
            $baseUrl = $this->getConfig()->app()->baseUrl;
            if (!empty($baseUrl)) {
                $this->router->setBaseUrl($baseUrl);
            }
        }

        return $this->router;
    }
}
