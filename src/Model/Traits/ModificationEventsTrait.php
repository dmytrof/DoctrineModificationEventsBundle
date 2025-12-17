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

use Closure;
use Dmytrof\DoctrineModificationEventsBundle\Event\ModificationEventInterface;
use Dmytrof\DoctrineModificationEventsBundle\Event\SingletonModificationEventInterface;
use Dmytrof\DoctrineModificationEventsBundle\Event\TrackedModificationEventInterface;
use Dmytrof\DoctrineModificationEventsBundle\Model\ModificationEventsInterface;

trait ModificationEventsTrait
{
    /**
     * @var array<array-key, ModificationEventInterface>
     */
    protected array $modificationEvents = [];

    /**
     * Returns modification events
     * @return array<array-key, ModificationEventInterface>
     */
    public function getModificationEvents(?Closure $filterCallback = null): array
    {
        if (!$filterCallback) {
            return $this->modificationEvents;
        }

        return \array_values(\array_filter($this->getModificationEvents(), $filterCallback));
    }

    /**
     * Returns modification events of class
     * @return array<array-key, ModificationEventInterface>
     */
    public function getModificationEventsOfClass(string $className): array
    {
        return $this->getModificationEvents(
            fn (ModificationEventInterface $modificationEvent)
                => $modificationEvent::class === $className,
        );
    }

    /**
     * Returns modification events of class or which are extend class
     * @return array<array-key, ModificationEventInterface>
     */
    public function getModificationEventsInstanceOf(string $className): array
    {
        return $this->getModificationEvents(
            fn (ModificationEventInterface $modificationEvent)
            => \is_a($modificationEvent, $className),
        );
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
        if ($event instanceof SingletonModificationEventInterface) {
            $existedEvents = \array_filter(
                $this->getModificationEvents(),
                static fn (ModificationEventInterface $evt)
                => $evt instanceof SingletonModificationEventInterface
                    && $evt->getSingletonKey() === $event->getSingletonKey(),
            );
            if ($existedEvents && $event->isRewriteExisted()) {
                $this->removeModificationEvent(...$existedEvents);
            }
            if (!$existedEvents || $event->isRewriteExisted()) {
                $this->modificationEvents[] = $event;
            }
        } else {
            $this->modificationEvents[] = $event;
        }

        return $this;
    }

    /**
     * Removes modification events
     * @see ModificationEventsInterface::addModificationEvent()
     */
    public function removeModificationEvent(ModificationEventInterface $event): static
    {
        $events = \func_get_args();
        $this->modificationEvents = $this->getModificationEvents(
            static fn (ModificationEventInterface $evt) => !\in_array($evt, $events, true),
        );

        return $this;
    }

    /**
     * Clears modification events
     * @see ModificationEventsInterface::cleanupModificationEvents()
     */
    public function cleanupModificationEvents(bool $withTrackedEvents = true): static
    {
        $this->modificationEvents = $withTrackedEvents
            ? []
            : $this->getModificationEvents(
                static fn (ModificationEventInterface $event) => $event instanceof TrackedModificationEventInterface,
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
                => $event instanceof TrackedModificationEventInterface || !$event->isDispatched(),
        );

        return $this;
    }
}
