<?php

namespace App\Entity;

use App\Repository\CategoryCourseTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CategoryCourseTranslationRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_category_course_language', columns: ['category_course_id', 'language_id'])]
class CategoryCourseTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'categoryCourseTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategoryCourse $categoryCourse = null;

    #[ORM\ManyToOne(inversedBy: 'categoryCourseTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryCourse(): ?CategoryCourse
    {
        return $this->categoryCourse;
    }

    public function setCategoryCourse(?CategoryCourse $categoryCourse): static
    {
        $this->categoryCourse = $categoryCourse;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageLocale(): ?string
    {
        return $this->language->getLocale();
    }
}
