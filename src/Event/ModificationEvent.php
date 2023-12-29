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

use Dmytrof\DoctrineModificationEventsBundle\Event\Traits\ModificationEventTrait;
use Symfony\Contracts\EventDispatcher\Event;

abstract class ModificationEvent extends Event implements ModificationEventInterface
{
    use ModificationEventTrait;
}
