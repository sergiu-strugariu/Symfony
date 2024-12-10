<?php

namespace App\Entity;

use App\Repository\PacientVisitCalendarRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientVisitCalendarRepository::class)
 * @ORM\Table(name="pacients__visits_calendar")
 */
class PacientVisitCalendar
{

    const VISIT_TYPE_RELATION = 'relation';
    const VISIT_TYPE_VIEWING = 'viewing';
    const VISIT_TYPE_MEDICAL = 'medical';

    const VISIT_STATUS_PENDING = 'in asteptare';
    const VISIT_STATUS_CANCELED = 'anulata';

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
     * @ORM\ManyToOne(targetEntity=NursingHome::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $nursingHome;

    /**
     * @ORM\ManyToOne(targetEntity=Pacient::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $pacient;

    /**
     * @ORM\ManyToOne(targetEntity=Prospect::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $prospect;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observations;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $status;

    /**
     * @ORM\Column(name="visit_type", type="string", length=30, nullable=true, options={"default": self::VISIT_TYPE_RELATION})
     */
    private $type;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

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

    public function getNursingHome(): ?NursingHome
    {
        return $this->nursingHome;
    }

    public function setNursingHome(?NursingHome $nursingHome): self
    {
        $this->nursingHome = $nursingHome;

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

    public function getProspect(): ?Prospect
    {
        return $this->prospect;
    }

    public function setProspect(?Prospect $prospect): self
    {
        $this->prospect = $prospect;

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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

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

    public function getFormattedStartDate($format): ?string
    {
        return $this->startDate->format($format);
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string[]
     */
    public static function getVisitTypes(): array
    {
        return [
            self::VISIT_TYPE_MEDICAL => 'Programare medicală',
            self::VISIT_TYPE_RELATION => 'Vizită',
            self::VISIT_TYPE_VIEWING => 'Vizionare'
        ];
    }
}
