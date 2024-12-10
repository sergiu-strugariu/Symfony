<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\SummaryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SummaryRepository::class)
 * @ORM\Table(name="borderouri__lists")
 */
class Summary
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\OneToMany(targetEntity=SummaryPacient::class, mappedBy="summary")
     */
    private $summaryPacients;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $uid;

    /**
     * @ORM\Column(name="data", type="date", nullable=true)
     */
    private $summaryDate;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="id_user")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=SummaryType::class)
     * @ORM\JoinColumn(name="borderouri_type_id")
     */
    private $type;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $extraData = [];
    
    public function __construct()
    {
        $this->summaryPacients = new ArrayCollection();
    }

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

    public function getSummaryDate(): ?\DateTimeInterface
    {
        return $this->summaryDate;
    }

    public function setSummaryDate(?\DateTimeInterface $summaryDate): self
    {
        $this->summaryDate = $summaryDate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getType(): ?SummaryType
    {
        return $this->type;
    }

    public function setType(?SummaryType $type): self
    {
        $this->type = $type;

        return $this;
    }
    
    /**
     * @return Collection<int, SummaryPacient>
     */
    public function getSummaryPacients(): Collection
    {
        return $this->summaryPacients;
    }
    
    public function getSummaryPacientsCount()
    {
        return $this->summaryPacients->count();
    }
    
    public function getProgress()
    {
        $summaryPacientsCount = $this->getSummaryPacientsCount();
        $completedSummaryPacientsCount = $this->getSummaryPacients()->filter(function(SummaryPacient $summaryPacient) {
            return $summaryPacient->getStatus() == SummaryPacient::STATUS_COMPLETED;
        })->count();
        
        
        return $summaryPacientsCount > 0 ? ceil(floatval(($completedSummaryPacientsCount * 100) / $summaryPacientsCount)) : 0;
    }

    public function getExtraData(): ?array
    {
        return $this->extraData;
    }

    public function setExtraData(?array $extraData): self
    {
        $this->extraData = $extraData;

        return $this;
    }
    
    public function getExtraDataQuery(): ?string
    {
        if (null === $this->extraData) {
            return false;
        }
        
        return http_build_query($this->extraData);
    }
    
}
