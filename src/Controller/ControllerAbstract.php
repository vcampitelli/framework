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
abstract class ControllerAbstract implements ControllerInterface
{
    /**
     * Container object
     *
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * Constructor
     *
     * @param ContainerInterface $container Container object
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns the container object
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
