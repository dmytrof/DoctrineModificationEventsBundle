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

/**
 * @deprecated Will be removed on next versions. Use TrackedModificationEventInterface instead
 */
interface LongLifeModificationEventInterface extends ModificationEventInterface, TrackedModificationEventInterface
{
}
