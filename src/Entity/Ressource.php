<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\Table(name: 'ressource')]
class Ressource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

    #[ORM\Column(type: 'string', length: 50)]
    private $type; // 'salle', 'coworking', 'restaurant'

    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    #[ORM\OneToMany(mappedBy: 'ressource', targetEntity: Creneau::class)]
    private $creneaux;

    public function __construct()
    {
        $this->creneaux = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Creneau[]
     */
    public function getCreneaux(): Collection
    {
        return $this->creneaux;
    }

    public function addCreneau(Creneau $creneau): self
    {
        if (!$this->creneaux->contains($creneau)) {
            $this->creneaux[] = $creneau;
            $creneau->setRessource($this);
        }

        return $this;
    }

    public function removeCreneau(Creneau $creneau): self
    {
        if ($this->creneaux->removeElement($creneau)) {
            // set the owning side to null (unless already changed)
            if ($creneau->getRessource() === $this) {
                $creneau->setRessource(null);
            }
        }

        return $this;
    }
}