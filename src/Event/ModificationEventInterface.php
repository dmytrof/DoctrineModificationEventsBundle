<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Event;

interface ModificationEventInterface
{
    /**
     * Checks if dispatcher
     * @return bool
     */
    public function isDispatched(): bool;

    /**
     * Sets dispatched
     * @param bool $dispatched
     * @return $this
     */
    public function setDispatched(bool $dispatched = true): self;

    /**
     * Checks if flush needed
     * @return bool
     */
    public function isNeedsFlush(): bool;

    /**
     * Sets needs flush
     * @param bool $needsFlush
     * @return $this
     */
    public function setNeedsFlush(bool $needsFlush): self;
}