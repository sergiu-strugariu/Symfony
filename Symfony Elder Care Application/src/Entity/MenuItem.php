<?php

namespace App\Entity;

use App\Repository\MenuItemRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MenuItemRepository::class)
 */
class MenuItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="menuItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Menu $menu;

    /**
     * @ORM\ManyToOne(targetEntity="MenuItem", inversedBy="menuItems")
     */
    private ?MenuItem $menuItem;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private ?string $cssClass;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $icon;

    /**
     * @ORM\Column(type="integer")
     */
    private int $weight = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $createdAt;

    /**
     * @var Collection<int, MenuItem>
     * @ORM\OneToMany(targetEntity="MenuItem", mappedBy="menuItem")
     */
    private $menuItems;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $link;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $linkText;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->menuItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCssClass(): ?string
    {
        return $this->cssClass;
    }

    public function setCssClass(?string $cssClass): self
    {
        $this->cssClass = $cssClass;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;

        return $this;
    }

    public function getMenuItem(): ?self
    {
        return $this->menuItem;
    }

    public function setMenuItem(?self $menuItem): self
    {
        $this->menuItem = $menuItem;

        return $this;
    }

    /**
     * @return Collection<int, MenuItem>
     */
    public function getMenuItems(): Collection
    {
        return $this->menuItems;
    }

    public function addMenuItem(self $menuItem): self
    {
        if (!$this->menuItems->contains($menuItem)) {
            $this->menuItems[] = $menuItem;
            $menuItem->setMenuItem($this);
        }

        return $this;
    }

    public function removeMenuItem(self $menuItem): self
    {
        if ($this->menuItems->removeElement($menuItem)) {
            // set the owning side to null (unless already changed)
            if ($menuItem->getMenuItem() === $this) {
                $menuItem->setMenuItem(null);
            }
        }

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getLinkText(): ?string
    {
        return $this->linkText;
    }

    public function setLinkText(string $linkText): self
    {
        $this->linkText = $linkText;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return void
     */
    public function resetMenuItems(): void
    {
        if ($this->menuItems->count() >= 1) {
            foreach ($this->menuItems as $menuItem) {
                $menuItem->setMenuItem(null);
            }
        }
    }
}