<?php
/**
 * @author     Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package    Core
 * @subpackage Http
 * @since      2016-11-19
 */

namespace Vcampitelli\Framework\Http\Response;

/**
 * Simple data (JSON/XML/etc) response
 */
class DataResponse extends ResponseAbstract
{
    /**
     * Current response
     *
     * @var array
     */
    protected $data = [];

    /**
     * Returns a success response
     *
     * @param  mixed $data Data to be dispatched
     *
     * @return self
     */
    protected function doSuccess($data)
    {
        $this->data = $this->build($data, true);
        return $this;
    }

    /**
     * Returns an error response
     *
     * @param  mixed   $data   Error data to be dispatched
     * @param  integer $status HTTP status code
     *
     * @return self
     */
    protected function doError($data, $status)
    {
        $this->data = $this->build($data, false, $status);
        return $this;
    }

    /**
     * Dispatches a message
     *
     * @param  mixed    $data   Data to be dispatched
     * @param  boolean  $status Success or error
     * @param  int      $code   HTTP error
     *
     * @return string
     */
    protected function build($data, $status, $code = null)
    {
        // @TODO content-negotiation
        \header('Content-type: application/json');

        $arr = [
            'status' => (bool) $status,
            'data'   => $data
        ];

        if ($code) {
            $arr['code'] = $code;
        }

        return $arr;
    }

    /**
     * Dispatches current response
     *
     * @return void
     */
    public function dispatch()
    {
        echo \json_encode($this->data);
    }
}
