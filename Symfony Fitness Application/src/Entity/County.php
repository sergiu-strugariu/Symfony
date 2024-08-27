<?php

namespace App\Entity;

use App\Repository\CountyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountyRepository::class)]
class County
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $code = null;

    #[ORM\Column(length: 40)]
    private ?string $name = null;

    /**
     * @var Collection<int, City>
     */
    #[ORM\OneToMany(targetEntity: City::class, mappedBy: 'county', orphanRemoval: true)]
    private Collection $cities;

    /**
     * @var Collection<int, Education>
     */
    #[ORM\OneToMany(targetEntity: Education::class, mappedBy: 'county')]
    private Collection $educations;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
        $this->educations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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

    /**
     * @return Collection<int, City>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): static
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
            $city->setCounty($this);
        }

        return $this;
    }

    public function removeCity(City $city): static
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
     * @return Collection<int, Education>
     */
    public function getEducation(): Collection
    {
        return $this->educations;
    }

    public function addEducation(Education $educations): static
    {
        if (!$this->educations->contains($educations)) {
            $this->educations->add($educations);
            $educations->setCounty($this);
        }

        return $this;
    }

    public function removeEducation(Education $educations): static
    {
        if ($this->educations->removeElement($educations)) {
            // set the owning side to null (unless already changed)
            if ($educations->getCounty() === $this) {
                $educations->setCounty(null);
            }
        }

        return $this;
    }
}