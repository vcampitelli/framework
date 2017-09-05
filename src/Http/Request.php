<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Http
 * @since       2016-11-02
 */

namespace Core\Http;

/**
 * Handles current request
 */
class Request
{
    /**
     * Server configuration
     *
     * @var array
     */
    protected $server = [];

    /**
     * GET data
     *
     * @var array
     */
    protected $get = [];

    /**
     * POST data
     *
     * @var array
     */
    protected $post = [];

    /**
     * Constructor
     *
     * @param array $server Server configuration
     * @param array $get    GET data
     * @param array $post   POST data
     */
    public function __construct(array $server, array $get = [], array $post = [])
    {
        $this->server = $server;
        $this->get    = $get;
        $this->post   = $post;
    }

    /**
     * Build a new Request object from global variables
     *
     * @static
     *
     * @return Request
     */
    public static function fromGlobals()
    {
        return new Request(
            $_SERVER,
            $_GET,
            $_POST
        );
    }

    /**
     * Returns the specified data from the request
     *
     * @param  string $property Property name
     * @param  mixed  $default  Default value (optional)
     *
     * @return mixed
     */
    public function get($property, $default = null)
    {
        if (isset($this->post[$property])) {
            return $this->post[$property];
        }
        if (isset($this->get[$property])) {
            return $this->get[$property];
        }

        return $default;
    }

    /**
     * Checks if it's an AJAX request (probably)
     *
     * @return boolean
     */
    public function isAjax()
    {
        return (!empty($this->server['HTTP_X_REQUESTED_WITH']))
            && (\strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * Returns the request attribute
     *
     * @param  string $attr Attribute name
     *
     * @return mixed
     */
    public function getAttr($attr)
    {
        return (isset($this->server[$attr])) ? $this->server[$attr] : null;
    }
}
