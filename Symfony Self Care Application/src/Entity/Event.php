<?php

namespace App\Entity;

use App\Repository\EventRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'A event with this name already exists.', errorPath: 'title')]
class Event
{
    const STATUS_ENDED = 'ended';
    const STATUS_FUTURE = 'future';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?County $county = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?City $city = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $programFileName = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(length: 40)]
    private ?string $eventStatus = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $videoPlaceholder = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $videoUrl = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, EventTranslation>
     */
    #[ORM\OneToMany(targetEntity: EventTranslation::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $eventTranslations;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->createdAt = new DateTime();
        $this->eventTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getEventStatus(): ?string
    {
        return $this->eventStatus;
    }

    public function setEventStatus(string $eventStatus): static
    {
        $this->eventStatus = $eventStatus;

        return $this;
    }

    public function getVideoPlaceholder(): ?string
    {
        return $this->videoPlaceholder;
    }

    public function setVideoPlaceholder(?string $videoPlaceholder): static
    {
        $this->videoPlaceholder = $videoPlaceholder;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getProgramFileName(): ?string
    {
        return $this->programFileName;
    }

    public function setProgramFileName(?string $programFileName): static
    {
        $this->programFileName = $programFileName;

        return $this;
    }

    public function getCounty(): ?County
    {
        return $this->county;
    }

    public function setCounty(?County $county): static
    {
        $this->county = $county;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Collection<int, EventTranslation>
     */
    public function getEventTranslations(): Collection
    {
        return $this->eventTranslations;
    }

    public function addEventTranslation(EventTranslation $eventTranslation): static
    {
        if (!$this->eventTranslations->contains($eventTranslation)) {
            $this->eventTranslations->add($eventTranslation);
            $eventTranslation->setEvent($this);
        }

        return $this;
    }

    public function removeEventTranslation(EventTranslation $eventTranslation): static
    {
        if ($this->eventTranslations->removeElement($eventTranslation)) {
            // set the owning side to null (unless already changed)
            if ($eventTranslation->getEvent() === $this) {
                $eventTranslation->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public static function getOptionStatus(): array
    {
        return [
            self::STATUS_FUTURE => self::STATUS_FUTURE,
            self::STATUS_ENDED => self::STATUS_ENDED
        ];
    }
}
