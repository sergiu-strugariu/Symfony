<?php

namespace App\Entity;

use App\Repository\PageSectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageSectionRepository::class)]
class PageSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pageSections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Page $page = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 99, unique: true)]
    private ?string $machineName = null;

    #[ORM\Column(length: 99, nullable: true)]
    private ?string $fileName = null;

    #[ORM\Column(length: 99, nullable: true)]
    private ?string $fileNameMob = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $classes = null;

    #[ORM\Column(length: 99)]
    private ?string $template = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, PageWidget>
     */
    #[ORM\OneToMany(targetEntity: PageWidget::class, mappedBy: 'pageSection', orphanRemoval: true)]
    private Collection $pageWidgets;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->pageWidgets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMachineName(): ?string
    {
        return $this->machineName;
    }

    public function setMachineName(string $machineName): static
    {
        $this->machineName = $machineName;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileNameMob(): ?string
    {
        return $this->fileNameMob;
    }

    public function setFileNameMob(?string $fileNameMob): static
    {
        $this->fileNameMob = $fileNameMob;

        return $this;
    }

    public function getClasses(): ?string
    {
        return $this->classes;
    }

    public function setClasses(?string $classes): static
    {
        $this->classes = $classes;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(string $template): static
    {
        $this->template = $template;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, PageWidget>
     */
    public function getPageWidgets(): Collection
    {
        return $this->pageWidgets;
    }

    public function addPageWidget(PageWidget $pageWidget): static
    {
        if (!$this->pageWidgets->contains($pageWidget)) {
            $this->pageWidgets->add($pageWidget);
            $pageWidget->setPageSection($this);
        }

        return $this;
    }

    public function removePageWidget(PageWidget $pageWidget): static
    {
        if ($this->pageWidgets->removeElement($pageWidget)) {
            // set the owning side to null (unless already changed)
            if ($pageWidget->getPageSection() === $this) {
                $pageWidget->setPageSection(null);
            }
        }

        return $this;
    }
}
