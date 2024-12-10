<?php

namespace App\Entity;

use App\Repository\PacientDischargeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientDischargeRepository::class)
 * @ORM\Table(name="pacients__externari")
 */
class PacientDischarge
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
     * @ORM\ManyToOne(targetEntity=Pacient::class, inversedBy="pacientDischarges")
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;
    
    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="pacientDischargesBy")
     * @ORM\JoinColumn(name="id_medic", nullable=false)
     */
    private $dischargedBy;

    /**
     * @ORM\Column(name="data_externare", type="datetime")
     */
    private $dischargeDate;

    /**
     * @ORM\Column(name="motiv_externare", type="text", nullable=true)
     */
    private $dischargeReason;

    /**
     * @ORM\Column(name="externare_diagnostic_clinic", type="text", nullable=true)
     */
    private $clinicalDiagnostic;

    /**
     * @ORM\Column(name="externare_diagnostic_paraclinic", type="text", nullable=true)
     */
    private $paraclinicalDiagnostic;

    /**
     * @ORM\Column(name="externare_epicriza", type="text", nullable=true)
     */
    private $epicrisis;

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

    public function getDischargeDate(): ?\DateTimeInterface
    {
        return $this->dischargeDate;
    }

    public function setDischargeDate(\DateTimeInterface $dischargeDate): self
    {
        $this->dischargeDate = $dischargeDate;

        return $this;
    }

    public function getDischargeReason(): ?string
    {
        return $this->dischargeReason;
    }

    public function setDischargeReason(string $dischargeReason): self
    {
        $this->dischargeReason = $dischargeReason;

        return $this;
    }

    public function getClinicalDiagnostic(): ?string
    {
        return $this->clinicalDiagnostic;
    }

    public function setClinicalDiagnostic(string $clinicalDiagnostic): self
    {
        $this->clinicalDiagnostic = $clinicalDiagnostic;

        return $this;
    }

    public function getParaclinicalDiagnostic(): ?string
    {
        return $this->paraclinicalDiagnostic;
    }

    public function setParaclinicalDiagnostic(?string $paraclinicalDiagnostic): self
    {
        $this->paraclinicalDiagnostic = $paraclinicalDiagnostic;

        return $this;
    }

    public function getEpicrisis(): ?string
    {
        return $this->epicrisis;
    }

    public function setEpicrisis(string $epicrisis): self
    {
        $this->epicrisis = $epicrisis;

        return $this;
    }

    public function getDischargedBy(): ?User
    {
        return $this->dischargedBy;
    }

    public function setDischargedBy(?User $dischargedBy): self
    {
        $this->dischargedBy = $dischargedBy;

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
