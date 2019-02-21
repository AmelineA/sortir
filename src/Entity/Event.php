<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message= "Veuillez renseigner le champ nom !")
     * @Assert\Length(
     *     min="3",
     *     max="255",
     *     minMessage="3 caractères minimum svp !",
     *     maxMessage="255 caractères maximum svp !"
     * )
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Assert\NotBlank(message=" Veuillez donner un rendez-vous !")
     * @Assert\DateTime()
     * @ORM\Column(type="datetime")
     */
    private $rdvTime;

    /**
     * @Assert\NotBlank(message=" Veuillez préciser le temps de sortie !")
     * @ORM\Column(type="integer")
     */
    private $duration;

    /**
     * @Assert\NotBlank(message=" Veuillez préciser la limite d'inscription !")
     * @Assert\DateTime()
     * @ORM\Column(type="datetime")
     */
    private $signOnDeadline;

    /**
     * @Assert\NotBlank(message=" Veuillez préciser le nombre max de participants!")
     * @ORM\Column(type="integer")
     */
    private $maxNumber;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="organizedEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organizer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $site;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="signedOnEvents")
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="relation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $location;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRdvTime(): ?\DateTimeInterface
    {
        return $this->rdvTime;
    }

    public function setRdvTime(\DateTimeInterface $rdvTime): self
    {
        $this->rdvTime = $rdvTime;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getSignOnDeadline(): ?\DateTimeInterface
    {
        return $this->signOnDeadline;
    }

    public function setSignOnDeadline(\DateTimeInterface $signOnDeadline): self
    {
        $this->signOnDeadline = $signOnDeadline;

        return $this;
    }

    public function getMaxNumber(): ?int
    {
        return $this->maxNumber;
    }

    public function setMaxNumber(int $maxNumber): self
    {
        $this->maxNumber = $maxNumber;

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

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): self
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
        }

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }
}
