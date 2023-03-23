<?php /** @noinspection PhpIllegalPsrClassPathInspection */

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
use Dmytrof\DoctrineModificationEventsBundle\Event\ModificationEventInterface;
use Dmytrof\DoctrineModificationEventsBundle\Model\{ModificationEventsInterface, Traits\ModificationEventsTrait};
use PHPUnit\Framework\TestCase;

class ModificationEventsTraitTest extends TestCase
{
    public function testModificationEvents()
    {
        $modelWithModificationEvents = new class implements ModificationEventsInterface {
            use ModificationEventsTrait;

            public function setSomething($value): void
            {
                $logEvent = new class ($value) extends ModificationEvent {

                    private $value;

                    public function __construct($value)
                    {
                        $this->value = $value;
                    }

                    /**
                     * @return mixed
                     */
                    public function getValue()
                    {
                        return $this->value;
                    }
                };
                $this->addModificationEvent($logEvent);
            }

            public function setLongLifeValue($value): void
            {
                $logEvent = new class ($value) extends ModificationEvent implements LongLifeModificationEventInterface {

                    private $value;

                    public function __construct($value)
                    {
                        $this->value = $value;
                    }

                    /**
                     * @return mixed
                     */
                    public function getValue()
                    {
                        return $this->value;
                    }
                };
                $this->addModificationEvent($logEvent);
            }

            public function setPrioritizedValue($value, int $priority = 100): void
            {
                $logEvent = new class ($value) extends ModificationEvent {

                    private $value;

                    public function __construct($value)
                    {
                        $this->value = $value;
                    }

                    /**
                     * @return mixed
                     */
                    public function getValue()
                    {
                        return $this->value;
                    }
                };
                $logEvent->setPriority($priority);
                $this->addModificationEvent($logEvent);
            }
        };

        $this->assertEquals([], $modelWithModificationEvents->getModificationEvents());
        $modelWithModificationEvents->setSomething(123);
        $this->assertCount(1, $modelWithModificationEvents->getModificationEvents());
        $this->assertInstanceOf(ModificationEvent::class, $modelWithModificationEvents->getModificationEvents()[0]);
        $this->assertEquals(123, $modelWithModificationEvents->getModificationEvents()[0]->getValue());
        $this->assertEquals(0, $modelWithModificationEvents->getModificationEvents()[0]->getPriority());

        $modelWithModificationEvents->setSomething('qwer');
        $this->assertCount(2, $modelWithModificationEvents->getModificationEvents());
        $this->assertInstanceOf(ModificationEvent::class, $modelWithModificationEvents->getModificationEvents()[1]);
        $this->assertEquals('qwer', $modelWithModificationEvents->getModificationEvents()[1]->getValue());
        $this->assertEquals(0, $modelWithModificationEvents->getModificationEvents()[1]->getPriority());

        $modelWithModificationEvents->setLongLifeValue('longLife');
        $this->assertCount(3, $modelWithModificationEvents->getModificationEvents());
        $this->assertInstanceOf(LongLifeModificationEventInterface::class, $modelWithModificationEvents->getModificationEvents()[2]);
        $this->assertEquals('longLife', $modelWithModificationEvents->getModificationEvents()[2]->getValue());
        $this->assertEquals(0, $modelWithModificationEvents->getModificationEvents()[2]->getPriority());

        $modelWithModificationEvents->setPrioritizedValue('priority-99', -99);
        $this->assertCount(4, $modelWithModificationEvents->getModificationEvents());
        $this->assertInstanceOf(ModificationEvent::class, $modelWithModificationEvents->getModificationEvents()[3]);
        $this->assertEquals('priority-99', $modelWithModificationEvents->getModificationEvents()[3]->getValue());
        $this->assertEquals(-99, $modelWithModificationEvents->getModificationEvents()[3]->getPriority());

        $modelWithModificationEvents->setPrioritizedValue('priority100', 100);
        $this->assertCount(5, $modelWithModificationEvents->getModificationEvents());
        $this->assertInstanceOf(ModificationEvent::class, $modelWithModificationEvents->getModificationEvents()[4]);
        $this->assertEquals('priority100', $modelWithModificationEvents->getModificationEvents()[4]->getValue());
        $this->assertEquals(100, $modelWithModificationEvents->getModificationEvents()[4]->getPriority());

        $this->assertCount(5, $notDispatchedEvents = $modelWithModificationEvents->getNotDispatchedModificationEvents());

        $this->assertEquals('priority100', $notDispatchedEvents[0]->getValue());
        $this->assertEquals('priority-99', $notDispatchedEvents[4]->getValue());

        $this->assertEquals('123', $modelWithModificationEvents->getModificationEvents(function (ModificationEventInterface $event) {
            return $event->getValue() === 123;
        })[0]->getValue());

        $this->assertEquals('longLife', $modelWithModificationEvents->getModificationEvents(function (ModificationEventInterface $event) {
            return $event instanceof LongLifeModificationEventInterface;
        })[2]->getValue());

        $modelWithModificationEvents->getModificationEvents(function (ModificationEventInterface $event) {
            return $event->getValue() === 123;
        })[0]->setDispatched();
        $this->assertCount(4, $modelWithModificationEvents->getNotDispatchedModificationEvents());

        $this->assertInstanceOf(get_class($modelWithModificationEvents), $modelWithModificationEvents->cleanupDispatchedModificationEvents());
        $this->assertCount(4, $modelWithModificationEvents->getModificationEvents());

        $this->assertInstanceOf(get_class($modelWithModificationEvents), $modelWithModificationEvents->cleanupModificationEvents(false));
        $this->assertCount(1, $modelWithModificationEvents->getModificationEvents());

        $this->assertInstanceOf(get_class($modelWithModificationEvents), $modelWithModificationEvents->cleanupModificationEvents());
        $this->assertCount(0, $modelWithModificationEvents->getModificationEvents());
    }
}
