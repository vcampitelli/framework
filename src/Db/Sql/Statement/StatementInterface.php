<?php
/**
 * @author      Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @package     Core
 * @subpackage  Db
 * @since       2016-11-02
 */

namespace Core\Db\Sql\Statement;

use Core\Db\Adapter;

/**
 * Insert expression
 */
interface StatementInterface
{
    /**
     * Executes statement
     *
     * @param  Adapter $db DB adapter
     *
     * @return mixed
     */
    public function execute(Adapter $db);
}
