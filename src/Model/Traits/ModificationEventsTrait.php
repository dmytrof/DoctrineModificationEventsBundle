<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Model\Traits;

use Dmytrof\DoctrineModificationEventsBundle\Event\LongLifeModificationEventInterface;
use Dmytrof\DoctrineModificationEventsBundle\Event\ModificationEventInterface;
use Dmytrof\DoctrineModificationEventsBundle\Model\ModificationEventsInterface;
use Closure;

trait ModificationEventsTrait
{
    /**
     * @var array
     */
    protected $modificationEvents = [];

    /**
     * Returns modification events
     * @see ModificationEventsInterface::getModificationEvents()
     */
    public function getModificationEvents(Closure $filterCallback = null): array
    {
        if (!$filterCallback) {
            return $this->modificationEvents;
        }

        return array_filter($this->getModificationEvents(), $filterCallback);
    }

    /**
     * Returns not dispatched modification events
     * @see ModificationEventsInterface::getNotDispatchedModificationEvents()
     */
    public function getNotDispatchedModificationEvents(): array
    {
        $events = $this->getModificationEvents(function (ModificationEventInterface $event) {
            return !$event->isDispatched();
        });
        usort($events, static function (ModificationEventInterface $eventA, ModificationEventInterface $eventB) {
            return $eventB->getPriority() <=> $eventA->getPriority();
        });

        return $events;
    }

    /**
     * Adds modification events
     * @see ModificationEventsInterface::addModificationEvent()
     */
    public function addModificationEvent(ModificationEventInterface $event): ModificationEventsInterface
    {
        $this->modificationEvents[] = $event;

        return $this;
    }

    /**
     * Clears modification events
     * @see ModificationEventsInterface::cleanupModificationEvents()
     */
    public function cleanupModificationEvents(bool $withLongLifeEvents = true): ModificationEventsInterface
    {
        $this->modificationEvents = $withLongLifeEvents ? [] : $this->getModificationEvents(function (ModificationEventInterface $event) {
            return $event instanceof LongLifeModificationEventInterface;
        });

        return $this;
    }

    /**
     * Clears dispatcher modification events
     * @see ModificationEventsInterface::cleanupDispatchedModificationEvents()
     */
    public function cleanupDispatchedModificationEvents(): ModificationEventsInterface
    {
        $this->modificationEvents = $this->getModificationEvents(function (ModificationEventInterface $event) {
            return $event instanceof LongLifeModificationEventInterface
                || !$event->isDispatched()
            ;
        });

        return $this;
    }
}
