<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'creneau')]
class Creneau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    private $date_debut;

    #[ORM\Column(type: 'datetime')]
    private $date_fin;

    #[ORM\ManyToOne(targetEntity: Ressource::class, inversedBy: 'creneaux')]
    #[ORM\JoinColumn(nullable: false)]
    private $ressource;

    #[ORM\OneToOne(mappedBy: 'creneau', targetEntity: Reservation::class)]
    private $reservation;

    
    public function getId(): ?int
    {
    return $this->id;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $date_debut): self
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;
        return $this;
    }

    public function getRessourceId(): ?Ressource
    {
        return $this->ressource_id;
    }

    public function setRessourceId(?Ressource $ressource_id): self
    {
        $this->ressource_id = $ressource_id;
        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        if ($reservation !== null && $reservation->getCreneau() !== $this) {
            $reservation->setCreneau($this);
        }

        $this->reservation = $reservation;
        return $this;
    }

}