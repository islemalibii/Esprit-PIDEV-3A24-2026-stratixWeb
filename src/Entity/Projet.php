<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column]
    #[Assert\Positive(message: "Le budget doit être positif.")]
    private ?float $budget = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = "Planifié";


    #[ORM\Column]
    private ?bool $isArchived = false;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $responsable = null;

    #[ORM\ManyToMany(targetEntity: Utilisateur::class)]
    private Collection $membres;

    public function __construct()
    {
        $this->membres = new ArrayCollection();
        $this->statut = "Planifié";
        $this->isArchived = false;
        $this->dateDebut = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getDateDebut(): ?\DateTimeInterface { return $this->dateDebut; }
    public function setDateDebut(?\DateTimeInterface $dateDebut): self { $this->dateDebut = $dateDebut; return $this; }

    public function getDateFin(): ?\DateTimeInterface { return $this->dateFin; }
    public function setDateFin(?\DateTimeInterface $dateFin): self { $this->dateFin = $dateFin; return $this; }

    public function getBudget(): ?float { return $this->budget; }
    public function setBudget(?float $budget): self { $this->budget = $budget; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): self { $this->statut = $statut; return $this; }

    public function isIsArchived(): ?bool { return $this->isArchived; }
    public function setIsArchived(bool $isArchived): self { $this->isArchived = $isArchived; return $this; }

    public function getResponsable(): ?Utilisateur { return $this->responsable; }
    public function setResponsable(?Utilisateur $responsable): self { $this->responsable = $responsable; return $this; }

    public function getMembres(): Collection { return $this->membres; }

    public function addMembre(Utilisateur $membre): self
    {
        if (!$this->membres->contains($membre)) { $this->membres->add($membre); }
        return $this;
    }

    public function removeMembre(Utilisateur $membre): self
    {
        $this->membres->removeElement($membre);
        return $this;
    }
}