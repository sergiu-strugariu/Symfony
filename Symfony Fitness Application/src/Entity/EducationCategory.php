<?php

namespace App\Entity;

use App\Repository\EducationCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EducationCategoryRepository::class)]
class EducationCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, EducationCategoryTranslation>
     */
    #[ORM\OneToMany(targetEntity: EducationCategoryTranslation::class, mappedBy: 'educationCategory')]
    private Collection $educationCategoryTranslations;

    private $defaultLocale = 'ro';

    /**
     * @var Collection<int, Education>
     */
    #[ORM\OneToMany(targetEntity: Education::class, mappedBy: 'category')]
    private Collection $education;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->educationCategoryTranslations = new ArrayCollection();
        $this->education = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

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

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

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
            $educationCategoryTranslation->setEducationCategory($this);
        }

        return $this;
    }

    public function removeEducationCategoryTranslation(EducationCategoryTranslation $educationCategoryTranslation): static
    {
        if ($this->educationCategoryTranslations->removeElement($educationCategoryTranslation)) {
            // set the owning side to null (unless already changed)
            if ($educationCategoryTranslation->getEducationCategory() === $this) {
                $educationCategoryTranslation->setEducationCategory(null);
            }
        }

        return $this;
    }

    public function getTranslation($locale)
    {
        $translations = $this->getEducationCategoryTranslations()->filter(function ($educationCategoryTranslation) use ($locale) {
            return $educationCategoryTranslation->getLanguage()->getLocale() === $locale;
        });

        if ($translations->isEmpty()) {
            return $this->getEducationCategoryTranslations()->filter(function ($educationCategoryTranslation) {
                return $educationCategoryTranslation->getLanguage()->getLocale() === $this->defaultLocale;
            })->first();
        }

        return $translations->first();
    }

    /**
     * @return Collection<int, Education>
     */
    public function getEducation(): Collection
    {
        return $this->education;
    }

    public function addEducation(Education $education): static
    {
        if (!$this->education->contains($education)) {
            $this->education->add($education);
            $education->setCategory($this);
        }

        return $this;
    }

    public function removeEducation(Education $education): static
    {
        if ($this->education->removeElement($education)) {
            // set the owning side to null (unless already changed)
            if ($education->getCategory() === $this) {
                $education->setCategory(null);
            }
        }

        return $this;
    }
}
