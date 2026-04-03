<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ProduitRepository;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name: 'produit')]
class Produit
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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $categorie = null;

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $prix = null;

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $stock_actuel = null;

    public function getStock_actuel(): ?int
    {
        return $this->stock_actuel;
    }

    public function setStock_actuel(?int $stock_actuel): self
    {
        $this->stock_actuel = $stock_actuel;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $stock_min = null;

    public function getStock_min(): ?int
    {
        return $this->stock_min;
    }

    public function setStock_min(?int $stock_min): self
    {
        $this->stock_min = $stock_min;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_creation = null;

    public function getDate_creation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDate_creation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $ressources_necessaires = null;

    public function getRessources_necessaires(): ?string
    {
        return $this->ressources_necessaires;
    }

    public function setRessources_necessaires(?string $ressources_necessaires): self
    {
        $this->ressources_necessaires = $ressources_necessaires;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $image_path = null;

    public function getImage_path(): ?string
    {
        return $this->image_path;
    }

    public function setImage_path(?string $image_path): self
    {
        $this->image_path = $image_path;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_fabrication = null;

    public function getDate_fabrication(): ?\DateTimeInterface
    {
        return $this->date_fabrication;
    }

    public function setDate_fabrication(?\DateTimeInterface $date_fabrication): self
    {
        $this->date_fabrication = $date_fabrication;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_peremption = null;

    public function getDate_peremption(): ?\DateTimeInterface
    {
        return $this->date_peremption;
    }

    public function setDate_peremption(?\DateTimeInterface $date_peremption): self
    {
        $this->date_peremption = $date_peremption;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_garantie = null;

    public function getDate_garantie(): ?\DateTimeInterface
    {
        return $this->date_garantie;
    }

    public function setDate_garantie(?\DateTimeInterface $date_garantie): self
    {
        $this->date_garantie = $date_garantie;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $details = null;

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function getStockActuel(): ?int
    {
        return $this->stock_actuel;
    }

    public function setStockActuel(?int $stock_actuel): static
    {
        $this->stock_actuel = $stock_actuel;

        return $this;
    }

    public function getStockMin(): ?int
    {
        return $this->stock_min;
    }

    public function setStockMin(?int $stock_min): static
    {
        $this->stock_min = $stock_min;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTime $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getRessourcesNecessaires(): ?string
    {
        return $this->ressources_necessaires;
    }

    public function setRessourcesNecessaires(?string $ressources_necessaires): static
    {
        $this->ressources_necessaires = $ressources_necessaires;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->image_path;
    }

    public function setImagePath(?string $image_path): static
    {
        $this->image_path = $image_path;

        return $this;
    }

    public function getDateFabrication(): ?\DateTime
    {
        return $this->date_fabrication;
    }

    public function setDateFabrication(?\DateTime $date_fabrication): static
    {
        $this->date_fabrication = $date_fabrication;

        return $this;
    }

    public function getDatePeremption(): ?\DateTime
    {
        return $this->date_peremption;
    }

    public function setDatePeremption(?\DateTime $date_peremption): static
    {
        $this->date_peremption = $date_peremption;

        return $this;
    }

    public function getDateGarantie(): ?\DateTime
    {
        return $this->date_garantie;
    }

    public function setDateGarantie(?\DateTime $date_garantie): static
    {
        $this->date_garantie = $date_garantie;

        return $this;
    }

}
