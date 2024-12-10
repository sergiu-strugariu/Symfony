<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?County $county = null;

    #[ORM\Column(length: 199)]
    private ?string $longitude = null;

    #[ORM\Column(length: 199)]
    private ?string $latitude = null;

    #[ORM\Column(length: 99)]
    private ?string $name = null;

    /**
     * @var Collection<int, Education>
     */
    #[ORM\OneToMany(targetEntity: Education::class, mappedBy: 'city')]
    private Collection $educations;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'city')]
    private Collection $users;

    /**
     * @var Collection<int, EducationRegistration>
     */
    #[ORM\OneToMany(targetEntity: EducationRegistration::class, mappedBy: 'city')]
    private Collection $educationRegistrations;

    public function __construct()
    {
        $this->educations = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->educationRegistrations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;

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
            $educations->setCity($this);
        }

        return $this;
    }

    public function removeEducation(Education $educations): static
    {
        if ($this->educations->removeElement($educations)) {
            // set the owning side to null (unless already changed)
            if ($educations->getCity() === $this) {
                $educations->setCity(null);
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
            $user->setCity($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCity() === $this) {
                $user->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EducationRegistration>
     */
    public function getEducationRegistrations(): Collection
    {
        return $this->educationRegistrations;
    }

    public function addEducationRegistration(EducationRegistration $educationRegistration): static
    {
        if (!$this->educationRegistrations->contains($educationRegistration)) {
            $this->educationRegistrations->add($educationRegistration);
            $educationRegistration->setCity($this);
        }

        return $this;
    }

    public function removeEducationRegistration(EducationRegistration $educationRegistration): static
    {
        if ($this->educationRegistrations->removeElement($educationRegistration)) {
            // set the owning side to null (unless already changed)
            if ($educationRegistration->getCity() === $this) {
                $educationRegistration->setCity(null);
            }
        }

        return $this;
    }
}