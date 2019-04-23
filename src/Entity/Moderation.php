<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ModerationRepository")
 */
class Moderation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $reporterId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $reportedUserId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $reporterName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $reportedUserName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReporterId(): ?int
    {
        return $this->reporterId;
    }

    public function setReporterId(?int $reporterId): self
    {
        $this->reporterId = $reporterId;

        return $this;
    }

    public function getReportedUserId(): ?int
    {
        return $this->reportedUserId;
    }

    public function setReportedUserId(?int $reportedUserId): self
    {
        $this->reportedUserId = $reportedUserId;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getReporterName(): ?string
    {
        return $this->reporterName;
    }

    public function setReporterName(string $reporterName): self
    {
        $this->reporterName = $reporterName;

        return $this;
    }

    public function getReportedUserName(): ?string
    {
        return $this->reportedUserName;
    }

    public function setReportedUserName(string $reportedUserName): self
    {
        $this->reportedUserName = $reportedUserName;

        return $this;
    }
}
