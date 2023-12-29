<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Model;

use Dmytrof\DoctrineModificationEventsBundle\Event\ModificationEventInterface;
use Closure;

interface ModificationEventsInterface
{
    /**
     * Returns modification events
     * @param Closure|null $filterCallback
     * @return array|ModificationEventInterface[]
     */
    public function getModificationEvents(Closure $filterCallback = null): array;

    /**
     * Returns not dispatched modification events
     * @return array
     */
    public function getNotDispatchedModificationEvents(): array;

    /**
     * Adds modification events
     * @param ModificationEventInterface $event
     * @return $this
     */
    public function addModificationEvent(ModificationEventInterface $event): static;

    /**
     * Removes modification event
     * @param ModificationEventInterface $event
     * @return $this
     */
    public function removeModificationEvent(ModificationEventInterface $event): static;

    /**
     * Clears modification events
     * @param bool $withTrackedEvents
     * @return $this
     */
    public function cleanupModificationEvents(bool $withTrackedEvents = true): static;

    /**
     * Clears dispatched modification events
     * @return $this
     */
    public function cleanupDispatchedModificationEvents(): static;
}
