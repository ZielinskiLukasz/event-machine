<?php
/**
 * This file is part of the proophsoftware/event-machine.
 * (c) 2017-2018 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\EventMachine\Util;

trait DetermineVariableType
{
    private static function getType($var): string
    {
        return is_object($var) ? get_class($var) : gettype($var);
    }
}