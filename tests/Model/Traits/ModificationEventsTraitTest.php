<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Tests\Model\Traits;

use Dmytrof\DoctrineModificationEventsBundle\Event\LongLifeModificationEventInterface;
use Dmytrof\DoctrineModificationEventsBundle\Event\ModificationEvent;
use Dmytrof\DoctrineModificationEventsBundle\Model\ModificationEventsInterface;
use Dmytrof\DoctrineModificationEventsBundle\Model\Traits\ModificationEventsTrait;
use PHPUnit\Framework\TestCase;

class ModificationEventsTraitTest extends TestCase
{
    public function testModificationEvents(): void
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

            public function setLongLifeValue(mixed $value): void
            {
                $logEvent = new class ($value) extends ModificationEvent implements LongLifeModificationEventInterface
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

        $meModel->setLongLifeValue('longLife');
        $this->assertCount(3, $meModel->getModificationEvents());
        $this->assertInstanceOf(
            LongLifeModificationEventInterface::class,
            $meModel->getModificationEvents()[2],
        );
        $this->assertEquals('longLife', $meModel->getModificationEvents()[2]->getValue());
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
            return $event->getValue() === 123;
        })[0]->getValue());

        $this->assertEquals('longLife', $meModel->getModificationEvents(function (object $event) {
            return $event instanceof LongLifeModificationEventInterface;
        })[2]->getValue());

        $meModel->getModificationEvents(function (object $event) {
            return $event->getValue() === 123;
        })[0]->setDispatched();
        $this->assertCount(4, $meModel->getNotDispatchedModificationEvents());

        $this->assertInstanceOf(get_class($meModel), $meModel->cleanupDispatchedModificationEvents());
        $this->assertCount(4, $meModel->getModificationEvents());

        $this->assertInstanceOf(get_class($meModel), $meModel->cleanupModificationEvents(false));
        $this->assertCount(1, $meModel->getModificationEvents());

        $this->assertInstanceOf(get_class($meModel), $meModel->cleanupModificationEvents());
        $this->assertCount(0, $meModel->getModificationEvents());
    }
}
