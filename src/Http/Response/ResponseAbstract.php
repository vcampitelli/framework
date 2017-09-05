<?php
/**
 * @author     Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package    Core
 * @subpackage Http
 * @since      2016-11-02
 */

namespace Vcampitelli\Framework\Http\Response;

use Vcampitelli\Framework\Http\Request;

/**
 * Handles response
 *
 * @abstract
 */
abstract class ResponseAbstract
{
    /**
     * The related Request object
     *
     * @var Request
     */
    protected $request = null;

    /**
     * Constructor
     *
     * @param  Request $request Request object
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
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
        if ($request->isAjax()) {
            return new DataResponse($request);
        }

        return new ViewResponse($request);
    }

    /**
     * Returns a success response
     *
     * @param  mixed $data Data to be dispatched
     *
     * @return self
     */
    public function withSuccess($data)
    {
        $response = $this->doSuccess($data);
        return ($response instanceof ResponseAbstract) ? $response : $this;
    }

    /**
     * Returns an error response
     *
     * @param  mixed   $data Error data to be dispatched
     * @param  integer $status  HTTP status code
     *
     * @return self
     */
    public function withError($data, $status = 500)
    {
        // HTTP code
        $status = (int) $status;
        if (($status < 400) || ($status > 500)) {
            $status = 500;
        }
        \http_response_code($status);

        // Response
        $response = $this->doError($data, $status);
        return ($response instanceof ResponseAbstract) ? $response : $this;
    }

    /**
     * Returns a success response
     *
     * @abstract
     *
     * @param  mixed $data Data to be dispatched
     *
     * @return self
     */
    abstract protected function doSuccess($data);

    /**
     * Returns an error response
     *
     * @abstract
     *
     * @param  string  $message Error message
     * @param  integer $status  HTTP status code
     *
     * @return self
     */
    abstract protected function doError($message, $status);

    /**
     * Dispatches current response
     *
     * @abstract
     *
     * @return void
     */
    abstract public function dispatch();
}
