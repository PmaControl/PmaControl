<?php

namespace Rap2hpoutre\MySQLExplainExplain;

/**
 * Class responsible for column workflows.
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
class Column
{
/**
 * Stores `$field` for field.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    public $field, $type, $null, $key, $default, $extra;

/**
 * Handle column state through `__construct`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $sql_col Input value for `sql_col`.
 * @phpstan-param mixed $sql_col
 * @psalm-param mixed $sql_col
 * @return void Returned value for __construct.
 * @phpstan-return void
 * @psalm-return void
 * @see self::__construct()
 * @example /fr/column/__construct
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function __construct($sql_col)
    {
        $this->field   = $sql_col['Field'];
        $this->type    = $sql_col['Type'];
        $this->null    = $sql_col['Null'];
        $this->key     = $sql_col['Key'];
        $this->default = $sql_col['Default'];
        $this->extra   = $sql_col['Extra'];
    }

/**
 * Handle column state through `containsId`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for containsId.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::containsId()
 * @example /fr/column/containsId
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function containsId()
    {
        return preg_match('/id/', $this->field);
    }

/**
 * Handle column state through `isNull`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for isNull.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::isNull()
 * @example /fr/column/isNull
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function isNull()
    {
        return trim($this->null) == 'YES';
    }
}
