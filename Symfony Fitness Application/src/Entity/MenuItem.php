<?php

namespace App\Entity;

use App\Repository\MenuItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

#[ORM\Entity(repositoryClass: MenuItemRepository::class)]
class MenuItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $cssClass = null;

    #[ORM\Column(length: 256, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $weight = 0;

    #[ORM\ManyToOne(inversedBy: 'menuItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Menu $menu = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'menuItems')]
    #[ORM\JoinColumn(name: "menu_item_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private ?self $menuItem = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'menuItem')]
    private Collection $menuItems;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $createdAt;

    /**
     * @var Collection<int, MenuItemTranslation>
     */
    #[ORM\OneToMany(targetEntity: MenuItemTranslation::class, mappedBy: 'menuItem', orphanRemoval: true)]
    private Collection $menuItemTranslations;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->menuItems = new ArrayCollection();
        $this->menuItemTranslations = new ArrayCollection();
    }

    #[PrePersist]
    #[PreUpdate]
    public function validateMenuItem(): void
    {
        if ($this->menuItem === $this) {
            throw new \InvalidArgumentException("A menu item cannot be its own parent.");
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCssClass(): ?string
    {
        return $this->cssClass;
    }

    public function setCssClass(?string $cssClass): static
    {
        $this->cssClass = $cssClass;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }

    public function getMenuItem(): ?self
    {
        return $this->menuItem;
    }

    public function setMenuItem(?self $menuItem): static
    {
        if ($menuItem === $this) {
            throw new \InvalidArgumentException("A menu item cannot be its own parent.");
        }

        $this->menuItem = $menuItem;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getMenuItems(): Collection
    {
        return $this->menuItems;
    }

    public function addMenuItem(self $menuItem): static
    {
        if (!$this->menuItems->contains($menuItem)) {
            $this->menuItems->add($menuItem);
            $menuItem->setMenuItem($this);
        }

        return $this;
    }

    public function removeMenuItem(self $menuItem): static
    {
        if ($this->menuItems->removeElement($menuItem)) {
            // set the owning side to null (unless already changed)
            if ($menuItem->getMenuItem() === $this) {
                $menuItem->setMenuItem(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MenuItemTranslation>
     */
    public function getMenuItemTranslations(): Collection
    {
        return $this->menuItemTranslations;
    }

    public function getMenuItemTranslation($locale): ?MenuItemTranslation
    {
        foreach ($this->menuItemTranslations as $translation) {
            if ($translation->getLanguage()->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    public function addMenuItemTranslation(MenuItemTranslation $menuItemTranslation): static
    {
        if (!$this->menuItemTranslations->contains($menuItemTranslation)) {
            $this->menuItemTranslations->add($menuItemTranslation);
            $menuItemTranslation->setMenuItem($this);
        }

        return $this;
    }

    public function removeMenuItemTranslation(MenuItemTranslation $menuItemTranslation): static
    {
        if ($this->menuItemTranslations->removeElement($menuItemTranslation)) {
            // set the owning side to null (unless already changed)
            if ($menuItemTranslation->getMenuItem() === $this) {
                $menuItemTranslation->setMenuItem(null);
            }
        }

        return $this;
    }

    public function resetMenuItems(): void
    {
        if ($this->menuItems->count() >= 1) {
            foreach ($this->menuItems as $menuItem) {
                $menuItem->setMenuItem(null);
            }
        }
    }
}
