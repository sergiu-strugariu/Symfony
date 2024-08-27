<?php

namespace App\Entity;

use App\Repository\CategoryServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CategoryServiceRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'A category with this name already exists.', errorPath: 'title')]
class CategoryService
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
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
     * @var Collection<int, CategoryServiceTranslation>
     */
    #[ORM\OneToMany(targetEntity: CategoryServiceTranslation::class, mappedBy: 'categoryService', orphanRemoval: true)]
    private Collection $categoryServiceTranslations;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\ManyToMany(targetEntity: Company::class, mappedBy: 'categoryServices')]
    private Collection $companies;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->categoryServiceTranslations = new ArrayCollection();
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
     * @return CategoryServiceTranslation|null
     */
    public function getTranslation($locale): ?CategoryServiceTranslation
    {
        foreach ($this->categoryServiceTranslations as $translation) {
            if ($translation->getLanguage()->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, CategoryServiceTranslation>
     */
    public function getCategoryServiceTranslations(): Collection
    {
        return $this->categoryServiceTranslations;
    }

    public function addCategoryServiceTranslation(CategoryServiceTranslation $categoryServiceTranslation): static
    {
        if (!$this->categoryServiceTranslations->contains($categoryServiceTranslation)) {
            $this->categoryServiceTranslations->add($categoryServiceTranslation);
            $categoryServiceTranslation->setCategoryService($this);
        }

        return $this;
    }

    public function removeCategoryServiceTranslation(CategoryServiceTranslation $categoryServiceTranslation): static
    {
        if ($this->categoryServiceTranslations->removeElement($categoryServiceTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryServiceTranslation->getCategoryService() === $this) {
                $categoryServiceTranslation->setCategoryService(null);
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
            $company->addCategoryService($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        if ($this->companies->removeElement($company)) {
            $company->removeCategoryService($this);
        }

        return $this;
    }
}
