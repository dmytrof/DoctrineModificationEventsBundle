<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Event;

interface SingletonModificationEventInterface extends ModificationEventInterface
{
    /**
     * Returns singleton key
     * @return string
     */
    public function getSingletonKey(): string;

    /**
     * Checks if current event should rewrite already existed modification event
     * @return bool
     */
    public function isRewriteExisted(): bool;
}
