<?php

namespace App\Entity;

use App\Repository\EventPartnerRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EventPartnerRepository::class)]
class EventPartner
{
    const SPONSOR_TYPE = 'sponsor';
    const MEDIA_TYPE = 'media';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 99)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'eventPartnerSponsors')]
    private Collection $sponsorEvents;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'eventPartnerMedia')]
    private Collection $mediaEvents;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->uuid = Uuid::v4();
        $this->sponsorEvents = new ArrayCollection();
        $this->mediaEvents = new ArrayCollection();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

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
     * @return string[]
     */
    public static function getTypes(): array
    {
        return [
            self::MEDIA_TYPE => self::MEDIA_TYPE,
            self::SPONSOR_TYPE => self::SPONSOR_TYPE,
        ];
    }

    /**
     * @return Collection<int, Event>
     */
    public function getSponsorEvents(): Collection
    {
        return $this->sponsorEvents;
    }

    public function addSponsorEvent(Event $sponsorEvent): static
    {
        if (!$this->sponsorEvents->contains($sponsorEvent)) {
            $this->sponsorEvents->add($sponsorEvent);
            $sponsorEvent->addEventPartnerSponsor($this);
        }

        return $this;
    }

    public function removeSponsorEvent(Event $sponsorEvent): static
    {
        if ($this->sponsorEvents->removeElement($sponsorEvent)) {
            $sponsorEvent->removeEventPartnerSponsor($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getMediaEvents(): Collection
    {
        return $this->mediaEvents;
    }

    public function addMediaEvent(Event $mediaEvent): static
    {
        if (!$this->mediaEvents->contains($mediaEvent)) {
            $this->mediaEvents->add($mediaEvent);
            $mediaEvent->addEventPartnerMedium($this);
        }

        return $this;
    }

    public function removeMediaEvent(Event $mediaEvent): static
    {
        if ($this->mediaEvents->removeElement($mediaEvent)) {
            $mediaEvent->removeEventPartnerMedium($this);
        }

        return $this;
    }
}
