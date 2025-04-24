<?php

namespace App\Entity;

use App\Repository\UserAccountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAccountRepository::class)]
class UserAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'user_account_id')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 150, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', length: 50)]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 50)]
    private string $lastName;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'role_id', nullable: false)]
    private Role $role;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): self
    {
        $this->role = $role;
        return $this;
    }
}
