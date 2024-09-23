<?php

namespace App\Entity;

use App\Repository\EducationScheduleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EducationScheduleRepository::class)]
class EducationSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Education::class, cascade: ["persist"], inversedBy: "schedules")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Education $education;

    #[ORM\OneToMany(targetEntity: EducationScheduleTranslation::class, mappedBy: 'educationSchedule', orphanRemoval: true)]
    private Collection $educationScheduleTranslations;

    private $defaultLocale = 'ro';
    
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->educationScheduleTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

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

    public function getEducationScheduleTranslations(): Collection
    {
        return $this->educationScheduleTranslations;
    }

    public function addArticleTranslation(EducationScheduleTranslation $educationScheduleTranslation): static
    {
        if (!$this->educationScheduleTranslations->contains($educationScheduleTranslation)) {
            $this->educationScheduleTranslations->add($educationScheduleTranslation);
            $educationScheduleTranslation->setEducationSchedule($this);
        }

        return $this;
    }

    public function removeEducationTranslation(EducationScheduleTranslation $educationScheduleTranslation): static
    {
        if ($this->educationScheduleTranslations->removeElement($educationScheduleTranslation)) {
            // set the owning side to null (unless already changed)
            if ($educationScheduleTranslation->getEducationSchedule() === $this) {
                $educationScheduleTranslation->setEducationSchedule(null);
            }
        }

        return $this;
    }

    public function getTranslation($locale)
    {
        return $translations = $this->getEducationScheduleTranslations()->filter(function ($educationScheduleTranslation) use ($locale) {
            return $educationScheduleTranslation->getLanguage()->getLocale() === $locale;
        })->first();
    }

    public function getEducation(): ?Education
    {
        return $this->education;
    }

    public function setEducation(?Education $education): self
    {
        $this->education = $education;

        return $this;
    }

    public function getFormattedDate($locale)
    {

        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        if (!$startDate instanceof \DateTime || !$endDate instanceof \DateTime) {
            throw new \InvalidArgumentException('startDate and endDate must be instances of DateTime');
        }

        $days = [
            'Monday'    => 'Luni',
            'Tuesday'   => 'Marți',
            'Wednesday' => 'Miercuri',
            'Thursday'  => 'Joi',
            'Friday'    => 'Vineri',
            'Saturday'  => 'Sâmbătă',
            'Sunday'    => 'Duminică',
        ];

        $startDay = $locale === 'ro' ? $days[$startDate->format('l')] : $startDate->format('l');
        return $startDay . ' <br/> ' . $startDate->format('H:i') . ' - ' . $endDate->format('H:i');
    }

}
