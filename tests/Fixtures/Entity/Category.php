<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Entity;

use Dmytrof\DoctrineModificationEventsBundle\Model\ModificationEventsInterface;
use Dmytrof\DoctrineModificationEventsBundle\Model\Traits\ModificationEventsTrait;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\GenerateLink;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\NewCategory;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\NotifyChangedName;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\NotifyFromChangedName;
use Dmytrof\DoctrineModificationEventsBundle\Tests\Fixtures\Event\UpdateCategorySlug;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Category implements ModificationEventsInterface
{
    use ModificationEventsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $link = null;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
        $this->addModificationEvent(new NewCategory($this)); // Tracked
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->addModificationEvent(new NotifyFromChangedName($this->name ?? '')); // Singleton not updatable
        $this->name = $name;
        $this->addModificationEvent(new NotifyChangedName($this->name)); // Singleton updatable
        $this->addModificationEvent(new UpdateCategorySlug($this)); // Regular
        $this->addModificationEvent(new GenerateLink($this)); // Force flush previous

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }
}
