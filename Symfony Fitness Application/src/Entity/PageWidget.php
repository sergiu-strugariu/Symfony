<?php

namespace App\Entity;

use App\Repository\PageWidgetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageWidgetRepository::class)]
class PageWidget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 99, unique: true)]
    private ?string $machineName = null;

    #[ORM\ManyToOne(inversedBy: 'pageWidgets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PageSection $pageSection = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $classes = null;

    #[ORM\Column(length: 99, nullable: true)]
    private ?string $fileName = null;

    #[ORM\Column(length: 99, nullable: true)]
    private ?string $fileNameMob = null;

    #[ORM\Column(length: 99)]
    private ?string $template = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;
    
    private $defaultLocale = 'ro';

    /**
     * @var Collection<int, PageWidgetTranslation>
     */
    #[ORM\OneToMany(targetEntity: PageWidgetTranslation::class, mappedBy: 'pageWidget', orphanRemoval: true)]
    private Collection $pageWidgetTranslations;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->pageWidgetTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPageSection(): ?PageSection
    {
        return $this->pageSection;
    }

    public function setPageSection(?PageSection $pageSection): static
    {
        $this->pageSection = $pageSection;

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
     * @return Collection<int, PageWidgetTranslation>
     */
    public function getPageWidgetTranslations(): Collection
    {
        return $this->pageWidgetTranslations;
    }
    
    public function getTranslation($locale)
    {
        $translations = $this->getPageWidgetTranslations()->filter(function ($pageWidgetTranslations) use ($locale) {
            return $pageWidgetTranslations->getLanguage()->getLocale() === $locale;
        });
        
        if ($translations->isEmpty()) {
            return $this->getPageWidgetTranslations()->filter(function ($pageWidgetTranslations) {
                return $pageWidgetTranslations->getLanguage()->getLocale() === $this->defaultLocale;
            })->first();
        }
        
        return $translations->first();
    }

    public function addPageWidgetTranslation(PageWidgetTranslation $pageWidgetTranslation): static
    {
        if (!$this->pageWidgetTranslations->contains($pageWidgetTranslation)) {
            $this->pageWidgetTranslations->add($pageWidgetTranslation);
            $pageWidgetTranslation->setPageWidget($this);
        }

        return $this;
    }

    public function removePageWidgetTranslation(PageWidgetTranslation $pageWidgetTranslation): static
    {
        if ($this->pageWidgetTranslations->removeElement($pageWidgetTranslation)) {
            // set the owning side to null (unless already changed)
            if ($pageWidgetTranslation->getPageWidget() === $this) {
                $pageWidgetTranslation->setPageWidget(null);
            }
        }

        return $this;
    }
}
