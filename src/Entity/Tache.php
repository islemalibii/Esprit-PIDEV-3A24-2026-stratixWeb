<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\TacheRepository;

#[ORM\Entity(repositoryClass: TacheRepository::class)]
#[ORM\Table(name: 'tache')]
class Tache
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
    private ?string $titre = null;

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;
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
    private ?\DateTimeInterface $deadline = null;

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;
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
    private ?int $employe_id = null;

    public function getEmploye_id(): ?int
    {
        return $this->employe_id;
    }

    public function setEmploye_id(?int $employe_id): self
    {
        $this->employe_id = $employe_id;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $projet_id = null;

    public function getProjet_id(): ?int
    {
        return $this->projet_id;
    }

    public function setProjet_id(?int $projet_id): self
    {
        $this->projet_id = $projet_id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $priorite = null;

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(?string $priorite): self
    {
        $this->priorite = $priorite;
        return $this;
    }

    public function getEmployeId(): ?int
    {
        return $this->employe_id;
    }

    public function setEmployeId(?int $employe_id): static
    {
        $this->employe_id = $employe_id;

        return $this;
    }

    public function getProjetId(): ?int
    {
        return $this->projet_id;
    }

    public function setProjetId(?int $projet_id): static
    {
        $this->projet_id = $projet_id;

        return $this;
    }

}
