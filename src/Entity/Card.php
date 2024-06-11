<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use App\Repository\CardRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getCards","getTypes"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getCards","getTypes"])]
    #[Assert\NotBlank(message: "The tittle is required")]
    #[Assert\Length(max: 255, maxMessage: "The tittle must be at most {{ limit }} characters")]
    private ?string $tittle = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getCards","getTypes"])]
    #[Assert\NotBlank(message: "The description is required")]
    #[Assert\Length(max: 255, maxMessage: "The description must be at most {{ limit }} characters")]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'Cards')]
    #[ORM\JoinColumn(onDelete:"CASCADE")]
    #[Groups(["getCards"])]
    private ?Type $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTittle(): ?string
    {
        return $this->tittle;
    }

    public function setTittle(string $tittle): static
    {
        $this->tittle = $tittle;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): static
    {
        $this->type = $type;

        return $this;
    }
}
