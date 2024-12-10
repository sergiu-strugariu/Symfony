<?php

namespace App\Entity;

use App\Repository\CookMenuItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CookMenuItemRepository::class)
 */
class CookMenuItem
{
    const MONDAY = 'luni';
    const TUESDAY = 'marti';
    const WEDNESDAY = 'miercuri';
    const THURSDAY = 'joi';
    const FRIDAY = 'vineri';
    const SATURDAY = 'sambata';
    const SUNDAY = 'duminica';
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=CookMenu::class, inversedBy="cookMenuItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cookMenu;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $day;

    /**
     * @ORM\Column(type="text")
     */
    private $breakfast;

    /**
     * @ORM\Column(type="text")
     */
    private $firstSnack;

    /**
     * @ORM\Column(type="text")
     */
    private $lunch;

    /**
     * @ORM\Column(type="text")
     */
    private $secondSnack;

    /**
     * @ORM\Column(type="text")
     */
    private $dinner;

    /**
     * @ORM\Column(type="text")
     */
    private $dz;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;
    
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCookMenu(): ?CookMenu
    {
        return $this->cookMenu;
    }

    public function setCookMenu(?CookMenu $cookMenu): self
    {
        $this->cookMenu = $cookMenu;

        return $this;
    }

    public function getDay(): ?string
    {
        return $this->day;
    }

    public function setDay(string $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getBreakfast(): ?string
    {
        return $this->breakfast;
    }

    public function setBreakfast(string $breakfast): self
    {
        $this->breakfast = $breakfast;

        return $this;
    }

    public function getFirstSnack(): ?string
    {
        return $this->firstSnack;
    }

    public function setFirstSnack(string $firstSnack): self
    {
        $this->firstSnack = $firstSnack;

        return $this;
    }

    public function getLunch(): ?string
    {
        return $this->lunch;
    }

    public function setLunch(string $lunch): self
    {
        $this->lunch = $lunch;

        return $this;
    }

    public function getSecondSnack(): ?string
    {
        return $this->secondSnack;
    }

    public function setSecondSnack(string $secondSnack): self
    {
        $this->secondSnack = $secondSnack;

        return $this;
    }

    public function getDinner(): ?string
    {
        return $this->dinner;
    }

    public function setDinner(string $dinner): self
    {
        $this->dinner = $dinner;

        return $this;
    }

    public function getDz(): ?string
    {
        return $this->dz;
    }

    public function setDz(string $dz): self
    {
        $this->dz = $dz;

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
    
    public static function getDays() {
        return [
            strtoupper(self::MONDAY) => self::MONDAY,
            strtoupper(self::TUESDAY) => self::TUESDAY,
            strtoupper(self::WEDNESDAY) => self::WEDNESDAY,
            strtoupper(self::THURSDAY) => self::THURSDAY,
            strtoupper(self::FRIDAY) => self::FRIDAY,
            strtoupper(self::SATURDAY) => self::SATURDAY,
            strtoupper(self::SUNDAY) => self::SUNDAY
        ];
    }
}
