<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Controller
 * @since       2016-11-02
 */

namespace Core\Controller;

use Core\Service\ContainerInterface;

/**
 * Abstract controller
 *
 * @abstract
 */
interface ControllerInterface
{
    /**
     * Constructor
     *
     * @param ContainerInterface $container Container object
     */
    public function __construct(ContainerInterface $container);
    
    /**
     * Returns the container object
     *
     * @return ContainerInterface
     */
    public function getContainer();
}
