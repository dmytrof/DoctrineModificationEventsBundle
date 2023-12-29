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
     * @var array<int, ModificationEventInterface>
     */
    protected array $modificationEvents = [];

    /**
     * Returns modification events
     * @see ModificationEventsInterface::getModificationEvents()
     */
    public function getModificationEvents(Closure $filterCallback = null): array
    {
        if (!$filterCallback) {
            return $this->modificationEvents;
        }

        return \array_filter($this->getModificationEvents(), $filterCallback);
    }

    /**
     * Returns not dispatched modification events
     * @see ModificationEventsInterface::getNotDispatchedModificationEvents()
     */
    public function getNotDispatchedModificationEvents(): array
    {
        $events = $this->getModificationEvents(
            static fn (ModificationEventInterface $event) => !$event->isDispatched(),
        );
        \usort(
            $events,
            static fn (ModificationEventInterface $eventA, ModificationEventInterface $eventB)
                => $eventB->getPriority() <=> $eventA->getPriority(),
        );

        return $events;
    }

    /**
     * Adds modification events
     * @see ModificationEventsInterface::addModificationEvent()
     */
    public function addModificationEvent(ModificationEventInterface $event): static
    {
        $this->modificationEvents[] = $event;

        return $this;
    }

    /**
     * Clears modification events
     * @see ModificationEventsInterface::cleanupModificationEvents()
     */
    public function cleanupModificationEvents(bool $withLongLifeEvents = true): static
    {
        $this->modificationEvents = $withLongLifeEvents
            ? []
            : $this->getModificationEvents(
                static fn (ModificationEventInterface $event) => $event instanceof LongLifeModificationEventInterface,
            );

        return $this;
    }

    /**
     * Clears dispatcher modification events
     * @see ModificationEventsInterface::cleanupDispatchedModificationEvents()
     */
    public function cleanupDispatchedModificationEvents(): static
    {
        $this->modificationEvents = $this->getModificationEvents(
            static fn (ModificationEventInterface $event)
                => $event instanceof LongLifeModificationEventInterface || !$event->isDispatched(),
        );

        return $this;
    }
}
