<?php

namespace App\Entity;

use App\Repository\FoodEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=FoodEntryRepository::class)
 */
class FoodEntry
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Assert\Positive
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\LessThanOrEqual("now")
     */
    private $eatDate;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\Length(
     *      min = 2,
     *      max = 50)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Positive
     */
    private $calories;

    /**
     * @ORM\Column(type="boolean")
     */
    private $skipDiet = false;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Positive
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEatDate(): ?\DateTimeInterface
    {
        return $this->eatDate;
    }

    public function setEatDate(\DateTimeInterface $eatDate): self
    {
        $this->eatDate = $eatDate;

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

    public function getCalories(): ?int
    {
        return $this->calories;
    }

    public function setCalories(int $calories): self
    {
        $this->calories = $calories;

        return $this;
    }

    public function getUser(): ?int
    {
        return $this->user;
    }

    public function setUser(int $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSkipDiet(): ?bool
    {
        return $this->skipDiet;
    }

    public function setSkipDiet(bool $skipDiet): self
    {
        $this->skipDiet = $skipDiet;

        return $this;
    }
}
