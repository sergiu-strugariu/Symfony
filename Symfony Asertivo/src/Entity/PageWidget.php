<?php

namespace App\Entity;

use App\Repository\PageWidgetRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PageWidgetRepository::class)
 */
class PageWidget
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=PageSection::class, inversedBy="pageWidgets")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?PageSection $pageSection;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=99, unique=true)
     */
    private ?string $machineName;

    /**
     * @ORM\Column(type="string", length=99)
     */
    private ?string $template;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $weight;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $isActive;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private ?string $classes;

    /**
     * @ORM\Column(type="string", length=99, nullable=true)
     */
    private ?string $fileName;

    /**
     * @ORM\Column(type="string", length=99, nullable=true)
     */
    private ?string $fileNameMob;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $link;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $linkText;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private array $links = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private array $galleries = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->isActive = 0;
        $this->weight = 0;
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPageSection(): ?PageSection
    {
        return $this->pageSection;
    }

    public function setPageSection(?PageSection $pageSection): self
    {
        $this->pageSection = $pageSection;

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

    public function getClasses(): ?string
    {
        return $this->classes;
    }

    public function setClasses(?string $classes): self
    {
        $this->classes = $classes;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileNameMob(): ?string
    {
        return $this->fileNameMob;
    }

    public function setFileNameMob(?string $fileNameMob): self
    {
        $this->fileNameMob = $fileNameMob;

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

    public function getLinkText(): ?string
    {
        return $this->linkText;
    }

    public function setLinkText(?string $linkText): self
    {
        $this->linkText = $linkText;

        return $this;
    }

    public function getIsActive(): ?int
    {
        return $this->isActive ? 1 : 0;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }


    public function getGalleries(): ?array
    {
        return $this->galleries;
    }

    public function setGalleries(?array $galleries): self
    {
        $this->galleries = $galleries;

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

    public function getLinks(): ?array
    {
        return $this->links;
    }

    public function setLinks(?array $links): self
    {
        $this->links = $links;

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
}
