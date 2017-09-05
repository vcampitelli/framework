<?php
/**
 * @author     Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package    Core
 * @subpackage View
 * @since      2016-11-19
 */

namespace Vcampitelli\Framework\View;

/**
 * View class to render HTML scripts
 */
class HtmlView implements ViewInterface
{
    /**
     * Renders a script and display its content
     *
     * @param  string $script Script path
     *
     * @return self
     */
    public function render($script)
    {
        echo $this->partial($script);
        return $this;
    }

    /**
     * Renders a script and returns its content
     *
     * @param  string $script Script path
     *
     * @return string Script content
     */
    public function partial($script)
    {
        \ob_start();
        require $script;
        return \ob_get_clean();
    }
}
