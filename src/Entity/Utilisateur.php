<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\UtilisateurRepository;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
class Utilisateur
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
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $tel = null;

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $password = null;

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $prenom = null;

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $cin = null;

    public function getCin(): ?int
    {
        return $this->cin;
    }

    public function setCin(int $cin): self
    {
        $this->cin = $cin;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_ajout = null;

    public function getDate_ajout(): ?\DateTimeInterface
    {
        return $this->date_ajout;
    }

    public function setDate_ajout(?\DateTimeInterface $date_ajout): self
    {
        $this->date_ajout = $date_ajout;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $role = null;

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $department = null;

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $poste = null;

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(?string $poste): self
    {
        $this->poste = $poste;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_embauche = null;

    public function getDate_embauche(): ?\DateTimeInterface
    {
        return $this->date_embauche;
    }

    public function setDate_embauche(?\DateTimeInterface $date_embauche): self
    {
        $this->date_embauche = $date_embauche;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $competences = null;

    public function getCompetences(): ?string
    {
        return $this->competences;
    }

    public function setCompetences(?string $competences): self
    {
        $this->competences = $competences;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $salaire = null;

    public function getSalaire(): ?float
    {
        return $this->salaire;
    }

    public function setSalaire(?float $salaire): self
    {
        $this->salaire = $salaire;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $failed_login_attempts = null;

    public function getFailed_login_attempts(): ?int
    {
        return $this->failed_login_attempts;
    }

    public function setFailed_login_attempts(?int $failed_login_attempts): self
    {
        $this->failed_login_attempts = $failed_login_attempts;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $account_locked = null;

    public function isAccount_locked(): ?bool
    {
        return $this->account_locked;
    }

    public function setAccount_locked(?bool $account_locked): self
    {
        $this->account_locked = $account_locked;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $locked_until = null;

    public function getLocked_until(): ?\DateTimeInterface
    {
        return $this->locked_until;
    }

    public function setLocked_until(?\DateTimeInterface $locked_until): self
    {
        $this->locked_until = $locked_until;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $two_factor_enabled = null;

    public function isTwo_factor_enabled(): ?bool
    {
        return $this->two_factor_enabled;
    }

    public function setTwo_factor_enabled(?bool $two_factor_enabled): self
    {
        $this->two_factor_enabled = $two_factor_enabled;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $two_factor_secret = null;

    public function getTwo_factor_secret(): ?string
    {
        return $this->two_factor_secret;
    }

    public function setTwo_factor_secret(?string $two_factor_secret): self
    {
        $this->two_factor_secret = $two_factor_secret;
        return $this;
    }

    public function getDateAjout(): ?\DateTime
    {
        return $this->date_ajout;
    }

    public function setDateAjout(?\DateTime $date_ajout): static
    {
        $this->date_ajout = $date_ajout;

        return $this;
    }

    public function getDateEmbauche(): ?\DateTime
    {
        return $this->date_embauche;
    }

    public function setDateEmbauche(?\DateTime $date_embauche): static
    {
        $this->date_embauche = $date_embauche;

        return $this;
    }

    public function getFailedLoginAttempts(): ?int
    {
        return $this->failed_login_attempts;
    }

    public function setFailedLoginAttempts(?int $failed_login_attempts): static
    {
        $this->failed_login_attempts = $failed_login_attempts;

        return $this;
    }

    public function isAccountLocked(): ?bool
    {
        return $this->account_locked;
    }

    public function setAccountLocked(?bool $account_locked): static
    {
        $this->account_locked = $account_locked;

        return $this;
    }

    public function getLockedUntil(): ?\DateTime
    {
        return $this->locked_until;
    }

    public function setLockedUntil(?\DateTime $locked_until): static
    {
        $this->locked_until = $locked_until;

        return $this;
    }

    public function isTwoFactorEnabled(): ?bool
    {
        return $this->two_factor_enabled;
    }

    public function setTwoFactorEnabled(?bool $two_factor_enabled): static
    {
        $this->two_factor_enabled = $two_factor_enabled;

        return $this;
    }

    public function getTwoFactorSecret(): ?string
    {
        return $this->two_factor_secret;
    }

    public function setTwoFactorSecret(?string $two_factor_secret): static
    {
        $this->two_factor_secret = $two_factor_secret;

        return $this;
    }

}
