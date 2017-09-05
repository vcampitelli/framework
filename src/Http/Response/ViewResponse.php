<?php
/**
 * @author     Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package    Core
 * @subpackage Http
 * @since      2016-11-19
 */

namespace Vcampitelli\Framework\Http\Response;

use Vcampitelli\Framework\View;

/**
 * Simple view (HTML) response
 */
class ViewResponse extends ResponseAbstract
{
    /**
     * Current data
     *
     * @var string
     */
    protected $data = [];

    /**
     * Response status
     *
     * @var boolean
     */
    protected $status = true;

    /**
     * Returns a success response
     *
     * @param  mixed $data Data to be dispatched
     *
     * @return self
     */
    protected function doSuccess($data)
    {
        $this->data = (array) $data;
        $this->status = true;
        return $this;
    }

    /**
     * Returns an error response
     *
     * @param  mixed   $data   Error message to be dispatched
     * @param  integer $status HTTP status code
     *
     * @return self
     */
    protected function doError($data, $status)
    {
        $this->data = (\is_array($data)) ? $data : ['content' => $data];
        $this->status = false;

        return $this;
    }

    /**
     * Dispatches current response
     *
     * @param  string $controller Controller to handle custom response, if any (optional)
     * @param  string $action     Action to handle custom response, if any (optional)
     * @param  string $basePath   Base path por views (optional)
     *
     * @return self
     */
    public function dispatch($controller = null, $action = null, $basePath = null)
    {
        // Initializing view
        $view = new View\HtmlView();
        $basePath = rtrim(trim($basePath), '/') . '/';

        // Two arguments: controller and action
        if (($this->status) && (!empty($controller)) && (!empty($action))) {
            $controller = \strtolower($controller);

            // Removes "Controller" from its name
            if (\substr($controller, -10) === 'controller') {
                $controller = \substr($controller, 0, -10);
            }

            // Separates module from the controller name
            $arr = \explode('\\', \trim($controller, '\\'));

            // Script path @FIXME APPLICATION_PATH
            $script = $basePath . \implode('/', $arr) . "/{$action}.phtml";
            if (\is_file($script)) {
                // Initializing view
                $innerView = new View\HtmlView();

                // View data
                foreach ($this->data as $key => $value) {
                    $innerView->{$key} = $value;
                }

                // View content
                $view->content = $innerView->partial($script);

                $view->render("{$basePath}/layout.phtml");
                return $this;
            }

            $view->content = "Couldn't find {$action} view for {$controller}";
        }

        // View data
        foreach ($this->data as $key => $value) {
            $view->{$key} = $value;
        }
        $view->render("{$basePath}/error.phtml");

        return $this;
    }
}
