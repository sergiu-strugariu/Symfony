<?php

namespace App\Entity;

use App\Repository\PacientMedicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientMedicationRepository::class)
 * @ORM\Table(name="pacients__plan_medicamentatie")
 */
class PacientMedication
{
    
    const STATUS_ACTIVE = 'in curs';
    const STATUS_STOPPED = 'intrerupta';
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Pacient::class, inversedBy="pacientMedications")
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="id_medic", nullable=false)
     */
    private $medic;
    
    /**
     * @ORM\ManyToOne(targetEntity=PacientDiagnosis::class, inversedBy="pacientMedications")
     * @ORM\JoinColumn(name="id_diagnostic", nullable=false)
     */
    private $pacientDiagnosis;

    /**
     * @ORM\Column(name="detaliere_plan", type="text", nullable=true)
     */
    private $details;
    
    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=PacientMedicationDetails::class, mappedBy="pacientMedication")
     */
    private $pacientMedicationDetails;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default": 0})
     */
    private $asNecessary = false;

    public function __construct()
    {
        $this->pacientMedicationDetails = new ArrayCollection();
    }

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

    public function getMedic(): ?User
    {
        return $this->medic;
    }

    public function setMedic(?User $medic): self
    {
        $this->medic = $medic;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getPacientDiagnosis(): ?User
    {
        return $this->pacientDiagnosis;
    }

    public function setPacientDiagnosis(?PacientDiagnosis $pacientDiagnosis): self
    {
        $this->pacientDiagnosis = $pacientDiagnosis;

        return $this;
    }
    
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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

    /**
     * @return Collection<int, PacientMedicationDetails>
     */
    public function getPacientMedicationDetails(): Collection
    {
        return $this->pacientMedicationDetails;
    }

    public function addPacientMedicationDetail(PacientMedicationDetails $pacientMedicationDetail): self
    {
        if (!$this->pacientMedicationDetails->contains($pacientMedicationDetail)) {
            $this->pacientMedicationDetails[] = $pacientMedicationDetail;
            $pacientMedicationDetail->setPacientMedication($this);
        }

        return $this;
    }

    public function removePacientMedicationDetail(PacientMedicationDetails $pacientMedicationDetail): self
    {
        if ($this->pacientMedicationDetails->removeElement($pacientMedicationDetail)) {
            // set the owning side to null (unless already changed)
            if ($pacientMedicationDetail->getPacientMedication() === $this) {
                $pacientMedicationDetail->setPacientMedication(null);
            }
        }

        return $this;
    }
    
    public function getFilteredPacientMedicationDetails($status) 
    {
        return $this->pacientMedicationDetails->filter(function($element) use ($status) {
            return $element->getStatus() === $status;
        });
    }

    public function isAsNecessary(): ?bool
    {
        return $this->asNecessary;
    }

    public function setAsNecessary(?bool $asNecessary): self
    {
        $this->asNecessary = $asNecessary;

        return $this;
    }
}
