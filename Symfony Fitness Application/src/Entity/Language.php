<?php

namespace App\Entity;

use App\Repository\LanguageRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: LanguageRepository::class)]
#[UniqueEntity(fields: ['locale'], message: 'A language aleardy exists with this locale.', errorPath: 'locale')]
class Language
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $name = null;

    #[ORM\Column(length: 2, unique: true)]
    private ?string $locale = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, MenuItemTranslation>
     */
    #[ORM\OneToMany(targetEntity: MenuItemTranslation::class, mappedBy: 'language', orphanRemoval: true)]
    private Collection $menuItemTranslations;

    /**
     * @var Collection<int, PageWidgetTranslation>
     */
    #[ORM\OneToMany(targetEntity: PageWidgetTranslation::class, mappedBy: 'language', orphanRemoval: true)]
    private Collection $pageWidgetTranslations;

    /**
     * @var Collection<int, CertificationTranslation>
     */
    #[ORM\OneToMany(targetEntity: CertificationTranslation::class, mappedBy: 'language')]
    private Collection $certificationTranslations;

    #[ORM\OneToMany(targetEntity: EducationScheduleTranslation::class, mappedBy: 'language')]
    private Collection $educationScheduleTranslations;

    /**
     * @var Collection<int, TeamMemberTranslation>
     */
    #[ORM\OneToMany(targetEntity: TeamMemberTranslation::class, mappedBy: 'language')]
    private Collection $teamMemberTranslations;

    /**
     * @var Collection<int, EducationCategoryTranslation>
     */
    #[ORM\OneToMany(targetEntity: EducationCategoryTranslation::class, mappedBy: 'language')]
    private Collection $educationCategoryTranslations;

    public function __construct()
    {
        $this->menuItemTranslations = new ArrayCollection();
        $this->pageWidgetTranslations = new ArrayCollection();
        $this->certificationTranslations = new ArrayCollection();
        $this->educationScheduleTranslations = new ArrayCollection();
        $this->teamMemberTranslations = new ArrayCollection();
        $this->educationCategoryTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Collection<int, PageWidgetTranslation>
     */
    public function getPageWidgetTranslations(): Collection
    {
        return $this->pageWidgetTranslations;
    }

    public function addPageWidgetTranslation(PageWidgetTranslation $pageWidgetTranslation): static
    {
        if (!$this->pageWidgetTranslations->contains($pageWidgetTranslation)) {
            $this->pageWidgetTranslations->add($pageWidgetTranslation);
            $pageWidgetTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removePageWidgetTranslation(PageWidgetTranslation $pageWidgetTranslation): static
    {
        if ($this->pageWidgetTranslations->removeElement($pageWidgetTranslation)) {
            // set the owning side to null (unless already changed)
            if ($pageWidgetTranslation->getLanguage() === $this) {
                $pageWidgetTranslation->setLanguage(null);
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'locale' => $this->getLocale(),
            'deletedAt' => $this->getDeletedAt()
        ];
    }

    /**
     * @return Collection<int, PageWidgetTranslation>
     */
    public function getLanguage(): Collection
    {
        return $this->language;
    }

    public function addLanguage(PageWidgetTranslation $language): static
    {
        if (!$this->language->contains($language)) {
            $this->language->add($language);
            $language->setLanguage($this);
        }

        return $this;
    }

    public function removeLanguage(PageWidgetTranslation $language): static
    {
        if ($this->language->removeElement($language)) {
            // set the owning side to null (unless already changed)
            if ($language->getLanguage() === $this) {
                $language->setLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CertificationTranslation>
     */
    public function getCertificationTranslations(): Collection
    {
        return $this->certificationTranslations;
    }

    public function addCertificationTranslation(CertificationTranslation $certificationTranslation): static
    {
        if (!$this->certificationTranslations->contains($certificationTranslation)) {
            $this->certificationTranslations->add($certificationTranslation);
            $certificationTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeCertificationTranslation(CertificationTranslation $certificationTranslation): static
    {
        if ($this->certificationTranslations->removeElement($certificationTranslation)) {
            // set the owning side to null (unless already changed)
            if ($certificationTranslation->getLanguage() === $this) {
                $certificationTranslation->setLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EducationScheduleTranslation>
     */
    public function getEducationScheduleTranslations(): Collection
    {
        return $this->educationScheduleTranslations;
    }

    public function addEducationScheduleTranslation(EducationScheduleTranslation $educationScheduleTranslation): static
    {
        if (!$this->certificationTranslations->contains($educationScheduleTranslation)) {
            $this->certificationTranslations->add($educationScheduleTranslation);
            $educationScheduleTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeEducationScheduleTranslation(EducationScheduleTranslation $educationScheduleTranslation): static
    {
        if ($this->certificationTranslations->removeElement($educationScheduleTranslation)) {
            // set the owning side to null (unless already changed)
            if ($educationScheduleTranslation->getLanguage() === $this) {
                $educationScheduleTranslation->setLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TeamMemberTranslation>
     */
    public function getTeamMemberTranslations(): Collection
    {
        return $this->teamMemberTranslations;
    }

    public function addTeamMemberTranslation(TeamMemberTranslation $teamMemberTranslation): static
    {
        if (!$this->teamMemberTranslations->contains($teamMemberTranslation)) {
            $this->teamMemberTranslations->add($teamMemberTranslation);
            $teamMemberTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeTeamMemberTranslation(TeamMemberTranslation $teamMemberTranslation): static
    {
        if ($this->teamMemberTranslations->removeElement($teamMemberTranslation)) {
            // set the owning side to null (unless already changed)
            if ($teamMemberTranslation->getLanguage() === $this) {
                $teamMemberTranslation->setLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EducationCategoryTranslation>
     */
    public function getEducationCategoryTranslations(): Collection
    {
        return $this->educationCategoryTranslations;
    }

    public function addEducationCategoryTranslation(EducationCategoryTranslation $educationCategoryTranslation): static
    {
        if (!$this->educationCategoryTranslations->contains($educationCategoryTranslation)) {
            $this->educationCategoryTranslations->add($educationCategoryTranslation);
            $educationCategoryTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeEducationCategoryTranslation(EducationCategoryTranslation $educationCategoryTranslation): static
    {
        if ($this->educationCategoryTranslations->removeElement($educationCategoryTranslation)) {
            // set the owning side to null (unless already changed)
            if ($educationCategoryTranslation->getLanguage() === $this) {
                $educationCategoryTranslation->setLanguage(null);
            }
        }

        return $this;
    }
}
