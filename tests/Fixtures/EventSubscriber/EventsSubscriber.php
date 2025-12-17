<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\EventSubscriber;

use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Entity\Category;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\GenerateLink;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\NewCategory;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\NotifyChangedName;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\NotifyFromChangedName;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\UpdateCategorySlug;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Service\CategoryNotifier;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UpdateCategorySlug::class, method: 'updateCategorySlug')]
#[AsEventListener(event: NotifyChangedName::class, method: 'notify')]
#[AsEventListener(event: NotifyFromChangedName::class, method: 'notify')]
#[AsEventListener(event: NewCategory::class, method: 'newCategory')]
#[AsEventListener(event: GenerateLink::class, method: 'generateLink')]
class EventsSubscriber
{
    public function __construct(
        private readonly CategoryNotifier $categoryNotifier,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function updateCategorySlug(UpdateCategorySlug $event): void
    {
        $category = $event->getCategory();
        $inflector = InflectorFactory::create()->build();

        $category->setSlug($inflector->urlize($category->getName()));
        $event->setNeedsFlush(true);
    }

    public function notify(NotifyChangedName $event): void
    {
        $this->categoryNotifier->notify($event);
    }

    public function newCategory(NewCategory $event): void
    {
        // Some handler here
    }

    public function generateLink(GenerateLink $event): void
    {
        $builder = $this->entityManager->getRepository(Category::class)->createQueryBuilder('c');
        $builder
            ->select('c.slug')
            ->where('c.id = :id')
            ->setParameter('id', $event->getCategory()->getId());
        try {
            $slug = $builder->getQuery()->getSingleScalarResult();
        } catch (NoResultException) {
            $slug = '';
        }

        $event->getCategory()->setLink('/category/' . $slug);
        $event->setNeedsFlush(true);
    }
}
