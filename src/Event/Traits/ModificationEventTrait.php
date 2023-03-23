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

use Dmytrof\DoctrineModificationEventsBundle\Event\ModificationEventInterface;

trait ModificationEventTrait
{
    /**
     * @var bool
     */
    protected $needsFlush = false;

    /**
     * @var bool
     */
    protected $dispatched = false;

    /**
     * @var int
     */
    protected $priority = 0;

    /**
     * Checks if flush needed
     * @see ModificationEventInterface::isNeedsFlush()
     */
    public function isNeedsFlush(): bool
    {
        return $this->needsFlush;
    }

    /**
     * Sets needs flush
     * @see ModificationEventInterface::setNeedsFlush()
     */
    public function setNeedsFlush(bool $needsFlush = true): ModificationEventInterface
    {
        $this->needsFlush = $needsFlush;

        return $this;
    }

    /**
     * Checks if dispatcher
     * @see ModificationEventInterface::isDispatched()
     */
    public function isDispatched(): bool
    {
        return $this->dispatched;
    }

    /**
     * Sets dispatched
     * @see ModificationEventInterface::setDispatched()
     */
    public function setDispatched(bool $dispatched = true): ModificationEventInterface
    {
        $this->dispatched = $dispatched;

        return $this;
    }

    /**
     * Sets priority
     * @see ModificationEventInterface::getPriority()
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Sets priority
     * @see ModificationEventInterface::setPriority()
     */
    public function setPriority(int $priority): ModificationEventInterface
    {
        $this->priority = $priority;

        return $this;
    }
}
