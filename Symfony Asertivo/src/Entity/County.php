<?php

namespace App\Entity;

use App\Repository\CountyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CountyRepository::class)
 * @ORM\Table(name="account_county")
 */
class County
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=City::class, mappedBy="county")
     */
    private $cities;

    /**
     * @ORM\OneToMany(targetEntity=UserBillingData::class, mappedBy="county")
     */
    private $userBillingData;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
        $this->userBillingData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    /**
     * @return Collection<int, City>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): self
    {
        if (!$this->cities->contains($city)) {
            $this->cities[] = $city;
            $city->setCounty($this);
        }

        return $this;
    }

    public function removeCity(City $city): self
    {
        if ($this->cities->removeElement($city)) {
            // set the owning side to null (unless already changed)
            if ($city->getCounty() === $this) {
                $city->setCounty(null);
            }
        }

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
            $userBillingData->setCounty($this);
        }

        return $this;
    }

    public function removeUserBillingData(UserBillingData $userBillingData): self
    {
        if ($this->userBillingData->removeElement($userBillingData)) {
            // set the owning side to null (unless already changed)
            if ($userBillingData->getCounty() === $this) {
                $userBillingData->setCounty(null);
            }
        }

        return $this;
    }
}
