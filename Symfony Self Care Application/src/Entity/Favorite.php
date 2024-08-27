<?php

namespace App\Entity;

use App\Repository\FavoriteRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_favorite_user', columns: ['user_id', 'entity_id', 'type'])]
class Favorite
{
    const CARE_FAVORITE = Company::LOCATION_TYPE_CARE;
    const PROVIDER_FAVORITE = Company::LOCATION_TYPE_PROVIDER;
    const COURSE_FAVORITE = 'course';
    const JOB_FAVORITE = 'job';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'favorites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $entityId = null;

    #[ORM\Column(length: 40)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;


    public function __construct()
    {
        $this->createdAt = new DateTime();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): static
    {
        $this->entityId = $entityId;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string[]
     */
    public static function getFavoriteTypes(): array
    {
        return [
            self::CARE_FAVORITE => self::CARE_FAVORITE,
            self::PROVIDER_FAVORITE => self::PROVIDER_FAVORITE,
            self::JOB_FAVORITE => self::JOB_FAVORITE,
            self::COURSE_FAVORITE => self::COURSE_FAVORITE
        ];
    }
}
