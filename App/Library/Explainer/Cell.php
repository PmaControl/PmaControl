<?php

namespace Rap2hpoutre\MySQLExplainExplain;

/**
 * Class responsible for cell workflows.
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
class Cell
{
/**
 * Stores `$v` for v.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    public $v;
/**
 * Stores `$score` for score.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    public $score = null;
/**
 * Stores `$info` for info.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    public $info;
/**
 * Stores `$id` for id.
 *
 * @var mixed
 * @phpstan-var mixed
 * @psalm-var mixed
 */
    public $id;

/**
 * Handle cell state through `__construct`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $v Input value for `v`.
 * @phpstan-param mixed $v
 * @psalm-param mixed $v
 * @return void Returned value for __construct.
 * @phpstan-return void
 * @psalm-return void
 * @see self::__construct()
 * @example /fr/cell/__construct
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function __construct($v)
    {
        $this->v  = $v;
        $this->id = uniqid('cell');
    }

/**
 * Handle cell state through `setSuccess`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for setSuccess.
 * @phpstan-return void
 * @psalm-return void
 * @see self::setSuccess()
 * @example /fr/cell/setSuccess
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function setSuccess()
    {
        $this->score = 2;
    }

/**
 * Handle cell state through `setWarning`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for setWarning.
 * @phpstan-return void
 * @psalm-return void
 * @see self::setWarning()
 * @example /fr/cell/setWarning
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function setWarning()
    {
        $this->score = 1;
    }

/**
 * Handle cell state through `setDanger`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return void Returned value for setDanger.
 * @phpstan-return void
 * @psalm-return void
 * @see self::setDanger()
 * @example /fr/cell/setDanger
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function setDanger()
    {
        $this->score = 0;
    }

/**
 * Handle cell state through `isSuccess`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for isSuccess.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::isSuccess()
 * @example /fr/cell/isSuccess
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function isSuccess()
    {
        return $this->score === 2;
    }

/**
 * Handle cell state through `isWarning`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for isWarning.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::isWarning()
 * @example /fr/cell/isWarning
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function isWarning()
    {
        return $this->score === 1;
    }

/**
 * Handle cell state through `isDanger`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @return mixed Returned value for isDanger.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::isDanger()
 * @example /fr/cell/isDanger
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    public function isDanger()
    {
        return $this->score === 0;
    }
}
