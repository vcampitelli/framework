<?php
/**
 * @author     Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package    Core
 * @subpackage View
 * @since      2016-11-19
 */

namespace Vcampitelli\Framework\View;

/**
 * View interface
 */
interface ViewInterface
{
    /**
     * Renders a script
     *
     * @param  string $script Script path
     *
     * @return mixed
     */
    public function render($script);
}
