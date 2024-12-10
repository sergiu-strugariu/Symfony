<?php

namespace App\Entity;

use App\Repository\PacientOrderlyDataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientOrderlyDataRepository::class)
 * @ORM\Table(name="pacients__date_infirmier")
 */
class PacientOrderlyData
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Pacient::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $pacient;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $addedBy;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     */
    private $uid;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hydrationFood;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $diuresis;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $stool;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $bathing;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $diapers;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observations;

    /**
     * @ORM\Column(name="administration_date", type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    public function getId(): ?int
    {
        return $this->id;
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

    public function isHydrationFood(): ?bool
    {
        return $this->hydrationFood;
    }

    public function setHydrationFood(?bool $hydrationFood): self
    {
        $this->hydrationFood = $hydrationFood;

        return $this;
    }

    public function isDiuresis(): ?bool
    {
        return $this->diuresis;
    }

    public function setDiuresis(?bool $diuresis): self
    {
        $this->diuresis = $diuresis;

        return $this;
    }

    public function isStool(): ?bool
    {
        return $this->stool;
    }

    public function setStool(?bool $stool): self
    {
        $this->stool = $stool;

        return $this;
    }

    public function isBathing(): ?bool
    {
        return $this->bathing;
    }

    public function setBathing(?bool $bathing): self
    {
        $this->bathing = $bathing;

        return $this;
    }

    public function isDiapers(): ?bool
    {
        return $this->diapers;
    }

    public function setDiapers(?bool $diapers): self
    {
        $this->diapers = $diapers;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
