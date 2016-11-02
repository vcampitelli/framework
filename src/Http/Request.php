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
    protected $_server = [];
    
    /**
     * GET data
     *
     * @var array
     */
    protected $_get = [];
    
    /**
     * POST data
     *
     * @var array
     */
    protected $_post = [];
    
    /**
     * Constructor
     *
     * @param array $server Server configuration
     * @param array $get    GET data
     * @param array $post   POST data
     */
    public function __construct(array $server, array $get = [], array $post = [])
    {
        $this->_server = $server;
        $this->_get    = $get;
        $this->_post   = $post;
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
        if (isset($this->_post[$property])) {
            return $this->_post[$property];
        }
        if (isset($this->_get[$property])) {
            return $this->_get[$property];
        }
        
        return $default;
    }
    
    /**
     * Returns server configuration data
     *
     * @return array
     */
    public function getServer()
    {
        return $this->_server;
    }
}
