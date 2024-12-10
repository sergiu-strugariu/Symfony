<?php

namespace App\Entity;

use App\Repository\PacientMedicalDataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientMedicalDataRepository::class)
 * @ORM\Table(name="pacients__date_medicale")
 */
class PacientMedicalData
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
     * @ORM\ManyToOne(targetEntity=Pacient::class, inversedBy="pacientMedicalData")
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;
    
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $addedBy;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $bloodType;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $rh;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $vaccinatedAgainstCovid;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $hadCovid;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $alergies;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $weight;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $admissionDiagnostic;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $admissionReason;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $heterolateralCollateralAntecedents;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $personalPhysiologicalPathologicalAntecedents;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $livingAndWorkingConditions;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $behaviors;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $previousMedication;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $treatment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $epicrisis;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $surgeryConsultation;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $radiologicalExaminations;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $labExaminations;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $ultrasoundExaminations;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $otherSpecializedExams;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $otherTherapeuticProcedures;

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

    public function getBloodType(): ?string
    {
        return $this->bloodType;
    }

    public function setBloodType(?string $bloodType): self
    {
        $this->bloodType = $bloodType;

        return $this;
    }

    public function getRh(): ?string
    {
        return $this->rh;
    }

    public function setRh(?string $rh): self
    {
        $this->rh = $rh;

        return $this;
    }

    public function isVaccinatedAgainstCovid(): ?bool
    {
        return $this->vaccinatedAgainstCovid;
    }

    public function setVaccinatedAgainstCovid(bool $vaccinatedAgainstCovid): self
    {
        $this->vaccinatedAgainstCovid = $vaccinatedAgainstCovid;

        return $this;
    }

    public function isHadCovid(): ?bool
    {
        return $this->hadCovid;
    }

    public function setHadCovid(bool $hadCovid): self
    {
        $this->hadCovid = $hadCovid;

        return $this;
    }

    public function getAlergies(): ?string
    {
        return $this->alergies;
    }

    public function setAlergies(?string $alergies): self
    {
        $this->alergies = $alergies;

        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(?string $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(?string $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getAdmissionDiagnostic(): ?string
    {
        return $this->admissionDiagnostic;
    }

    public function setAdmissionDiagnostic(?string $admissionDiagnostic): self
    {
        $this->admissionDiagnostic = $admissionDiagnostic;

        return $this;
    }

    public function getAdmissionReason(): ?string
    {
        return $this->admissionReason;
    }

    public function setAdmissionReason(?string $admissionReason): self
    {
        $this->admissionReason = $admissionReason;

        return $this;
    }

    public function getHeterolateralCollateralAntecedents(): ?string
    {
        return $this->heterolateralCollateralAntecedents;
    }

    public function setHeterolateralCollateralAntecedents(?string $heterolateralCollateralAntecedents): self
    {
        $this->heterolateralCollateralAntecedents = $heterolateralCollateralAntecedents;

        return $this;
    }

    public function getPersonalPhysiologicalPathologicalAntecedents(): ?string
    {
        return $this->personalPhysiologicalPathologicalAntecedents;
    }

    public function setPersonalPhysiologicalPathologicalAntecedents(?string $personalPhysiologicalPathologicalAntecedents): self
    {
        $this->personalPhysiologicalPathologicalAntecedents = $personalPhysiologicalPathologicalAntecedents;

        return $this;
    }

    public function getLivingAndWorkingConditions(): ?string
    {
        return $this->livingAndWorkingConditions;
    }

    public function setLivingAndWorkingConditions(?string $livingAndWorkingConditions): self
    {
        $this->livingAndWorkingConditions = $livingAndWorkingConditions;

        return $this;
    }

    public function getBehaviors(): ?string
    {
        return $this->behaviors;
    }

    public function setBehaviors(?string $behaviors): self
    {
        $this->behaviors = $behaviors;

        return $this;
    }

    public function getPreviousMedication(): ?string
    {
        return $this->previousMedication;
    }

    public function setPreviousMedication(?string $previousMedication): self
    {
        $this->previousMedication = $previousMedication;

        return $this;
    }

    public function getTreatment(): ?string
    {
        return $this->treatment;
    }

    public function setTreatment(string $treatment): self
    {
        $this->treatment = $treatment;

        return $this;
    }

    public function getEpicrisis(): ?string
    {
        return $this->epicrisis;
    }

    public function setEpicrisis(?string $epicrisis): self
    {
        $this->epicrisis = $epicrisis;

        return $this;
    }

    public function getSurgeryConsultation(): ?string
    {
        return $this->surgeryConsultation;
    }

    public function setSurgeryConsultation(?string $surgeryConsultation): self
    {
        $this->surgeryConsultation = $surgeryConsultation;

        return $this;
    }

    public function getRadiologicalExaminations(): ?string
    {
        return $this->radiologicalExaminations;
    }

    public function setRadiologicalExaminations(?string $radiologicalExaminations): self
    {
        $this->radiologicalExaminations = $radiologicalExaminations;

        return $this;
    }

    public function getLabExaminations(): ?string
    {
        return $this->labExaminations;
    }

    public function setLabExaminations(?string $labExaminations): self
    {
        $this->labExaminations = $labExaminations;

        return $this;
    }

    public function getUltrasoundExaminations(): ?string
    {
        return $this->ultrasoundExaminations;
    }

    public function setUltrasoundExaminations(?string $ultrasoundExaminations): self
    {
        $this->ultrasoundExaminations = $ultrasoundExaminations;

        return $this;
    }

    public function getOtherSpecializedExams(): ?string
    {
        return $this->otherSpecializedExams;
    }

    public function setOtherSpecializedExams(?string $otherSpecializedExams): self
    {
        $this->otherSpecializedExams = $otherSpecializedExams;

        return $this;
    }

    public function getOtherTherapeuticProcedures(): ?string
    {
        return $this->otherTherapeuticProcedures;
    }

    public function setOtherTherapeuticProcedures(?string $otherTherapeuticProcedures): self
    {
        $this->otherTherapeuticProcedures = $otherTherapeuticProcedures;

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
