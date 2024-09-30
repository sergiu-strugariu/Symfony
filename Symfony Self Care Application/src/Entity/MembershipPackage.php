<?php

namespace App\Entity;

use App\Repository\MembershipPackageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MembershipPackageRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'A article with this name already exists.', errorPath: 'name')]
class MembershipPackage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $discount = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\Column]
    private ?bool $isPopular = false;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    /**
     * @var Collection<int, MembershipPackageTranslation>
     */
    #[ORM\OneToMany(targetEntity: MembershipPackageTranslation::class, mappedBy: 'membershipPackage', orphanRemoval: true)]
    private Collection $membershipPackageTranslations;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->createdAt = new \DateTime();
        $this->membershipPackageTranslations = new ArrayCollection();
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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

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

    public function getPopular(): ?bool
    {
        return $this->isPopular;
    }

    public function setPopular(bool $isPopular): static
    {
        $this->isPopular = $isPopular;

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

    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    public function setDiscount(int $discount): static
    {
        $this->discount = $discount;

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
     * @return Collection<int, MembershipPackageTranslation>
     */
    public function getMembershipPackageTranslations(): Collection
    {
        return $this->membershipPackageTranslations;
    }

    public function addMembershipPackageTranslation(MembershipPackageTranslation $membershipPackageTranslation): static
    {
        if (!$this->membershipPackageTranslations->contains($membershipPackageTranslation)) {
            $this->membershipPackageTranslations->add($membershipPackageTranslation);
            $membershipPackageTranslation->setMembershipPackage($this);
        }

        return $this;
    }

    public function removeMembershipPackageTranslation(MembershipPackageTranslation $membershipPackageTranslation): static
    {
        if ($this->membershipPackageTranslations->removeElement($membershipPackageTranslation)) {
            // set the owning side to null (unless already changed)
            if ($membershipPackageTranslation->getMembershipPackage() === $this) {
                $membershipPackageTranslation->setMembershipPackage(null);
            }
        }

        return $this;
    }

    /**
     * @return float|int|string|null
     */
    public function getYearlyPricePerMonth(): float|int|string|null
    {
        if ($this->discount > 0) {
            return round($this->price - ($this->price * ($this->discount / 100)), 2);
        }

        return round($this->price, 2);
    }

    /**
     * @param $locale
     * @return MembershipPackageTranslation|null
     */
    public function getTranslation($locale): ?MembershipPackageTranslation
    {
        foreach ($this->membershipPackageTranslations as $translation) {
            if ($translation->getLanguage()->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }
}
