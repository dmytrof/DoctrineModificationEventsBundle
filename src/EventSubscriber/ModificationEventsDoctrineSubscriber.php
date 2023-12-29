<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\EventSubscriber;

use Dmytrof\DoctrineModificationEventsBundle\Event\ForceFlushPreviousModificationsInterface;
use Dmytrof\DoctrineModificationEventsBundle\Model\ModificationEventsInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsEventListener(event: Events::postPersist)]
#[AsEventListener(event: Events::postUpdate)]
#[AsEventListener(event: Events::postRemove)]
#[AsEventListener(event: Events::postFlush)]
class ModificationEventsDoctrineSubscriber
{
    private EventDispatcherInterface $eventDispatcher;

    private array $updatedEntities = [];

    private bool $needsFlush = false;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param mixed $entity
     */
    protected function addUpdatedEntityWithModificationEvents(mixed $entity): void
    {
        if ($entity instanceof ModificationEventsInterface) {
            $this->addUpdatedEntity($entity);
        }
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->addUpdatedEntityWithModificationEvents($args->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->addUpdatedEntityWithModificationEvents($args->getObject());
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->addUpdatedEntityWithModificationEvents($args->getObject());
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if ($this->hasUpdatedEntities()) {
            foreach ($this->getUpdatedEntities() as $entity) {
                if ($entity instanceof ModificationEventsInterface) {
                    while ($events = $entity->getNotDispatchedModificationEvents()) {
                        foreach ($events as $event) {
                            if ($event instanceof ForceFlushPreviousModificationsInterface) {
                                $this->makeFlushIfNeeded($args->getObjectManager());
                            }
                            $this->eventDispatcher->dispatch($event);
                            $event->setDispatched();
                            if ($event->isNeedsFlush()) {
                                $this->setNeedsFlush();
                            }
                        }
                        $entity->cleanupDispatchedModificationEvents();
                    }
                }
            }
            $this->cleanupUpdatedEntities();
        }
        $this->makeFlushIfNeeded($args->getObjectManager());
    }

    /**
     * Returns updated entities
     * @return array<int, ModificationEventsInterface>
     */
    protected function getUpdatedEntities(): array
    {
        return $this->updatedEntities;
    }

    /**
     * Checks updated entities exists
     * @return bool
     */
    protected function hasUpdatedEntities(): bool
    {
        return !\empty($this->updatedEntities);
    }

    /**
     * Adds updated entity
     * @param ModificationEventsInterface $entity
     * @return $this
     */
    protected function addUpdatedEntity(ModificationEventsInterface $entity): self
    {
        $entityKey = \spl_object_hash($entity);
        if (!($this->updatedEntities[$entityKey] ?? false)) {
            $this->updatedEntities[$entityKey] = $entity;
        }

        return $this;
    }

    /**
     * Cleans up updated entities
     * @return $this
     */
    protected function cleanupUpdatedEntities(): self
    {
        $this->updatedEntities = [];

        return $this;
    }

    /**
     * Sets need flush
     * @param bool $needsFlush
     * @return $this
     */
    protected function setNeedsFlush(bool $needsFlush = true): self
    {
        $this->needsFlush = $needsFlush;

        return $this;
    }

    /**
     * Makes flush if needed
     * @param ObjectManager $objectManager
     * @return $this
     */
    protected function makeFlushIfNeeded(ObjectManager $objectManager): self
    {
        if ($this->needsFlush) {
            $this->needsFlush = false;
            $objectManager->flush();
        }

        return $this;
    }
}
