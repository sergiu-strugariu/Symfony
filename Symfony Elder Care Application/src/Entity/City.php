<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CityRepository::class)
 * @ORM\Table(name="account_city")
 */
class City
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=County::class, inversedBy="cities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $county;

    /**
     * @ORM\Column(type="integer")
     */
    private $siruta;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=16)
     */
    private $longitude;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=16)
     */
    private $latitude;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $region;

    /**
     * @ORM\OneToMany(targetEntity=UserBillingData::class, mappedBy="city")
     */
    private $userBillingData;

    public function __construct()
    {
        $this->userBillingData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCounty(): ?County
    {
        return $this->county;
    }

    public function setCounty(?County $county): self
    {
        $this->county = $county;

        return $this;
    }

    public function getSiruta(): ?int
    {
        return $this->siruta;
    }

    public function setSiruta(int $siruta): self
    {
        $this->siruta = $siruta;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Collection<int, UserBillingData>
     */
    public function getUserBillingData(): Collection
    {
        return $this->userBillingData;
    }

    public function addUserBillingData(UserBillingData $userBillingData): self
    {
        if (!$this->userBillingData->contains($userBillingData)) {
            $this->userBillingData[] = $userBillingData;
            $userBillingData->setCity($this);
        }

        return $this;
    }

    public function removeUserBillingData(UserBillingData $userBillingData): self
    {
        if ($this->userBillingData->removeElement($userBillingData)) {
            // set the owning side to null (unless already changed)
            if ($userBillingData->getCity() === $this) {
                $userBillingData->setCity(null);
            }
        }

        return $this;
    }
}
