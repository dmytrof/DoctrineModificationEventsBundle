<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Service;

use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\NotifyChangedName;

class CategoryNotifier
{
    private array $notifications = [];

    public function notify(NotifyChangedName $event): void
    {
        $this->notifications[] = [
            'class' => $event::class,
            'name' => $event->getName(),
        ];
    }

    public function getNotifications(): array
    {
        return $this->notifications;
    }
}
