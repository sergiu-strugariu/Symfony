<?php

namespace App\Entity;

use App\Repository\LanguageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: LanguageRepository::class)]
#[UniqueEntity(fields: ['locale'], message: 'A language with this locale already exists.', errorPath: 'locale')]
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
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, JobTranslation>
     */
    #[ORM\OneToMany(targetEntity: JobTranslation::class, mappedBy: 'language', orphanRemoval: true)]
    private Collection $jobTranslations;

    /**
     * @var Collection<int, TrainingCourseTranslation>
     */
    #[ORM\OneToMany(targetEntity: TrainingCourseTranslation::class, mappedBy: 'language', orphanRemoval: true)]
    private Collection $trainingCourseTranslations;

    /**
     * @var Collection<int, CategoryCourseTranslation>
     */
    #[ORM\OneToMany(targetEntity: CategoryCourseTranslation::class, mappedBy: 'language', orphanRemoval: true)]
    private Collection $categoryCourseTranslations;

    /**
     * @var Collection<int, CategoryJobTranslation>
     */
    #[ORM\OneToMany(targetEntity: CategoryJobTranslation::class, mappedBy: 'language', orphanRemoval: true)]
    private Collection $categoryJobTranslations;

    /**
     * @var Collection<int, CategoryArticleTranslation>
     */
    #[ORM\OneToMany(targetEntity: CategoryArticleTranslation::class, mappedBy: 'language', orphanRemoval: true)]
    private Collection $categoryArticleTranslations;

    /**
     * @var Collection<int, CategoryCareTranslation>
     */
    #[ORM\OneToMany(targetEntity: CategoryCareTranslation::class, mappedBy: 'language', orphanRemoval: true)]
    private Collection $categoryCareTranslations;

    /**
     * @var Collection<int, CategoryServiceTranslation>
     */
    #[ORM\OneToMany(targetEntity: CategoryServiceTranslation::class, mappedBy: 'language', orphanRemoval: true)]
    private Collection $categoryServiceTranslations;

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
     * @var Collection<int, EventTranslation>
     */
    #[ORM\OneToMany(targetEntity: EventTranslation::class, mappedBy: 'language', orphanRemoval: true)]
    private Collection $eventTranslations;

    public function __construct()
    {
        $this->jobTranslations = new ArrayCollection();
        $this->trainingCourseTranslations = new ArrayCollection();
        $this->categoryCourseTranslations = new ArrayCollection();
        $this->categoryJobTranslations = new ArrayCollection();
        $this->categoryArticleTranslations = new ArrayCollection();
        $this->categoryCareTranslations = new ArrayCollection();
        $this->categoryServiceTranslations = new ArrayCollection();
        $this->menuItemTranslations = new ArrayCollection();
        $this->pageWidgetTranslations = new ArrayCollection();
        $this->eventTranslations = new ArrayCollection();
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

    /**
     * @return Collection<int, JobTranslation>
     */
    public function getJobTranslations(): Collection
    {
        return $this->jobTranslations;
    }

    public function addJobTranslation(JobTranslation $jobTranslation): static
    {
        if (!$this->jobTranslations->contains($jobTranslation)) {
            $this->jobTranslations->add($jobTranslation);
            $jobTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeJobTranslation(JobTranslation $jobTranslation): static
    {
        if ($this->jobTranslations->removeElement($jobTranslation)) {
            // set the owning side to null (unless already changed)
            if ($jobTranslation->getLanguage() === $this) {
                $jobTranslation->setLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TrainingCourseTranslation>
     */
    public function getTrainingCourseTranslations(): Collection
    {
        return $this->trainingCourseTranslations;
    }

    public function addTrainingCourseTranslation(TrainingCourseTranslation $trainingCourseTranslation): static
    {
        if (!$this->trainingCourseTranslations->contains($trainingCourseTranslation)) {
            $this->trainingCourseTranslations->add($trainingCourseTranslation);
            $trainingCourseTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeTrainingCourseTranslation(TrainingCourseTranslation $trainingCourseTranslation): static
    {
        if ($this->trainingCourseTranslations->removeElement($trainingCourseTranslation)) {
            // set the owning side to null (unless already changed)
            if ($trainingCourseTranslation->getLanguage() === $this) {
                $trainingCourseTranslation->setLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CategoryCourseTranslation>
     */
    public function getCategoryCourseTranslations(): Collection
    {
        return $this->categoryCourseTranslations;
    }

    public function addCategoryCourseTranslation(CategoryCourseTranslation $categoryCourseTranslation): static
    {
        if (!$this->categoryCourseTranslations->contains($categoryCourseTranslation)) {
            $this->categoryCourseTranslations->add($categoryCourseTranslation);
            $categoryCourseTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeCategoryCourseTranslation(CategoryCourseTranslation $categoryCourseTranslation): static
    {
        if ($this->categoryCourseTranslations->removeElement($categoryCourseTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryCourseTranslation->getLanguage() === $this) {
                $categoryCourseTranslation->setLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CategoryJobTranslation>
     */
    public function getCategoryJobTranslations(): Collection
    {
        return $this->categoryJobTranslations;
    }

    public function addCategoryJobTranslation(CategoryJobTranslation $categoryJobTranslation): static
    {
        if (!$this->categoryJobTranslations->contains($categoryJobTranslation)) {
            $this->categoryJobTranslations->add($categoryJobTranslation);
            $categoryJobTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeCategoryJobTranslation(CategoryJobTranslation $categoryJobTranslation): static
    {
        if ($this->categoryJobTranslations->removeElement($categoryJobTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryJobTranslation->getLanguage() === $this) {
                $categoryJobTranslation->setLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CategoryArticleTranslation>
     */
    public function getCategoryArticleTranslations(): Collection
    {
        return $this->categoryArticleTranslations;
    }

    public function addCategoryArticleTranslation(CategoryArticleTranslation $categoryArticleTranslation): static
    {
        if (!$this->categoryArticleTranslations->contains($categoryArticleTranslation)) {
            $this->categoryArticleTranslations->add($categoryArticleTranslation);
            $categoryArticleTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeCategoryArticleTranslation(CategoryArticleTranslation $categoryArticleTranslation): static
    {
        if ($this->categoryArticleTranslations->removeElement($categoryArticleTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryArticleTranslation->getLanguage() === $this) {
                $categoryArticleTranslation->setLanguage(null);
            }
        }

        return $this;
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
            $categoryCareTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeCategoryCareTranslation(CategoryCareTranslation $categoryCareTranslation): static
    {
        if ($this->categoryCareTranslations->removeElement($categoryCareTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryCareTranslation->getLanguage() === $this) {
                $categoryCareTranslation->setLanguage(null);
            }
        }

        return $this;
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
            $categoryServiceTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeCategoryServiceTranslation(CategoryServiceTranslation $categoryServiceTranslation): static
    {
        if ($this->categoryServiceTranslations->removeElement($categoryServiceTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryServiceTranslation->getLanguage() === $this) {
                $categoryServiceTranslation->setLanguage(null);
            }
        }

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
     * @return Collection<int, MenuItemTranslation>
     */
    public function getMenuItemTranslations(): Collection
    {
        return $this->menuItemTranslations;
    }

    public function addMenuItemTranslation(MenuItemTranslation $menuItemTranslation): static
    {
        if (!$this->menuItemTranslations->contains($menuItemTranslation)) {
            $this->menuItemTranslations->add($menuItemTranslation);
            $menuItemTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeMenuItemTranslation(MenuItemTranslation $menuItemTranslation): static
    {
        if ($this->menuItemTranslations->removeElement($menuItemTranslation)) {
            // set the owning side to null (unless already changed)
            if ($menuItemTranslation->getLanguage() === $this) {
                $menuItemTranslation->setLanguage(null);
            }
        }

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

    /**
     * @return Collection<int, EventTranslation>
     */
    public function getEventTranslations(): Collection
    {
        return $this->eventTranslations;
    }

    public function addEventTranslation(EventTranslation $eventTranslation): static
    {
        if (!$this->eventTranslations->contains($eventTranslation)) {
            $this->eventTranslations->add($eventTranslation);
            $eventTranslation->setLanguage($this);
        }

        return $this;
    }

    public function removeEventTranslation(EventTranslation $eventTranslation): static
    {
        if ($this->eventTranslations->removeElement($eventTranslation)) {
            // set the owning side to null (unless already changed)
            if ($eventTranslation->getLanguage() === $this) {
                $eventTranslation->setLanguage(null);
            }
        }

        return $this;
    }
}
