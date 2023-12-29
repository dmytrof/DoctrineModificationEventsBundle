<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Event\Traits;

use Dmytrof\DoctrineModificationEventsBundle\Event\SingletonModificationEventInterface;
use Dmytrof\DoctrineModificationEventsBundle\Model\Traits\ModificationEventsTrait;

trait SingletonModificationEventTrait
{
    use ModificationEventsTrait;

    /**
     * Returns singleton key
     * @see SingletonModificationEventInterface::getSingletonKey()
     */
    public function getSingletonKey(): string
    {
        return static::class;
    }

    /**
     * Checks if current event should rewrite already existed modification event
     * @see SingletonModificationEventInterface::isRewriteExisted()
     */
    public function isRewriteExisted(): bool
    {
        return false;
    }
}
