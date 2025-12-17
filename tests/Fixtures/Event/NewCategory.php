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

use Dmytrof\DoctrineModificationEventsBundle\Event\ModificationEvent;
use Dmytrof\DoctrineModificationEventsBundle\Event\TrackedModificationEventInterface;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Entity\Category;

class NewCategory extends ModificationEvent implements TrackedModificationEventInterface
{
    public function __construct(
        private Category $category,
    ) {
    }

    public function getCategory(): Category
    {
        return $this->category;
    }
}
