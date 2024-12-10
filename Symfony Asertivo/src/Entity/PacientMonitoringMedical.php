<?php

namespace App\Entity;

use App\Repository\PacientMonitoringMedicalRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientMonitoringMedicalRepository::class)
 * @ORM\Table(name="pacients__monitorizare_functii_vitale")
 */
class PacientMonitoringMedical
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Pacient::class, inversedBy="pacientMonitoringMedicals")
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;
    
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="id_asistent", nullable=false)
     */
    private $addedBy;

    /**
     * @ORM\Column(name="temperatura", type="string", length=50, nullable=true)
     */
    private $temperature;

    /**
     * @ORM\Column(name="saturatie", type="string", length=50, nullable=true)
     */
    private $saturation;

    /**
     * @ORM\Column(name="tensiune_arteriala", type="string", length=50, nullable=true)
     */
    private $bloodPressure;

    /**
     * @ORM\Column(name="glicemie", type="string", length=50, nullable=true)
     */
    private $glucose;

    /**
     * @ORM\Column(name="perfuzabile", type="string", length=50, nullable=true)
     */
    private $infusion;

    /**
     * @ORM\Column(name="data_administrare", type="datetime")
     */
    private $date;
    
    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $heartRate;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observations;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;
    
    /**
     * @ORM\Column(name="tensiune_sistolica", type="string", length=10, nullable=true)
     */
    private $systolicBloodPressure;

    /**
     * @ORM\Column(name="tensiune_diastolica", type="string", length=10, nullable=true)
     */
    private $diastolicBloodPressure;

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
    
    public function getAddedBy(): ?User
    {
        return $this->addedBy;
    }

    public function setAddedBy(?User $addedBy): self
    {
        $this->addedBy = $addedBy;

        return $this;
    }

    public function getTemperature(): ?string
    {
        return $this->temperature;
    }

    public function setTemperature(?string $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getSaturation(): ?string
    {
        return $this->saturation;
    }

    public function setSaturation(?string $saturation): self
    {
        $this->saturation = $saturation;

        return $this;
    }

    public function getBloodPressure(): ?string
    {
        return $this->bloodPressure;
    }

    public function setBloodPressure(?string $bloodPressure): self
    {
        $this->bloodPressure = $bloodPressure;

        return $this;
    }

    public function getGlucose(): ?string
    {
        return $this->glucose;
    }

    public function setGlucose(?string $glucose): self
    {
        $this->glucose = $glucose;

        return $this;
    }

    public function getInfusion(): ?string
    {
        return $this->infusion;
    }

    public function setInfusion(?string $infusion): self
    {
        $this->infusion = $infusion;

        return $this;
    }
    
    public function getHeartRate(): ?string
    {
        return $this->heartRate;
    }

    public function setHeartRate(?string $heartRate): self
    {
        $this->heartRate = $heartRate;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

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
    
    public function getSystolicBloodPressure(): ?string
    {
        return $this->systolicBloodPressure;
    }

    public function setSystolicBloodPressure(?string $systolicBloodPressure): self
    {
        $this->systolicBloodPressure = $systolicBloodPressure;

        return $this;
    }

    public function getDiastolicBloodPressure(): ?string
    {
        return $this->diastolicBloodPressure;
    }

    public function setDiastolicBloodPressure(?string $diastolicBloodPressure): self
    {
        $this->diastolicBloodPressure = $diastolicBloodPressure;

        return $this;
    }
}