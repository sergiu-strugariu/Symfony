<?php

namespace App\Entity;

use App\Repository\PacientGeneralDataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientGeneralDataRepository::class)
 * @ORM\Table(name="pacients__date_generale")
 */
class PacientGeneralData
{
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
     * @ORM\ManyToOne(targetEntity=Pacient::class, inversedBy="pacientGeneralData")
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $pacientType;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $pacientMobility;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $diet;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $requiresDiapers;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $requiresMedicalBed;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $admissionDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $nosocomialInfection;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $addedBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPacientType(): ?string
    {
        return $this->pacientType;
    }

    public function setPacientType(?string $pacientType): self
    {
        $this->pacientType = $pacientType;

        return $this;
    }

    public function getPacientMobility(): ?string
    {
        return $this->pacientMobility;
    }

    public function setPacientMobility(?string $pacientMobility): self
    {
        $this->pacientMobility = $pacientMobility;

        return $this;
    }

    public function getDiet(): ?string
    {
        return $this->diet;
    }

    public function setDiet(?string $diet): self
    {
        $this->diet = $diet;

        return $this;
    }

    public function isRequiresDiapers(): ?bool
    {
        return $this->requiresDiapers;
    }

    public function setRequiresDiapers(bool $requiresDiapers): self
    {
        $this->requiresDiapers = $requiresDiapers;

        return $this;
    }

    public function isRequiresMedicalBed(): ?bool
    {
        return $this->requiresMedicalBed;
    }

    public function setRequiresMedicalBed(bool $requiresMedicalBed): self
    {
        $this->requiresMedicalBed = $requiresMedicalBed;

        return $this;
    }

    public function getAdmissionDate(): ?\DateTimeInterface
    {
        return $this->admissionDate;
    }

    public function setAdmissionDate(?\DateTimeInterface $admissionDate): self
    {
        $this->admissionDate = $admissionDate;

        return $this;
    }

    public function getNosocomialInfection(): ?string
    {
        return $this->nosocomialInfection;
    }

    public function setNosocomialInfection(?string $nosocomialInfection): self
    {
        $this->nosocomialInfection = $nosocomialInfection;

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

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

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
