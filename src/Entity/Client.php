<?php

namespace App\Entity;

use App\Entity\UserAccount;
use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'client_id')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $organizationName = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Resource::class)]
    private Collection $resources;

    #[ORM\OneToOne(inversedBy: 'client')]
    #[ORM\JoinColumn(name: 'user_account_id', referencedColumnName: 'user_account_id', nullable: false)]
    private UserAccount $userAccount;

    public function __construct()
    {
        $this->resources = new ArrayCollection();
    }

    public function getResources(): Collection
    {
        return $this->resources;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganizationName(): ?string
    {
        return $this->organizationName;
    }

    public function setOrganizationName(?string $organizationName): self
    {
        $this->organizationName = $organizationName;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getUserAccount(): UserAccount
    {
        return $this->userAccount;
    }

    public function setUserAccount(UserAccount $userAccount): self
    {
        $this->userAccount = $userAccount;
        return $this;
    }
}
