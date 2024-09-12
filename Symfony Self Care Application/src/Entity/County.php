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
     * @var Collection<int, Job>
     */
    #[ORM\OneToMany(targetEntity: Job::class, mappedBy: 'county', orphanRemoval: true)]
    private Collection $jobs;

    /**
     * @var Collection<int, TrainingCourse>
     */
    #[ORM\OneToMany(targetEntity: TrainingCourse::class, mappedBy: 'county', orphanRemoval: true)]
    private Collection $trainingCourses;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\OneToMany(targetEntity: Company::class, mappedBy: 'county', orphanRemoval: true)]
    private Collection $companies;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'county', orphanRemoval: true)]
    private Collection $events;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
        $this->jobs = new ArrayCollection();
        $this->trainingCourses = new ArrayCollection();
        $this->companies = new ArrayCollection();
        $this->events = new ArrayCollection();
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
            $job->setCounty($this);
        }

        return $this;
    }

    public function removeJob(Job $job): static
    {
        if ($this->jobs->removeElement($job)) {
            // set the owning side to null (unless already changed)
            if ($job->getCounty() === $this) {
                $job->setCounty(null);
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
            $trainingCourse->setCounty($this);
        }

        return $this;
    }

    public function removeTrainingCourse(TrainingCourse $trainingCourse): static
    {
        if ($this->trainingCourses->removeElement($trainingCourse)) {
            // set the owning side to null (unless already changed)
            if ($trainingCourse->getCounty() === $this) {
                $trainingCourse->setCounty(null);
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
            $company->setCounty($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        if ($this->companies->removeElement($company)) {
            // set the owning side to null (unless already changed)
            if ($company->getCounty() === $this) {
                $company->setCounty(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setCounty($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getCounty() === $this) {
                $event->setCounty(null);
            }
        }

        return $this;
    }
}
