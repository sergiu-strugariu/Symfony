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
     * @var Collection<int, Job>
     */
    #[ORM\OneToMany(targetEntity: Job::class, mappedBy: 'city')]
    private Collection $jobs;

    /**
     * @var Collection<int, TrainingCourse>
     */
    #[ORM\OneToMany(targetEntity: TrainingCourse::class, mappedBy: 'city')]
    private Collection $trainingCourses;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\OneToMany(targetEntity: Company::class, mappedBy: 'city', orphanRemoval: true)]
    private Collection $companies;

    public function __construct()
    {
        $this->jobs = new ArrayCollection();
        $this->trainingCourses = new ArrayCollection();
        $this->companies = new ArrayCollection();
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
     * @return Collection<int, Job>
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function addJob(Job $job): static
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs->add($job);
            $job->setCity($this);
        }

        return $this;
    }

    public function removeJob(Job $job): static
    {
        if ($this->jobs->removeElement($job)) {
            // set the owning side to null (unless already changed)
            if ($job->getCity() === $this) {
                $job->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TrainingCourse>
     */
    public function getTrainingCourses(): Collection
    {
        return $this->trainingCourses;
    }

    public function addTrainingCourse(TrainingCourse $trainingCourse): static
    {
        if (!$this->trainingCourses->contains($trainingCourse)) {
            $this->trainingCourses->add($trainingCourse);
            $trainingCourse->setCity($this);
        }

        return $this;
    }

    public function removeTrainingCourse(TrainingCourse $trainingCourse): static
    {
        if ($this->trainingCourses->removeElement($trainingCourse)) {
            // set the owning side to null (unless already changed)
            if ($trainingCourse->getCity() === $this) {
                $trainingCourse->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): static
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
            $company->setCity($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        if ($this->companies->removeElement($company)) {
            // set the owning side to null (unless already changed)
            if ($company->getCity() === $this) {
                $company->setCity(null);
            }
        }

        return $this;
    }
}
