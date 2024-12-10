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
#[UniqueEntity(fields: ['slug'], message: 'A package with this name already exists.', errorPath: 'name')]
class MembershipPackage
{
    const MONTHLY = 'monthly';
    const YEARLY = 'yearly';

    const PACKAGE_FREE = 'gratuit';
    const PACKAGE_SILVER = 'silver-help';
    const PACKAGE_GOLD = 'gold-help';
    const PACKAGE_DIAMOND = 'diamond-help';

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

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $maxJobPerMonth = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $maxCoursePerMonth = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $maxArticlePerMonth = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $maxGenerateArticlePerMonth = null;

    /**
     * @var Collection<int, MembershipPackageTranslation>
     */
    #[ORM\OneToMany(targetEntity: MembershipPackageTranslation::class, mappedBy: 'membershipPackage', orphanRemoval: true)]
    private Collection $membershipPackageTranslations;

    /**
     * @var Collection<int, Payment>
     */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'membershipPackage', orphanRemoval: true)]
    private Collection $payments;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'membershipPackage')]
    private Collection $users;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->maxJobPerMonth = 0;
        $this->maxCoursePerMonth = 0;
        $this->maxArticlePerMonth = 0;
        $this->maxGenerateArticlePerMonth = 0;
        $this->createdAt = new \DateTime();
        $this->membershipPackageTranslations = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->users = new ArrayCollection();
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
        return round($this->price, 2);
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
     * This function calculates the discounted price per month for the yearly plan.
     * It multiplies the monthly price by 12 to get the yearly price, then applies the discount.
     *
     * @return float|null
     */
    public function getYearlyPrice(): ?float
    {
        // Calculate the yearly price by multiplying the monthly price by 12
        $yearlyPrice = $this->price * 12;

        // Apply the discount if it's greater than 0
        if ($this->discount > 0) {
            $discountedYearlyPrice = $yearlyPrice - ($yearlyPrice * ($this->discount / 100));
            return round($discountedYearlyPrice, 2);
        }

        return round($yearlyPrice, 2);
    }

    /**
     * @param $price
     * @return float
     */
    public function getPriceByTva($price): float
    {
        $priceWithTva = $price * 1.19;
        return round($priceWithTva, 2);
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


    /**
     * @return string[]
     */
    public static function getPlans(): array
    {
        return [
            self::MONTHLY => self::MONTHLY,
            self::YEARLY => self::YEARLY
        ];
    }

    /**
     * @return string[]
     */
    public static function getPackages(): array
    {
        return [
            self::PACKAGE_DIAMOND => self::PACKAGE_DIAMOND,
            self::PACKAGE_GOLD => self::PACKAGE_GOLD,
            self::PACKAGE_SILVER => self::PACKAGE_SILVER,
            self::PACKAGE_FREE => self::PACKAGE_FREE
        ];
    }


    /**
     * Remove multiple packages from the array.
     *
     * @param $packages
     * @param array $packagesToExclude
     * @return string[]
     */
    public static function getPackagesExcluding($packages, array $packagesToExclude): array
    {
        // Loop through the packages to exclude and remove them
        foreach ($packagesToExclude as $package) {
            if (isset($packages[$package])) {
                unset($packages[$package]);
            }
        }

        return $packages;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setMembershipPackage($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getMembershipPackage() === $this) {
                $payment->setMembershipPackage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setMembershipPackage($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getMembershipPackage() === $this) {
                $user->setMembershipPackage(null);
            }
        }

        return $this;
    }

    public function getMaxJobPerMonth(): ?int
    {
        return $this->maxJobPerMonth;
    }

    public function setMaxJobPerMonth(int $maxJobPerMonth): static
    {
        $this->maxJobPerMonth = $maxJobPerMonth;

        return $this;
    }

    public function getMaxCoursePerMonth(): ?int
    {
        return $this->maxCoursePerMonth;
    }

    public function setMaxCoursePerMonth(int $maxCoursePerMonth): static
    {
        $this->maxCoursePerMonth = $maxCoursePerMonth;

        return $this;
    }

    public function getMaxArticlePerMonth(): ?int
    {
        return $this->maxArticlePerMonth;
    }

    public function setMaxArticlePerMonth(int $maxArticlePerMonth): static
    {
        $this->maxArticlePerMonth = $maxArticlePerMonth;

        return $this;
    }

    public function getMaxGenerateArticlePerMonth(): ?int
    {
        return $this->maxGenerateArticlePerMonth;
    }

    public function setMaxGenerateArticlePerMonth(int $maxGenerateArticlePerMonth): static
    {
        $this->maxGenerateArticlePerMonth = $maxGenerateArticlePerMonth;

        return $this;
    }
}
