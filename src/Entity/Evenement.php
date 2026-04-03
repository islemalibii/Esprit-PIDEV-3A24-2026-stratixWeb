<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EvenementRepository;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
class Evenement
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
    private ?string $type_event = null;

    public function getType_event(): ?string
    {
        return $this->type_event;
    }

    public function setType_event(?string $type_event): self
    {
        $this->type_event = $type_event;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_event = null;

    public function getDate_event(): ?\DateTimeInterface
    {
        return $this->date_event;
    }

    public function setDate_event(?\DateTimeInterface $date_event): self
    {
        $this->date_event = $date_event;
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
    private ?string $lieu = null;

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $titre = null;

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isArchived = null;

    public function isIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(?bool $isArchived): self
    {
        $this->isArchived = $isArchived;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $image_url = null;

    public function getImage_url(): ?string
    {
        return $this->image_url;
    }

    public function setImage_url(?string $image_url): self
    {
        $this->image_url = $image_url;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $latitude = null;

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $longitude = null;

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: EventFeedback::class, mappedBy: 'evenement')]
    private Collection $eventFeedbacks;

    public function __construct()
    {
        $this->eventFeedbacks = new ArrayCollection();
    }

    /**
     * @return Collection<int, EventFeedback>
     */
    public function getEventFeedbacks(): Collection
    {
        if (!$this->eventFeedbacks instanceof Collection) {
            $this->eventFeedbacks = new ArrayCollection();
        }
        return $this->eventFeedbacks;
    }

    public function addEventFeedback(EventFeedback $eventFeedback): self
    {
        if (!$this->getEventFeedbacks()->contains($eventFeedback)) {
            $this->getEventFeedbacks()->add($eventFeedback);
        }
        return $this;
    }

    public function removeEventFeedback(EventFeedback $eventFeedback): self
    {
        $this->getEventFeedbacks()->removeElement($eventFeedback);
        return $this;
    }

    public function getTypeEvent(): ?string
    {
        return $this->type_event;
    }

    public function setTypeEvent(?string $type_event): static
    {
        $this->type_event = $type_event;

        return $this;
    }

    public function getDateEvent(): ?\DateTime
    {
        return $this->date_event;
    }

    public function setDateEvent(?\DateTime $date_event): static
    {
        $this->date_event = $date_event;

        return $this;
    }

    public function isArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(?string $image_url): static
    {
        $this->image_url = $image_url;

        return $this;
    }

}
