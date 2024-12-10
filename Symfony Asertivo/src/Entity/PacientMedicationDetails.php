<?php

namespace App\Entity;

use App\Repository\PacientMedicationDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientMedicationDetailsRepository::class)
 * @ORM\Table(name="pacients__plan_medicamentatie_details")
 */
class PacientMedicationDetails
{
    
    const STATUS_PLANNED = 'planificat';
    const STATUS_ADMINISTERED = 'administrat';
    const STATUS_REFUSED = 'refuzat';
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PacientMedication::class, inversedBy="pacientMedicationDetails")
     * @ORM\JoinColumn(name="id_plan", nullable=false)
     */
    private $pacientMedication;

    /**
     * @ORM\ManyToOne(targetEntity=Pacient::class, inversedBy="pacientMedicationDetails")
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="id_asistent_administrare")
     */
    private $assistant;

    /**
     * @ORM\Column(name="medicament", type="string", length=500, nullable=true)
     */
    private $drug;

    /**
     * @ORM\Column(name="doza", type="string", length=500, nullable=true)
     */
    private $dose;

    /**
     * @ORM\Column(name="observatii", type="text", nullable=true)
     */
    private $observations;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $status;

    /**
     * @ORM\Column(name="date_time", type="datetime", nullable=true)
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPacientMedication(): ?PacientMedication
    {
        return $this->pacientMedication;
    }

    public function setPacientMedication(?PacientMedication $pacientMedication): self
    {
        $this->pacientMedication = $pacientMedication;

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

    public function getAssistant(): ?User
    {
        return $this->assistant;
    }

    public function setAssistant(?User $assistant): self
    {
        $this->assistant = $assistant;

        return $this;
    }

    public function getDrug(): ?string
    {
        return $this->drug;
    }

    public function setDrug(?string $drug): self
    {
        $this->drug = $drug;

        return $this;
    }

    public function getDose(): ?string
    {
        return $this->dose;
    }

    public function setDose(?string $dose): self
    {
        $this->dose = $dose;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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
    
    public function getStatuses() 
    {
        return [
            self::STATUS_PLANNED => self::STATUS_PLANNED,
            self::STATUS_ADMINISTERED => self::STATUS_ADMINISTERED,
            self::STATUS_REFUSED => self::STATUS_REFUSED
        ];
    }
}
