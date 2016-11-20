<?php
/**
 * @author     Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package    Core
 * @subpackage Http
 * @since      2016-11-19
 */

namespace Core\Http\Response;

use Core\View;

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
    protected $_data = [];
    
    /**
     * Returns a success response
     *
     * @param  mixed $data Data to be dispatched
     *
     * @return self
     */
    protected function doSuccess($data)
    {
        $this->_data = (array) $data;
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
        $this->_data = (array) $data;
        return $this;
    }
    
    /**
     * Dispatches current response
     *
     * @return self
     */
    public function dispatch()
    {
        // Two arguments: controller and action
        if (\func_num_args() == 2) {
            list($controller, $action) = \func_get_args();
            $controller = \strtolower($controller);
            
            // Removes "Controller" from its name
            if (\substr($controller, -10) === 'controller') {
                $controller = \substr($controller, 0, -10);
            }
            
            // Separates module from the controller name
            $arr = \explode('\\', \trim($controller, '\\'));
            $module = \array_shift($arr);
            
            // Initializing view
            $view = new View\HtmlView();
            
            // Script path
            $script = APPLICATION_PATH . "/{$module}/view/" . \implode('/', $arr) . "/{$action}.phtml";
            if (\is_file($script)) {
                // Initializing view
                $innerView = new View\HtmlView();
                
                // View data
                foreach ($this->_data as $key => $value) {
                    $innerView->{$key} = $value;
                }
                
                // View content
                $view->content = $innerView->partial($script);

                $view->render(APPLICATION_PATH . '/view/layout.phtml');
                return $this;
            } else {
                $view->content = "Couldn't find {$action} view for {$controller}";
            }
        }
        
        $view->render(APPLICATION_PATH . '/view/error.phtml');
        
        return $this;
    }
}
