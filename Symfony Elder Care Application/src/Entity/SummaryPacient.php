<?php

namespace App\Entity;

use App\Repository\SummaryPacientRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SummaryPacientRepository::class)
 * @ORM\Table(name="borderouri__pacients_associated")
 */
class SummaryPacient
{
    
    const STATUS_IN_PROGRESS = 'In lucru';
    const STATUS_COMPLETED = 'Finalizat';
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $uid;

    /**
     * @ORM\ManyToOne(targetEntity=Summary::class, inversedBy="pacients")
     * @ORM\JoinColumn(name="id_borderou", nullable=false)
     */
    private $summary;

    /**
     * @ORM\ManyToOne(targetEntity=Pacient::class)
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="added_by_user_id")
     */
    private $addedBy;

    /**
     * @ORM\Column(name="is_urgent", type="boolean", options={"default": 0})
     */
    private $urgent;

    /**
     * @ORM\Column(name="observatii", type="text", nullable=true)
     */
    private $observations;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSummary(): ?Summary
    {
        return $this->summary;
    }

    public function setSummary(?Summary $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getPacient(): ?Pacient
    {
        return $this->pacient;
    }

    public function setPacient(?Pacient $pacient): self
    {
        $this->pacient = $pacient;

        return $this;
    }

    public function getAddedBy(): ?User
    {
        return $this->addedBy;
    }

    public function setAddedBy(?User $addedBy): self
    {
        $this->addedBy = $addedBy;

        return $this;
    }

    public function isUrgent(): ?bool
    {
        return $this->urgent;
    }

    public function setUrgent(bool $urgent): self
    {
        $this->urgent = $urgent;

        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): self
    {
        $this->observations = $observations;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }
    
    public function isCompleted(): bool {
        return $this->status === self::STATUS_COMPLETED;
    }
    
    public static function getStatuses() {
        return [
            self::STATUS_IN_PROGRESS => self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED => self::STATUS_COMPLETED
        ];
    }
}
