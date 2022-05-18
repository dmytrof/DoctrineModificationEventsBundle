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
        };

        $this->assertEquals([], $modelWithModificationEvents->getModificationEvents());
        $modelWithModificationEvents->setSomething(123);
        $this->assertCount(1, $modelWithModificationEvents->getModificationEvents());
        $this->assertInstanceOf(ModificationEvent::class, $modelWithModificationEvents->getModificationEvents()[0]);
        $this->assertEquals(123, $modelWithModificationEvents->getModificationEvents()[0]->getValue());

        $modelWithModificationEvents->setSomething('qwer');
        $this->assertCount(2, $modelWithModificationEvents->getModificationEvents());
        $this->assertInstanceOf(ModificationEvent::class, $modelWithModificationEvents->getModificationEvents()[1]);
        $this->assertEquals('qwer', $modelWithModificationEvents->getModificationEvents()[1]->getValue());

        $modelWithModificationEvents->setLongLifeValue('longLife');
        $this->assertCount(3, $modelWithModificationEvents->getModificationEvents());
        $this->assertInstanceOf(LongLifeModificationEventInterface::class, $modelWithModificationEvents->getModificationEvents()[2]);
        $this->assertEquals('longLife', $modelWithModificationEvents->getModificationEvents()[2]->getValue());

        $this->assertEquals('123', $modelWithModificationEvents->getModificationEvents(function (ModificationEventInterface $event) {
            return $event->getValue() === 123;
        })[0]->getValue());

        $this->assertEquals('longLife', $modelWithModificationEvents->getModificationEvents(function (ModificationEventInterface $event) {
            return $event instanceof LongLifeModificationEventInterface;
        })[2]->getValue());

        $this->assertInstanceOf(get_class($modelWithModificationEvents), $modelWithModificationEvents->cleanupModificationEvents(false));
        $this->assertCount(1, $modelWithModificationEvents->getModificationEvents());

        $this->assertInstanceOf(get_class($modelWithModificationEvents), $modelWithModificationEvents->cleanupModificationEvents());
        $this->assertCount(0, $modelWithModificationEvents->getModificationEvents());
    }
}