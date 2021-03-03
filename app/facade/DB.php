<?php

namespace app\facade;

use frame\core\database\interfaces\DBInterface;
use frame\core\database\query\Query;

/**
 * Class DB
 * @see DBInterface
 * @method static Query query()
 * @method static Query name(string $table)
 * @method static Query table(string $table)
 * @method static array select(string $sql, array $bindParams = [])
 * @method static int execute(string $sql, array $bindParams = [])
 * @package app\facade
 */
class DB extends BaseFacade
{
    public static function getFacade()
    {
        return 'DB';
    }
}