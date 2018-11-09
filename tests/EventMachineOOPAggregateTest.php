<?php
/**
 * This file is part of the proophsoftware/event-machine.
 * (c) 2017-2018 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\EventMachineTest;

use Prooph\EventMachine\EventMachine;
use Prooph\EventMachine\Runtime\CallInterceptor;
use Prooph\EventMachine\Runtime\CustomMessageCallInterceptor;
use Prooph\EventMachine\Runtime\OOPAggregateCallInterceptor;
use ProophExample\CustomMessages\Api\MessageDescription;
use ProophExample\CustomMessages\ExampleCustomMessagePort;
use ProophExample\OOPStyle\Aggregate\UserDescription;
use ProophExample\OOPStyle\ExampleOOPPort;

class EventMachineOOPAggregateTest extends EventMachineTestAbstract
{
    protected function loadEventMachineDescriptions(EventMachine $eventMachine)
    {
        $eventMachine->load(MessageDescription::class);
        $eventMachine->load(UserDescription::class);
    }

    protected function getCallInterceptor(): CallInterceptor
    {
        return new OOPAggregateCallInterceptor(
            new ExampleOOPPort(),
            new CustomMessageCallInterceptor(new ExampleCustomMessagePort())
        );
    }
}
