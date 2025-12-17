<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event;

use Dmytrof\DoctrineModificationEventsBundle\Event\ForceFlushPreviousModificationsInterface;
use Dmytrof\DoctrineModificationEventsBundle\Event\ModificationEvent;
use Dmytrof\DoctrineModificationEventsBundle\Event\SingletonModificationEventInterface;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Entity\Category;

class GenerateLink extends ModificationEvent implements
    SingletonModificationEventInterface,
    ForceFlushPreviousModificationsInterface
{
    public function __construct(
        private Category $category,
    ) {
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getSingletonKey(): string
    {
        return self::class;
    }

    public function isRewriteExisted(): bool
    {
        return true;
    }
}
