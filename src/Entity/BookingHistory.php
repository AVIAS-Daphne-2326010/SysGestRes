<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
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

    #[ORM\ManyToOne(targetEntity: Booking::class)]
    #[ORM\JoinColumn(name: "booking_id", referencedColumnName: "booking_id", nullable: true)] 
    private ?Booking $booking = null;


    #[ORM\ManyToOne(targetEntity: Resource::class)]
    #[ORM\JoinColumn(name: "resource_id", referencedColumnName: "resource_id", nullable: true)] 
    private ?Resource $resource = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_account_id', referencedColumnName: 'user_account_id', nullable: false)]
    private UserAccount $userAccount;

    #[ORM\ManyToOne(targetEntity: Timeslot::class)]
    #[ORM\JoinColumn(name: "timeslot_id", referencedColumnName: "timeslot_id", nullable: true)]
    private ?Timeslot $timeslot = null;

    // Getters et setters
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

    public function getBooking(): ?Booking
    {
        return $this->booking;
    }

    public function setBooking(?Booking $booking): self
    {
        $this->booking = $booking;
        return $this;
    }

    public function getUserAccount(): ?UserAccount
    {
        return $this->userAccount;
    }

    public function setUserAccount(UserAccount $userAccount): self
    {
        $this->userAccount = $userAccount;
        return $this;
    }

    public function getResource(): ?Resource
    {
        return $this->resource;
    }

    public function setResource(?Resource $resource): self
    {
        $this->resource = $resource;
        return $this;
    }

    public function getTimeslot(): ?Timeslot
    {
        return $this->timeslot;
    }

    public function setTimeslot(?Timeslot $timeslot): self
    {
        $this->timeslot = $timeslot;
        return $this;
    }

}

