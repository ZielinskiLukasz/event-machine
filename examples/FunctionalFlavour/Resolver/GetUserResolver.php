<?php
/**
 * This file is part of the proophsoftware/event-machine.
 * (c) 2017-2018 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophExample\FunctionalFlavour\Resolver;

use Prooph\EventMachine\Querying\SyncResolver;
use ProophExample\FunctionalFlavour\Query\GetUser;

final class GetUserResolver implements SyncResolver
{
    /**
     * @var array
     */
    private $cachedUserState;

    public function __construct(array $cachedUserState)
    {
        $this->cachedUserState = $cachedUserState;
    }

    public function __invoke(GetUser $getUser)
    {
        if ($this->cachedUserState['userId'] === $getUser->userId) {
            return $this->cachedUserState;
        }
        new \RuntimeException('User not found');
    }
}
