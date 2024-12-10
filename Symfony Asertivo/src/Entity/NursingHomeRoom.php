<?php

namespace App\Entity;

use App\Repository\NursingHomeRoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=NursingHomeRoomRepository::class)
 * @ORM\Table(name="camine_locatii_room_list")
 */
class NursingHomeRoom
{
    
    const ROOM_TYPE_SINGLE = 'Single';
    const ROOM_TYPE_DOUBLE = 'Double';
    
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
     * @ORM\ManyToOne(targetEntity=NursingHome::class, inversedBy="nursingHomeRooms")
     * @ORM\JoinColumn(name="id_camin", nullable=false)
     */
    private $nursingHome;

    /**
     * @ORM\Column(name="nr_camera", type="string", length=10)
     */
    private $roomNumber;

    /**
     * @ORM\Column(name="nr_locuri", type="integer")
     */
    private $numberOfBeds;

    /**
     * @ORM\Column(name="detalii", type="text", nullable=true)
     */
    private $details;

    /**
     * @ORM\Column(name="tip_camera", type="string", length=50)
     */
    private $roomType;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=PacientRoom::class, mappedBy="nursingHomeRoom")
     */
    private $pacients;

    /**
     * @ORM\Column(name="etaj", type="string", length=10, nullable=true)
     */
    private $floor;

    public function __construct()
    {
        $this->uid = Uuid::v4();
        $this->createdAt = new \DateTime();
        $this->pacients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNursingHome(): ?NursingHome
    {
        return $this->nursingHome;
    }

    public function setNursingHome(?NursingHome $nursingHome): self
    {
        $this->nursingHome = $nursingHome;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid($uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getRoomNumber(): ?string
    {
        return $this->roomNumber;
    }

    public function setRoomNumber(string $roomNumber): self
    {
        $this->roomNumber = $roomNumber;

        return $this;
    }

    public function getNumberOfBeds(): ?int
    {
        return $this->numberOfBeds;
    }

    public function setNumberOfBeds(int $numberOfBeds): self
    {
        $this->numberOfBeds = $numberOfBeds;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getRoomType(): ?string
    {
        return $this->roomType;
    }

    public function setRoomType(string $roomType): self
    {
        $this->roomType = $roomType;

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
    
    public static function getRoomTypes() {
        return [
            self::ROOM_TYPE_SINGLE => self::ROOM_TYPE_SINGLE,
            self::ROOM_TYPE_DOUBLE => self::ROOM_TYPE_DOUBLE
        ];
    }

    /**
     * @return Collection<int, PacientRoom>
     */
    public function getPacients(): Collection
    {
        return $this->pacients;
    }

    public function addPacient(PacientRoom $pacient): self
    {
        if (!$this->pacients->contains($pacient)) {
            $this->pacients[] = $pacient;
            $pacient->setNursingHomeRoom($this);
        }

        return $this;
    }

    public function removePacient(PacientRoom $pacient): self
    {
        if ($this->pacients->removeElement($pacient)) {
            // set the owning side to null (unless already changed)
            if ($pacient->getNursingHomeRoom() === $this) {
                $pacient->setNursingHomeRoom(null);
            }
        }

        return $this;
    }

    public function getFloor(): ?string
    {
        return $this->floor;
    }

    public function setFloor(?string $floor): self
    {
        $this->floor = $floor;

        return $this;
    }
}
