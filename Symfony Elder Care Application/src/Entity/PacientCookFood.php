<?php

namespace App\Entity;

use App\Repository\PacientCookFoodRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientCookFoodRepository::class)
 * @ORM\Table(name="pacients__date_bucatarie")
 */
class PacientCookFood
{
    
    const FOOD_OPTION_NO_SALT = 'Fara sare';
    const FOOD_OPTION_DIABETES_WITHOUT_INSULIN = 'Diabet fara insulina';
    const FOOD_OPTION_MASHED = 'Pasat';
    const FOOD_OPTION_COFFEE = 'Cafea';
    const FOOD_OPTION_DOUBLE_PORTION = 'Portie dubla';
    const FOOD_OPTION_NO_SAUSAGES_NO_MINCED_MEAT = 'Fara mezeluri si fara carne tocata';
    const FOOD_OPTION_PREFERENTIAL = 'Preferential';
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Pacient::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $pacient;

    /**
     * @ORM\Column(type="string", length=70)
     */
    private $foodOption;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observations;

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

    public function getPacient(): ?Pacient
    {
        return $this->pacient;
    }

    public function setPacient(?Pacient $pacient): self
    {
        $this->pacient = $pacient;

        return $this;
    }

    public function getFoodOption(): ?string
    {
        return $this->foodOption;
    }

    public function setFoodOption(string $foodOption): self
    {
        $this->foodOption = $foodOption;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): self
    {
        $this->observations = $observations;

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
    
    public static function getOptions() {
        return [
            self::FOOD_OPTION_NO_SALT => self::FOOD_OPTION_NO_SALT,
            self::FOOD_OPTION_DIABETES_WITHOUT_INSULIN => self::FOOD_OPTION_DIABETES_WITHOUT_INSULIN,
            self::FOOD_OPTION_MASHED => self::FOOD_OPTION_MASHED,
            self::FOOD_OPTION_COFFEE => self::FOOD_OPTION_COFFEE,
            self::FOOD_OPTION_DOUBLE_PORTION => self::FOOD_OPTION_DOUBLE_PORTION,
            self::FOOD_OPTION_NO_SAUSAGES_NO_MINCED_MEAT => self::FOOD_OPTION_NO_SAUSAGES_NO_MINCED_MEAT,
            self::FOOD_OPTION_PREFERENTIAL => self::FOOD_OPTION_PREFERENTIAL
        ];
    }
}
