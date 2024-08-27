<?php

namespace App\Entity;

use App\Repository\CategoryCareRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CategoryCareRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'A category with this name already exists.', errorPath: 'title')]
class CategoryCare
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, CategoryCareTranslation>
     */
    #[ORM\OneToMany(targetEntity: CategoryCareTranslation::class, mappedBy: 'categoryCare', orphanRemoval: true)]
    private Collection $categoryCareTranslations;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\ManyToMany(targetEntity: Company::class, mappedBy: 'categoryCares')]
    private Collection $companies;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->categoryCareTranslations = new ArrayCollection();
        $this->companies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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
     * @param $locale
     * @return CategoryCareTranslation|null
     */
    public function getTranslation($locale): ?CategoryCareTranslation
    {
        foreach ($this->categoryCareTranslations as $translation) {
            if ($translation->getLanguage()->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, CategoryCareTranslation>
     */
    public function getCategoryCareTranslations(): Collection
    {
        return $this->categoryCareTranslations;
    }

    public function addCategoryCareTranslation(CategoryCareTranslation $categoryCareTranslation): static
    {
        if (!$this->categoryCareTranslations->contains($categoryCareTranslation)) {
            $this->categoryCareTranslations->add($categoryCareTranslation);
            $categoryCareTranslation->setCategoryCare($this);
        }

        return $this;
    }

    public function removeCategoryCareTranslation(CategoryCareTranslation $categoryCareTranslation): static
    {
        if ($this->categoryCareTranslations->removeElement($categoryCareTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryCareTranslation->getCategoryCare() === $this) {
                $categoryCareTranslation->setCategoryCare(null);
            }
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => self::STATUS_DRAFT,
            self::STATUS_PUBLISHED => self::STATUS_PUBLISHED
        ];
    }

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): static
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
            $company->addCategoryCare($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        if ($this->companies->removeElement($company)) {
            $company->removeCategoryCare($this);
        }

        return $this;
    }
}
