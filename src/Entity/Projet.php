<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ProjetRepository;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
#[ORM\Table(name: 'projet')]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_debut = null;

    public function getDate_debut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDate_debut(?\DateTimeInterface $date_debut): self
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_fin = null;

    public function getDate_fin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDate_fin(?\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $budget = null;

    public function getBudget(): ?float
    {
        return $this->budget;
    }

    public function setBudget(?float $budget): self
    {
        $this->budget = $budget;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $statut = null;

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $responsable_id = null;

    public function getResponsable_id(): ?int
    {
        return $this->responsable_id;
    }

    public function setResponsable_id(?int $responsable_id): self
    {
        $this->responsable_id = $responsable_id;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $equipe_membres = null;

    public function getEquipe_membres(): ?string
    {
        return $this->equipe_membres;
    }

    public function setEquipe_membres(?string $equipe_membres): self
    {
        $this->equipe_membres = $equipe_membres;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $progression = null;

    public function getProgression(): ?int
    {
        return $this->progression;
    }

    public function setProgression(?int $progression): self
    {
        $this->progression = $progression;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $is_archived = null;

    public function is_archived(): ?bool
    {
        return $this->is_archived;
    }

    public function setIs_archived(?bool $is_archived): self
    {
        $this->is_archived = $is_archived;
        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->date_debut;
    }

    public function setDateDebut(?\DateTime $date_debut): static
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTime $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getResponsableId(): ?int
    {
        return $this->responsable_id;
    }

    public function setResponsableId(?int $responsable_id): static
    {
        $this->responsable_id = $responsable_id;

        return $this;
    }

    public function getEquipeMembres(): ?string
    {
        return $this->equipe_membres;
    }

    public function setEquipeMembres(?string $equipe_membres): static
    {
        $this->equipe_membres = $equipe_membres;

        return $this;
    }

    public function isArchived(): ?bool
    {
        return $this->is_archived;
    }

    public function setIsArchived(?bool $is_archived): static
    {
        $this->is_archived = $is_archived;

        return $this;
    }

}
