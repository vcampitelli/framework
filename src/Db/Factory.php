<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Db
 * @since       2015-10-12
 */

namespace Core\Db;

/**
 * Factory for the adapters
 */
class Factory extends \Core\Service\FactoryAbstract
{
    /**
     * Builds a new database connection, ignoring the pool
     *
     * @param  string $alias Connection alias
     *
     * @return Adapter
     */
    public function build($alias)
    {
        return new Adapter($this->doGetConfig($alias));
    }

    /**
     * Gets current configuration to connect to the database
     *
     * @throws \RuntimeException If no configuration is found
     *
     * @param  string $alias Connection alias
     *
     * @return array
     */
    protected function doGetConfig($alias)
    {
        $arr = $this->getConfig()->db()->{$alias};
        if (empty($arr)) {
            throw new \RuntimeException("Não foi possível configurar a conexão {$alias}.");
        }
        return $arr;
    }
}
