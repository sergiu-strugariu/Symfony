<?php

namespace App\Entity;

use App\Repository\CookMenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CookMenuRepository::class)
 */
class CookMenu
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\OneToMany(targetEntity=CookMenuItem::class, mappedBy="cookMenu", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $cookMenuItems;
    
    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $uid;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="date")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date")
     */
    private $endDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observations;

    /**
     * @ORM\ManyToOne(targetEntity=NursingHome::class, inversedBy="cookMenus")
     * @ORM\JoinColumn(nullable=true)
     */
    private $nursingHome;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->cookMenuItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): self
    {
        $this->observations = $observations;

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

    /**
     * @return Collection<int, CookMenuItem>
     */
    public function getCookMenuItems(): Collection
    {
        return $this->cookMenuItems;
    }

    public function addCookMenuItem(CookMenuItem $cookMenuItem): self
    {
        if (!$this->cookMenuItems->contains($cookMenuItem)) {
            $this->cookMenuItems[] = $cookMenuItem;
            $cookMenuItem->setCookMenu($this);
        }

        return $this;
    }

    public function removeCookMenuItem(CookMenuItem $cookMenuItem): self
    {
        if ($this->cookMenuItems->removeElement($cookMenuItem)) {
            // set the owning side to null (unless already changed)
            if ($cookMenuItem->getCookMenu() === $this) {
                $cookMenuItem->setCookMenu(null);
            }
        }

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getNursingHome(): ?NursingHome
    {
        return $this->nursingHome;
    }

    public function setNursingHome(?NursingHome $nursingHome): self
    {
        $this->nursingHome = $nursingHome;

        return $this;
    }
}
