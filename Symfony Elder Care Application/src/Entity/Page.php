<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;

/**
 * @ORM\Entity(repositoryClass=PageRepository::class)
 */
class Page
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=99)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=99, unique=true)
     */
    private ?string $machineName;

    /**
     * @ORM\Column(type="string", length=99)
     */
    private ?string $url;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private ?string $classes;

    /**
     * @ORM\Column(type="string", length=199, nullable=true)
     */
    private ?string $metaTitle;

    /**
     * @ORM\Column(type="string", length=199, nullable=true)
     */
    private ?string $metaDescription;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $deletedAt;

    /**
     * @ORM\OneToMany(targetEntity=PageSection::class, mappedBy="page", orphanRemoval=true)
     * @OrderBy({"weight" = "ASC"})
     */
    private Collection $pageSections;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->pageSections = new ArrayCollection();
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

    public function getMachineName(): ?string
    {
        return $this->machineName;
    }

    public function setMachineName(string $machineName): self
    {
        $this->machineName = $machineName;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getClasses(): ?string
    {
        return $this->classes;
    }

    public function setClasses(string $classes): self
    {
        $this->classes = $classes;

        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Collection<int, PageSection>
     */
    public function getPageSections(): Collection
    {
        return $this->pageSections;
    }

    public function addPageSection(PageSection $pageSection): self
    {
        if (!$this->pageSections->contains($pageSection)) {
            $this->pageSections[] = $pageSection;
            $pageSection->setPage($this);
        }

        return $this;
    }

    public function removePageSection(PageSection $pageSection): self
    {
        if ($this->pageSections->removeElement($pageSection)) {
            // set the owning side to null (unless already changed)
            if ($pageSection->getPage() === $this) {
                $pageSection->setPage(null);
            }
        }

        return $this;
    }
}
