<?php

namespace Rap2hpoutre\MySQLExplainExplain;

/**
 * Class responsible for key workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Key
{
/**
 * Stores `$key_name` for key name.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    public $key_name, $col_name;

/**
 * Handle key state through `__construct`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $sql_key_row Input value for `sql_key_row`.
 * @phpstan-param mixed $sql_key_row
 * @psalm-param mixed $sql_key_row
 * @return void Returned value for __construct.
 * @phpstan-return void
 * @psalm-return void
 * @see self::__construct()
 * @example /fr/key/__construct
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function __construct($sql_key_row)
    {
        $this->key_name = $sql_key_row['Key_name'];
        $this->col_name = $sql_key_row['Column_name'];
    }

/**
 * Handle key state through `isPrimary`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for isPrimary.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::isPrimary()
 * @example /fr/key/isPrimary
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function isPrimary()
    {
        return $this->key_name == 'PRIMARY';
    }
}
