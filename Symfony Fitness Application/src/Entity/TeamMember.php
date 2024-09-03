<?php

namespace App\Entity;

use App\Repository\TeamMemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamMemberRepository::class)]
class TeamMember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $imageName = null;
    
    private $defaultLocale = 'ro';

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, TeamMemberTranslation>
     */
    #[ORM\OneToMany(targetEntity: TeamMemberTranslation::class, mappedBy: 'teamMember', orphanRemoval: true)]
    private Collection $teamMemberTranslation;

    /**
     * @var Collection<int, Education>
     */
    #[ORM\ManyToMany(targetEntity: Education::class, mappedBy: 'teamMembers')]
    private Collection $educations;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->teamMemberTranslation = new ArrayCollection();
        $this->educations = new ArrayCollection();
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

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

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

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

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
     * @return Collection<int, TeamMemberTranslation>
     */
    public function getTeamMemberTranslation(): Collection
    {
        return $this->teamMemberTranslation;
    }

    public function addTeamMemberTranslation(TeamMemberTranslation $teamMemberTranslation): static
    {
        if (!$this->teamMemberTranslation->contains($teamMemberTranslation)) {
            $this->teamMemberTranslation->add($teamMemberTranslation);
            $teamMemberTranslation->setTeamMember($this);
        }

        return $this;
    }

    public function removeTeamMemberTranslation(TeamMemberTranslation $teamMemberTranslation): static
    {
        if ($this->teamMemberTranslation->removeElement($teamMemberTranslation)) {
            if ($teamMemberTranslation->getTeamMember() === $this) {
                $teamMemberTranslation->setTeamMember(null);
            }
        }

        return $this;
    }

    public function getEducationsFilteredCount($startDate, $endDate): int
    {
        return $this->educations->filter(function (Education $education) use ($startDate, $endDate) {
            if ($startDate !== null && $endDate !== null) {
                $educationDate = $education->getStartDate();
                return $educationDate >= $startDate && $educationDate <= $endDate;
            }

            return true;
        })->count();
    }



    public function getEducations(): Collection
    {
        return $this->educations;
    }

    public function getTranslation($locale)
    {
        $translations = $this->getTeamMemberTranslation()->filter(function ($teamMemberTranslation) use ($locale) {
            return $teamMemberTranslation->getLanguage()->getLocale() === $locale;
        });
        
        if ($translations->isEmpty()) {
            return $this->getTeamMemberTranslation()->filter(function ($teamMemberTranslation) {
                return $teamMemberTranslation->getLanguage()->getLocale() === $this->defaultLocale;
            })->first();
        }
        
        return $translations->first();
    }
}
