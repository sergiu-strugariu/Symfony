<?php

namespace App\Entity;

use App\Repository\PacientAdmissionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientAdmissionRepository::class)
 * @ORM\Table(name="internari")
 */
class PacientAdmission
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
     * @ORM\ManyToOne(targetEntity=Pacient::class, inversedBy="pacientAdmissions")
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;
    
    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="admissions")
     * @ORM\JoinColumn(name="id_medic", nullable=false)
     */
    private $admittedBy;

    /**
     * @ORM\Column(name="data_internare", type="datetime")
     */
    private $admissionDate;

    /**
     * @ORM\Column(name="tip_pacient", type="string", length=100)
     */
    private $pacientType;

    /**
     * @ORM\Column(name="mobilitate", type="string", length=100)
     */
    private $pacientMobility;

    /**
     * @ORM\Column(name="scutec", type="boolean")
     */
    private $requiresDiapers;

    /**
     * @ORM\Column(name="regim_alimentar", type="string", length=100)
     */
    private $diet;

    /**
     * @ORM\Column(name="pat_medical", type="boolean")
     */
    private $requiresMedicalBed;

    /**
     * @ORM\Column(name="infectie_nosocomiala", type="text", nullable=true)
     */
    private $nosocomialInfection;

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

    public function getAdmissionDate(): ?\DateTimeInterface
    {
        return $this->admissionDate;
    }

    public function setAdmissionDate(\DateTimeInterface $admissionDate): self
    {
        $this->admissionDate = $admissionDate;

        return $this;
    }

    public function getPacientType(): ?string
    {
        return $this->pacientType;
    }

    public function setPacientType(string $pacientType): self
    {
        $this->pacientType = $pacientType;

        return $this;
    }

    public function getPacientMobility(): ?string
    {
        return $this->pacientMobility;
    }

    public function setPacientMobility(string $pacientMobility): self
    {
        $this->pacientMobility = $pacientMobility;

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

    public function getDiet(): ?string
    {
        return $this->diet;
    }

    public function setDiet(string $diet): self
    {
        $this->diet = $diet;

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

    public function getNosocomialInfection(): ?string
    {
        return $this->nosocomialInfection;
    }

    public function setNosocomialInfection(?string $nosocomialInfection): self
    {
        $this->nosocomialInfection = $nosocomialInfection;

        return $this;
    }

    public function getAdmittedBy(): ?User
    {
        return $this->admittedBy;
    }

    public function setAdmittedBy(?User $admittedBy): self
    {
        $this->admittedBy = $admittedBy;

        return $this;
    }
}
