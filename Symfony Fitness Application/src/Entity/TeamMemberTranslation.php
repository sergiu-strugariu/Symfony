<?php

namespace App\Entity;

use App\Repository\TeamMemberTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamMemberTranslationRepository::class)]
class TeamMemberTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $specialization = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'teamMemberTranslation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TeamMember $teamMember = null;

    #[ORM\ManyToOne(inversedBy: 'teamMemberTranslations')]
    private ?Language $language = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpecialization(): ?string
    {
        return $this->specialization;
    }

    public function setSpecialization(string $specialization): static
    {
        $this->specialization = $specialization;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTeamMember(): ?TeamMember
    {
        return $this->teamMember;
    }

    public function setTeamMember(?TeamMember $teamMember): static
    {
        $this->teamMember = $teamMember;

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
}
