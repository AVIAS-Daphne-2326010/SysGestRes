<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'booking_id')]
    private ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $cancelledAt = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_account_id', referencedColumnName: 'user_account_id', nullable: false)]
    private UserAccount $userAccount;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'timeslot_id', referencedColumnName: 'timeslot_id', nullable: false)]
    private Timeslot $timeslot;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
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

    public function getCancelledAt(): ?\DateTimeInterface
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTimeInterface $cancelledAt): self
    {
        $this->cancelledAt = $cancelledAt;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
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

    public function getTimeslot(): Timeslot
    {
        return $this->timeslot;
    }

    public function setTimeslot(Timeslot $timeslot): self
    {
        $this->timeslot = $timeslot;
        return $this;
    }
}
