<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Http
 * @since       2016-11-02
 */

namespace Core\Http;

/**
 * Handles response
 */
class Response
{
    /**
     * Server configuration
     *
     * @var array
     */
    protected $_server = [];
    
    /**
     * If the current response should be sent as JSON
     *
     * @var boolean
     */
    protected $_isJson = false;
    
    /**
     * Constructor
     *
     * @param array $server Server configuration
     */
    public function __construct(array $server)
    {
        $this->_server = $server;
    }
    
    /**
     * Build a new Response object from the Request
     *
     * @static
     *
     * @param  Request $request Request object
     * 
     * @return Response
     */
    public static function fromRequest(Request $request)
    {
        $response = new Response($request->getServer());
        
        return $response;
    }
    
    /**
     * Returns a sucess message
     *
     * @param  string $message Message to be displayed
     *
     * @return self
     */
    public function withSuccess($message)
    {
        if (($this->isJson()) || ($this->isAjax())) {
            echo $this->json([
                'status' => true,
                'msg'    => $message
            ]);
        } else {
            // @FIXME: view rendering
            echo $message;
        }
        return $this;
    }
    
    /**
     * Returns an error response
     *
     * @param  string  $message Error message
     * @param  integer $status  HTTP status code
     *
     * @return self
     */
    public function withError($message, $status = 500)
    {
        \http_response_code($status);
        if (($this->isJson()) || ($this->isAjax())) {
            echo $this->json([
                'status' => false,
                'msg'    => $message
            ]);
        } else {
            // @FIXME: view rendering
            echo $message;
        }
        return $this;
    }
    
    /**
     * Checks if it's an AJAX request (probably)
     *
     * @return boolean
     */
    public function isAjax()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']))
            && (\strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }
    
    /**
     * Defines the current response as JSON
     *
     * @param  boolean $status Default: true
     *
     * @return self
     */
    public function asJson($status = true)
    {
        $this->_isJson = (bool) $status;
        return $this;
    }
    
    /**
     * Returns if the current response as JSON
     *
     * @return boolean
     */
    public function isJson()
    {
        return $this->_isJson;
    }
    
    /**
     * Returns a JSON object
     *
     * @param  array  $arr Data to be encoded
     *
     * @return string
     */
    public function json(array $arr)
    {
        \header('Content-type: application/json');
        return \json_encode($arr);
    }
}
