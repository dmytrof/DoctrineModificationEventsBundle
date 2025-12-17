<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event;

use Dmytrof\DoctrineModificationEventsBundle\Event\ModificationEvent;
use Dmytrof\DoctrineModificationEventsBundle\Event\SingletonModificationEventInterface;

class NotifyChangedName extends ModificationEvent implements SingletonModificationEventInterface
{
    public function __construct(
        private string $name,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSingletonKey(): string
    {
        return static::class;
    }

    public function isRewriteExisted(): bool
    {
        return true;
    }
}
