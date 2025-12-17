<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Tests\Unit\Model\Model\Traits;

use Dmytrof\DoctrineModificationEventsBundle\Event\ModificationEvent;
use Dmytrof\DoctrineModificationEventsBundle\Event\SingletonModificationEventInterface;
use Dmytrof\DoctrineModificationEventsBundle\Event\TrackedModificationEventInterface;
use Dmytrof\DoctrineModificationEventsBundle\Model\ModificationEventsInterface;
use Dmytrof\DoctrineModificationEventsBundle\Model\Traits\ModificationEventsTrait;
use PHPUnit\Framework\TestCase;

class ModificationEventsTraitTest extends TestCase
{
    public function testaRegularModificationEvents(): void
    {
        $meModel = new class implements ModificationEventsInterface
        {
            use ModificationEventsTrait;

            public function setSomething(mixed $value): void
            {
                $logEvent = new class ($value) extends ModificationEvent
                {
                    private mixed $value;

                    public function __construct(mixed $value)
                    {
                        $this->value = $value;
                    }

                    public function getValue(): mixed
                    {
                        return $this->value;
                    }
                };
                $this->addModificationEvent($logEvent);
            }

            public function setTrackedValue(mixed $value): void
            {
                $logEvent = new class ($value) extends ModificationEvent implements TrackedModificationEventInterface
                {
                    private mixed $value;

                    public function __construct(mixed $value)
                    {
                        $this->value = $value;
                    }

                    public function getValue(): mixed
                    {
                        return $this->value;
                    }
                };
                $this->addModificationEvent($logEvent);
            }

            public function setPrioritizedValue(mixed $value, int $priority = 100): void
            {
                $logEvent = new class ($value) extends ModificationEvent {
                    private mixed $value;

                    public function __construct(mixed $value)
                    {
                        $this->value = $value;
                    }

                    public function getValue(): mixed
                    {
                        return $this->value;
                    }
                };
                $logEvent->setPriority($priority);
                $this->addModificationEvent($logEvent);
            }

            public function setSingletonValue(string $key, mixed $value, bool $rewriteExisted = false): void
            {
                $logEvent = new class ($key, $value, $rewriteExisted) extends ModificationEvent implements
                    SingletonModificationEventInterface
                {
                    private string $key;

                    private bool $rewriteExisted;
                    private mixed $value;

                    public function __construct(string $key, mixed $value, bool $rewriteExisted)
                    {
                        $this->key = $key;
                        $this->value = $value;
                        $this->rewriteExisted = $rewriteExisted;
                    }

                    public function getValue(): mixed
                    {
                        return $this->value;
                    }

                    #[\Override]
                    public function getSingletonKey(): string
                    {
                        return $this->key;
                    }

                    #[\Override]
                    public function isRewriteExisted(): bool
                    {
                        return $this->rewriteExisted;
                    }
                };
                $this->addModificationEvent($logEvent);
            }
        };

        $this->assertEquals([], $meModel->getModificationEvents());
        $meModel->setSomething(123);
        $this->assertCount(1, $meModel->getModificationEvents());
        $this->assertInstanceOf(ModificationEvent::class, $meModel->getModificationEvents()[0]);
        $this->assertEquals(123, $meModel->getModificationEvents()[0]->getValue());
        $this->assertEquals(0, $meModel->getModificationEvents()[0]->getPriority());

        $meModel->setSomething('qwer');
        $this->assertCount(2, $meModel->getModificationEvents());
        $this->assertInstanceOf(ModificationEvent::class, $meModel->getModificationEvents()[1]);
        $this->assertEquals('qwer', $meModel->getModificationEvents()[1]->getValue());
        $this->assertEquals(0, $meModel->getModificationEvents()[1]->getPriority());

        $meModel->setTrackedValue('tracked');
        $this->assertCount(3, $meModel->getModificationEvents());
        $this->assertInstanceOf(
            TrackedModificationEventInterface::class,
            $meModel->getModificationEvents()[2],
        );
        $this->assertEquals('tracked', $meModel->getModificationEvents()[2]->getValue());
        $this->assertEquals(0, $meModel->getModificationEvents()[2]->getPriority());

        $meModel->setPrioritizedValue('priority-99', -99);
        $this->assertCount(4, $meModel->getModificationEvents());
        $this->assertInstanceOf(ModificationEvent::class, $meModel->getModificationEvents()[3]);
        $this->assertEquals('priority-99', $meModel->getModificationEvents()[3]->getValue());
        $this->assertEquals(-99, $meModel->getModificationEvents()[3]->getPriority());

        $meModel->setPrioritizedValue('priority100', 100);
        $this->assertCount(5, $meModel->getModificationEvents());
        $this->assertInstanceOf(ModificationEvent::class, $meModel->getModificationEvents()[4]);
        $this->assertEquals('priority100', $meModel->getModificationEvents()[4]->getValue());
        $this->assertEquals(100, $meModel->getModificationEvents()[4]->getPriority());

        $this->assertCount(5, $notDispatchedEvents = $meModel->getNotDispatchedModificationEvents());

        $this->assertEquals('priority100', $notDispatchedEvents[0]->getValue());
        $this->assertEquals('priority-99', $notDispatchedEvents[4]->getValue());

        $this->assertEquals('123', $meModel->getModificationEvents(function (object $event) {
            return 123 === $event->getValue();
        })[0]->getValue());

        $this->assertEquals('tracked', $meModel->getModificationEvents(function (object $event) {
            return $event instanceof TrackedModificationEventInterface;
        })[0]->getValue());

        $meModel->getModificationEvents(function (object $event) {
            return 123 === $event->getValue();
        })[0]->setDispatched();
        $this->assertCount(4, $meModel->getNotDispatchedModificationEvents());

        // Test singleton events

        $meModel->setSingletonValue('key1', 'value1', false);
        $this->assertCount(5, $meModel->getNotDispatchedModificationEvents());
        $meModel->setSingletonValue('key1', 'value5', false);
        $this->assertCount(5, $meModel->getNotDispatchedModificationEvents());
        $meModel->setSingletonValue('key2', 'value5', false);
        $this->assertCount(6, $meModel->getNotDispatchedModificationEvents());

        $this->assertEquals('value1', $meModel->getModificationEvents(function (object $event) {
            return $event instanceof SingletonModificationEventInterface && 'key1' === $event->getSingletonKey();
        })[0]->getValue());
        $this->assertEquals('value5', $meModel->getModificationEvents(function (object $event) {
            return $event instanceof SingletonModificationEventInterface && 'key2' === $event->getSingletonKey();
        })[0]->getValue());

        $meModel->setSingletonValue('key1', '100500', true);
        $this->assertCount(6, $meModel->getNotDispatchedModificationEvents());
        $this->assertEquals('100500', $meModel->getModificationEvents(function (object $event) {
            return $event instanceof SingletonModificationEventInterface && 'key1' === $event->getSingletonKey();
        })[0]->getValue());
        $this->assertEquals('value5', $meModel->getModificationEvents(function (object $event) {
            return $event instanceof SingletonModificationEventInterface && 'key2' === $event->getSingletonKey();
        })[0]->getValue());

        $this->assertInstanceOf(\get_class($meModel), $meModel->cleanupDispatchedModificationEvents());
        $this->assertCount(6, $meModel->getModificationEvents());

        $this->assertInstanceOf(\get_class($meModel), $meModel->cleanupModificationEvents(false));
        $this->assertCount(1, $meModel->getModificationEvents());

        $this->assertInstanceOf(\get_class($meModel), $meModel->cleanupModificationEvents());
        $this->assertCount(0, $meModel->getModificationEvents());
    }
}
