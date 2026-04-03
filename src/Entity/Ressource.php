<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\RessourceRepository;

#[ORM\Entity(repositoryClass: RessourceRepository::class)]
#[ORM\Table(name: 'ressource')]
class Ressource
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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $type_ressource = null;

    public function getType_ressource(): ?string
    {
        return $this->type_ressource;
    }

    public function setType_ressource(?string $type_ressource): self
    {
        $this->type_ressource = $type_ressource;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $quantite = null;

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $fournisseur = null;

    public function getFournisseur(): ?string
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?string $fournisseur): self
    {
        $this->fournisseur = $fournisseur;
        return $this;
    }

    public function getTypeRessource(): ?string
    {
        return $this->type_ressource;
    }

    public function setTypeRessource(?string $type_ressource): static
    {
        $this->type_ressource = $type_ressource;

        return $this;
    }

}
