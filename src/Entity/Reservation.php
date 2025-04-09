<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'reservation')]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private $utilisateur;

    #[ORM\OneToOne(targetEntity: Creneau::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $creneau;

    #[ORM\Column(type: 'datetime')]
    private $date_reservation;

    public function __construct()
    {
        $this->date_reservation = new \DateTime();
    }

    #[ORM\PrePersist]
    public function verifierDisponibilite()
    {
        if ($this->creneau->getReservation() !== null) {
            throw new \RuntimeException('Ce créneau est déjà réservé');
        }
    }

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateurId(): ?Utilisateur
    {
        return $this->utilisateur_id;
    }

    public function setUtilisateurId(?Utilisateur $utilisateur_id): self
    {
        $this->utilisateur_id = $utilisateur_id;
        return $this;
    }

    public function getCreneauId(): ?Creneau
    {
        return $this->creneau_id;
    }

    public function setCreneauId(?Creneau $creneau_id): self
    {
        $this->creneau_id = $creneau_id;
        return $this;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->date_reservation;
    }

    public function setDateReservation(\DateTimeInterface $date_reservation): self
    {
        $this->date_reservation = $date_reservation;
        return $this;
    }

}