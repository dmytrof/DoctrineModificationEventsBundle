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

use Dmytrof\DoctrineModificationEventsBundle\Event\ModificationEventInterface;
use Dmytrof\DoctrineModificationEventsBundle\Model\ModificationEventsInterface;

trait ModificationEventsTrait
{
    /**
     * @var array
     */
    protected $modificationEvents = [];

    /**
     * Returns modification events
     * @return array
     */
    public function getModificationEvents(): array
    {
        return $this->modificationEvents;
    }

    /**
     * Adds modification events
     * @param ModificationEventInterface $event
     * @return ModificationEventsInterface
     */
    public function addModificationEvent(ModificationEventInterface $event): ModificationEventsInterface
    {
        $this->modificationEvents[] = $event;

        return $this;
    }

    /**
     * Clears modification events
     * @return ModificationEventsInterface
     */
    public function cleanupModificationEvents(): ModificationEventsInterface
    {
        $this->modificationEvents = [];

        return $this;
    }
}