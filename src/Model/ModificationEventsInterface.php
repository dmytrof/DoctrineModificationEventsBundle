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

use Closure;
use Dmytrof\DoctrineModificationEventsBundle\Event\ModificationEventInterface;

interface ModificationEventsInterface
{
    /**
     * Returns modification events
     * @return array<array-key, ModificationEventInterface>
     */
    public function getModificationEvents(?Closure $filterCallback = null): array;

    /**
     * Returns not dispatched modification events
     */
    public function getNotDispatchedModificationEvents(): array;

    /**
     * Adds modification events
     */
    public function addModificationEvent(ModificationEventInterface $event): static;

    /**
     * Removes modification event
     */
    public function removeModificationEvent(ModificationEventInterface $event): static;

    /**
     * Clears modification events
     */
    public function cleanupModificationEvents(bool $withTrackedEvents = true): static;

    /**
     * Clears dispatched modification events
     */
    public function cleanupDispatchedModificationEvents(): static;
}
