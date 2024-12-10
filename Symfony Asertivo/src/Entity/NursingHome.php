<?php

namespace App\Entity;

use App\Repository\NursingHomeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=NursingHomeRepository::class)
 * @ORM\Table(name="camine_lists")
 */
class NursingHome
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
     /**
     * @ORM\Column(type="string", length="100", unique=true)
     */
    private $uid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logo;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;
    
    /**
     * @ORM\OneToMany(targetEntity=NursingHomeRoom::class, mappedBy="nursingHome")
     */
    private $nursingHomeRooms;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="nursingHome")
     */
    private $users;

    /**
     * @ORM\Column(name="j", type="string", length=50, nullable=true)
     */
    private $registrationNumber;

    /**
     * @ORM\Column(name="cui", type="string", length=25, nullable=true)
     */
    private $cui;

    /**
     * @ORM\Column(name="nume_mfinante", type="string", length=150, nullable=true)
     */
    private $officialName;

    /**
     * @ORM\Column(name="cod_caen", type="string", length=25, nullable=true)
     */
    private $caenCode;

    /**
     * @ORM\Column(name="cod_caen_label" ,type="string", length=255, nullable=true)
     */
    private $caenCodeLabel;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $dpoEmail;
    
    /**
     * @ORM\ManyToOne(targetEntity=County::class)
     */
    private $county;

    /**
     * @ORM\ManyToOne(targetEntity=City::class)
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="nursingHomes")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=Pacient::class, mappedBy="nursingHome")
     */
    private $pacients;

    /**
     * @ORM\OneToMany(targetEntity=CookMenu::class, mappedBy="nursingHome")
     */
    private $cookMenus;

    public function __construct()
    {
        $this->uid = Uuid::v4();
        $this->createdAt = new \DateTime();
        $this->nursingHomeRooms = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->pacients = new ArrayCollection();
        $this->cookMenus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

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
    
    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }


    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setNursingHome($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getNursingHome() === $this) {
                $user->setNursingHome(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection<int, NursingHomeRoom>
     */
    public function getNursingHomeRooms(): Collection
    {
        return $this->nursingHomeRooms;
    }

    public function addNursingHomeRoom(NursingHomeRoom $nursingHomeRoom): self
    {
        if (!$this->nursingHomeRooms->contains($nursingHomeRoom)) {
            $this->nursingHomeRooms[] = $nursingHomeRoom;
            $nursingHomeRoom->setNursingHome($this);
        }

        return $this;
    }

    public function removeNursingHomeRoom(NursingHomeRoom $nursingHomeRoom): self
    {
        if ($this->nursingHomeRooms->removeElement($nursingHomeRoom)) {
            // set the owning side to null (unless already changed)
            if ($nursingHomeRoom->getNursingHome() === $this) {
                $nursingHomeRoom->setNursingHome(null);
            }
        }

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): self
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getCui(): ?string
    {
        return $this->cui;
    }

    public function setCui(?string $cui): self
    {
        $this->cui = $cui;

        return $this;
    }

    public function getOfficialName(): ?string
    {
        return $this->officialName;
    }

    public function setOfficialName(?string $officialName): self
    {
        $this->officialName = $officialName;

        return $this;
    }

    public function getCaenCode(): ?string
    {
        return $this->caenCode;
    }

    public function setCaenCode(?string $caenCode): self
    {
        $this->caenCode = $caenCode;

        return $this;
    }

    public function getCaenCodeLabel(): ?string
    {
        return $this->caenCodeLabel;
    }

    public function setCaenCodeLabel(?string $caenCodeLabel): self
    {
        $this->caenCodeLabel = $caenCodeLabel;

        return $this;
    }

    public function getDpoEmail(): ?string
    {
        return $this->dpoEmail;
    }

    public function setDpoEmail(?string $dpoEmail): self
    {
        $this->dpoEmail = $dpoEmail;

        return $this;
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

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Pacient>
     */
    public function getPacients(): Collection
    {
        return $this->pacients;
    }

    public function addPacient(Pacient $pacient): self
    {
        if (!$this->pacients->contains($pacient)) {
            $this->pacients[] = $pacient;
            $pacient->setNursingHome($this);
        }

        return $this;
    }

    public function removePacient(Pacient $pacient): self
    {
        if ($this->pacients->removeElement($pacient)) {
            // set the owning side to null (unless already changed)
            if ($pacient->getNursingHome() === $this) {
                $pacient->setNursingHome(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CookMenu>
     */
    public function getCookMenus(): Collection
    {
        return $this->cookMenus;
    }

    public function addCookMenu(CookMenu $cookMenu): self
    {
        if (!$this->cookMenus->contains($cookMenu)) {
            $this->cookMenus[] = $cookMenu;
            $cookMenu->setNursingHome($this);
        }

        return $this;
    }

    public function removeCookMenu(CookMenu $cookMenu): self
    {
        if ($this->cookMenus->removeElement($cookMenu)) {
            // set the owning side to null (unless already changed)
            if ($cookMenu->getNursingHome() === $this) {
                $cookMenu->setNursingHome(null);
            }
        }

        return $this;
    }

}
