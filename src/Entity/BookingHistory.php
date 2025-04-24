<?php

namespace App\Entity;

use App\Repository\BookingHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingHistoryRepository::class)]
class BookingHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'log_id')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $changedAt = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $changedBy = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'booking_id', referencedColumnName: 'booking_id', nullable: false)]
    private Booking $booking;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_account_id', referencedColumnName: 'user_account_id', nullable: false)]
    private UserAccount $userAccount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getChangedAt(): ?\DateTimeInterface
    {
        return $this->changedAt;
    }

    public function setChangedAt(?\DateTimeInterface $changedAt): self
    {
        $this->changedAt = $changedAt;
        return $this;
    }

    public function getChangedBy(): ?string
    {
        return $this->changedBy;
    }

    public function setChangedBy(?string $changedBy): self
    {
        $this->changedBy = $changedBy;
        return $this;
    }

    public function getBooking(): Booking
    {
        return $this->booking;
    }

    public function setBooking(Booking $booking): self
    {
        $this->booking = $booking;
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
