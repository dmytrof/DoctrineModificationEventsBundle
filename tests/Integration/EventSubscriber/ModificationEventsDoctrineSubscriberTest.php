<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Tests\Integration\EventSubscriber;

use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Entity\Category;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\NewCategory;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\NotifyChangedName;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\NotifyFromChangedName;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Service\CategoryNotifier;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Integration\IntegrationTestCase;
use Doctrine\Inflector\InflectorFactory;

class ModificationEventsDoctrineSubscriberTest extends IntegrationTestCase
{
    public function testPersistFlow(): void
    {
        $categoryNotifier = self::getContainer()->get(CategoryNotifier::class);
        $category = new Category('Test')->setName('Some name 1');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $this->assertEquals([
            [
                'class' => NotifyFromChangedName::class,
                'name' => 'Test',
            ],
            [
                'class' => NotifyChangedName::class,
                'name' => 'Some name 1',
            ],
        ], $categoryNotifier->getNotifications());

        $id = $category->getId();
        $inflector = InflectorFactory::create()->build();

        $this->assertSame('Some name 1', $category->getName());
        $this->assertSame($inflector->urlize($category->getName()), $category->getSlug());
        $this->assertSame('/category/' . $inflector->urlize($category->getName()), $category->getLink());
        $this->entityManager->detach($category);

        $fetchedCategory = $this->entityManager->getReference(Category::class, $id);
        $this->assertSame('Some name 1', $fetchedCategory->getName());
        $this->assertSame($inflector->urlize($fetchedCategory->getName()), $fetchedCategory->getSlug());
        $this->assertSame('/category/' . $inflector->urlize($category->getName()), $category->getLink());
    }

    public function testUpdateFlow(): void
    {
        $categoryNotifier = self::getContainer()->get(CategoryNotifier::class);
        $category = new Category('Test')->setName('Some name 1');

        $this->assertCount(1, $category->getModificationEventsOfClass(NewCategory::class));
        $this->assertCount(1, $category->getModificationEventsOfClass(NotifyChangedName::class));
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // Check regular event
        $this->assertCount(0, $category->getModificationEventsOfClass(NotifyChangedName::class));
        // Check TrackedModificationEventInterface event
        $this->assertCount(1, $category->getModificationEventsOfClass(NewCategory::class));

        $this->assertEquals([
            [
                'class' => NotifyFromChangedName::class,
                'name' => 'Test',
            ],
            [
                'class' => NotifyChangedName::class,
                'name' => 'Some name 1',
            ],
        ], $categoryNotifier->getNotifications());

        $id = $category->getId();
        $inflector = InflectorFactory::create()->build();

        $this->entityManager->detach($category);

        $category = $this->entityManager->getRepository(Category::class)->find($id);

        // Check regular event
        $this->assertCount(0, $category->getModificationEvents());

        $this->assertSame('Some name 1', $category->getName());
        $this->assertSame($inflector->urlize($category->getName()), $category->getSlug());
        // Check Force flush previous event
        $this->assertSame('/category/' . $inflector->urlize($category->getName()), $category->getLink());

        // Test SingletonModificationEventInterface events
        $category->setName('New name');
        $category->setName('Newest name');

        // Check SingletonModificationEventInterface events
        $this->assertCount(5, $category->getModificationEvents());
        $this->assertSame('/category/' . $inflector->urlize('Some name 1'), $category->getLink());

        $this->entityManager->flush();

        // Check SingletonModificationEventInterface events
        $this->assertEquals([
            [
                'class' => NotifyFromChangedName::class,
                'name' => 'Test',
            ],
            [
                'class' => NotifyChangedName::class,
                'name' => 'Some name 1',
            ],
            [
                'class' => NotifyFromChangedName::class,
                'name' => 'Some name 1',
            ],
            [
                'class' => NotifyChangedName::class,
                'name' => 'Newest name',
            ],
        ], $categoryNotifier->getNotifications());

        $this->entityManager->detach($category);

        $fetchedCategory = $this->entityManager->getRepository(Category::class)->find($id);
        $this->assertSame('Newest name', $fetchedCategory->getName());
        $this->assertSame($inflector->urlize($fetchedCategory->getName()), $fetchedCategory->getSlug());
        $this->assertSame('/category/' . $inflector->urlize($category->getName()), $category->getLink());
    }

    public function testRemoveFlow(): void
    {
        $category = new Category()->setName('Some name 1');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $id = $category->getId();
        $inflector = InflectorFactory::create()->build();

        $this->entityManager->detach($category);

        $category = $this->entityManager->getRepository(Category::class)->find($id);

        $this->assertSame('Some name 1', $category->getName());
        $this->assertSame($inflector->urlize($category->getName()), $category->getSlug());

        $category->setName('Removed name');
        $this->entityManager->remove($category);
        $this->entityManager->flush();

        $this->assertSame('Removed name', $category->getName());
        $this->assertSame($inflector->urlize($category->getName()), $category->getSlug());
        $this->assertNull($this->entityManager->getRepository(Category::class)->find($id));
    }
}
