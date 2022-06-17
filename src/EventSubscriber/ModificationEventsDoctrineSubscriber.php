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

use Dmytrof\DoctrineModificationEventsBundle\Model\ModificationEventsInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ModificationEventsDoctrineSubscriber implements EventSubscriber
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var array
     */
    protected $updatedEntities = [];

    /**
     * @var bool
     */
    protected $needsFlush = false;

    /**
     * ModelDoctrineSubscriber constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Get subscribed events
     *
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
            Events::postFlush,
        ];
    }

    /**
     * @param mixed $entity
     */
    protected function addUpdatedEntityWithModificationEvents($entity): void
    {
        if ($entity instanceof ModificationEventsInterface) {
           $this->addUpdatedEntity($entity);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->addUpdatedEntityWithModificationEvents($args->getObject());
    }

    /**
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->addUpdatedEntityWithModificationEvents($args->getObject());
    }

    /**
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->addUpdatedEntityWithModificationEvents($args->getObject());
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        if ($this->hasUpdatedEntities()) {
            foreach ($this->getUpdatedEntities() as $entity) {
                if ($entity instanceof ModificationEventsInterface) {
                    while ($events = $entity->getNotDispatchedModificationEvents()) {
                        foreach ($events as $event) {
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
        $this->makeFlushIfNeeded($args->getEntityManager());
    }

    /**
     * Returns updated entities
     * @return array
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
        return (bool) count($this->updatedEntities);
    }

    /**
     * Adds updated entity
     * @param $entity
     * @return $this
     */
    protected function addUpdatedEntity($entity): self
    {
        if (!in_array($entity, $this->updatedEntities)) {
            $this->updatedEntities[] = $entity;
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