<?php

namespace App\Entity;

use App\Repository\GalleryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GalleryRepository::class)]
class Gallery
{

    const TYPE_COURSE = 'course';
    const TYPE_WORKSHOP = 'workshop';
    const TYPE_CONVENTION = 'convention';
    
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;
    
    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(length: 100)]
    private ?string $featuredImageName = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $galleryLink = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'educations')]
    private ?County $county = null;

    #[ORM\ManyToOne(inversedBy: 'educations')]
    private ?City $city = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $eventDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getEventDate(): ?\DateTimeInterface
    {
        return $this->eventDate;
    }

    public function setEventDate(\DateTimeInterface $eventDate): static
    {
        $this->eventDate = $eventDate;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getFeaturedImageName(): ?string
    {
        return $this->featuredImageName;
    }

    public function setFeaturedImageName(string $featuredImageName): static
    {
        $this->featuredImageName = $featuredImageName;

        return $this;
    }

    public function getGalleryLink(): ?string
    {
        return $this->galleryLink;
    }

    public function setGalleryLink(?string $galleryLink): static
    {
        $this->galleryLink = $galleryLink;

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

}
