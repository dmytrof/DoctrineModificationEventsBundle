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
     * Clears modification events
     * @param bool $withLongLifeEvents
     * @return $this
     */
    public function cleanupModificationEvents(bool $withLongLifeEvents = true): static;

    /**
     * Clears dispatched modification events
     * @return $this
     */
    public function cleanupDispatchedModificationEvents(): static;
}
