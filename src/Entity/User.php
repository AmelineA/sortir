<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="Ce mail est déjà enregistré!")
 * @UniqueEntity(fields={"username"}, message="Cet identifiant est déjà enregistré!")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\Length(
     *     min="3",
     *     max="180",
     *     minMessage="3 caractères minimum svp !",
     *     maxMessage="180 caractères maximum svp !"
     * )
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner le champ nom !")
     * @Assert\Length(
     *     min="3",
     *     max="30",
     *     minMessage="3 caractères minimum svp !",
     *     maxMessage="30 caractères maximum svp !"
     * )
     * @ORM\Column(type="string", length=30)
     */
    private $name;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner le champ prénom !")
     * @Assert\Length(
     *     min="3",
     *     max="30",
     *     minMessage="3 caractères minimum svp !",
     *     maxMessage="30 caractères maximum svp !"
     * )
     * @ORM\Column(type="string", length=30)
     */
    private $firstName;

    /**
     * @Assert\Length(
     *     min="10",
     *     max="10",
     *     minMessage="le champ doit contenir 10 chiffres",
     *     maxMessage="le champ doit contenir 10 chiffres",
     * )
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $telephone;

    /**
     * @Assert\NotBlank(message=" Veuillez renseigner le champ email !")
     * @Assert\Email(
     *     message = "adresse email invalide !",
     *     checkMX = true
     * )
     * @Assert\Length(
     *     max="50",
     *     maxMessage="50 caractères max !",
     * )
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="organizer")
     */
    private $organizedEvents;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $site;


    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Event", mappedBy="participants")
     */
    private $signedOnEvents;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\File(mimeTypes={"image/png" ,"image/jpg","image/jpeg"},
     *              mimeTypesMessage = "Svp inserer une image valide (png,jpg,jpeg)")
     */
    private $profilePictureName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetPassword;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $addedOn;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $promo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Location", mappedBy="creator")
     */
    private $locations;




    public function __construct()
    {
        $this->activated = true;
        $this->organizedEvents = new ArrayCollection();
        $this->signedOnEvents = new ArrayCollection();
        $this->locations = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }



    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }



    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }



    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return $roles;
    }


    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }


    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }



    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }



    public function getTelephone(): ?string
    {
        return $this->telephone;
    }



    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }



    public function getEmail(): ?string
    {
        return $this->email;
    }



    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }



    public function getActivated(): ?bool
    {
        return $this->activated;
    }



    public function setActivated(bool $activated): self
    {
        $this->activated = $activated;

        return $this;
    }


    /**
     * @return Collection|Event[]
     */
    public function getOrganizedEvents(): Collection
    {
        return $this->organizedEvents;
    }



    public function addOrganizedEvent(Event $organizedEvent): self
    {
        if (!$this->organizedEvents->contains($organizedEvent)) {
            $this->organizedEvents[] = $organizedEvent;
            $organizedEvent->setOrganizer($this);
        }

        return $this;
    }



    public function removeOrganizedEvent(Event $organizedEvent): self
    {
        if ($this->organizedEvents->contains($organizedEvent)) {
            $this->organizedEvents->removeElement($organizedEvent);
            // set the owning side to null (unless already changed)
            if ($organizedEvent->getOrganizer() === $this) {
                $organizedEvent->setOrganizer(null);
            }
        }

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
     * @return Collection|Event[]
     */
    public function getSignedOnEvents(): Collection
    {
        return $this->signedOnEvents;
    }



    public function addSignedOnEvent(Event $signedOnEvent): self
    {
        if (!$this->signedOnEvents->contains($signedOnEvent)) {
            $this->signedOnEvents[] = $signedOnEvent;
            $signedOnEvent->addParticipant($this);
        }

        return $this;
    }



    public function removeSignedOnEvent(Event $signedOnEvent): self
    {
        if ($this->signedOnEvents->contains($signedOnEvent)) {
            $this->signedOnEvents->removeElement($signedOnEvent);
            $signedOnEvent->removeParticipant($this);
        }

        return $this;
    }

    public function getProfilePictureName(): ?string
    {
        return $this->profilePictureName;
    }

    /**
     * @param UploadedFile|null $profilePictureName
     * @return User
     */
    public function setProfilePictureName($profilePictureName): self
    {
        $this->profilePictureName = $profilePictureName;

        return $this;
    }

    public function getResetPassword(): ?string
    {
        return $this->resetPassword;
    }

    public function setResetPassword(?string $resetPassword): self
    {
        $this->resetPassword = $resetPassword;

        return $this;
    }

    public function getAddedOn(): ?\DateTimeInterface
    {
        return $this->addedOn;
    }

    public function setAddedOn(?\DateTimeInterface $addedOn): self
    {
        $this->addedOn = $addedOn;

        return $this;
    }

    public function getPromo(): ?string
    {
        return $this->promo;
    }

    public function setPromo(?string $promo): self
    {
        $this->promo = $promo;

        return $this;
    }

    /**
     * @return Collection|Location[]
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): self
    {
        if (!$this->locations->contains($location)) {
            $this->locations[] = $location;
            $location->setCreator($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): self
    {
        if ($this->locations->contains($location)) {
            $this->locations->removeElement($location);
            // set the owning side to null (unless already changed)
            if ($location->getCreator() === $this) {
                $location->setCreator(null);
            }
        }

        return $this;
    }

}
