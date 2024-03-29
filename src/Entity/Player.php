<?php

namespace App\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PlayerRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Player implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true, nullable : true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column] //Defaulting to 0
    private ?int $gold = 0;

    #[ORM\Column]
    private ?int $stage = 0;
    
    #[ORM\Column(name: 'deletedAt', type: Types::DATETIME_MUTABLE, nullable: true)]
    private $deletedAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $registerDate;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: HaveItem::class)]
    private Collection $have_item;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: DemonPlayer::class)]
    private Collection $Demon_Player;

    #[ORM\Column(length: 100, nullable : true)]
    private ?string $email = null;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;
    
    #[ORM\ManyToMany(targetEntity: Suggestion::class, inversedBy: 'playersLikes')]
    #[ORM\JoinTable(name: "player_likes")]
    private Collection $likes;

    #[ORM\OneToMany(mappedBy: 'playerSuggestion', targetEntity: Suggestion::class)]
    private Collection $suggestions;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->have_item = new ArrayCollection();
        $this->Demon_Player = new ArrayCollection();
        $this->registerDate = new DateTime();
        $this->likes = new ArrayCollection();
        $this->suggestions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getDisplayUsername(): string
    {
        if ($this->username == null) return "Deleted user";
        return $this->getUsername();
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTime $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role)
    {
        $roles = $this->getRoles();
        array_push($roles, $role);
        $this->setRoles($roles);
        return $this;
    }

    public function removeRole(string $role)
    {
        $roles = $this->getRoles();
        if (($key = array_search($role, $roles)) !== false) 
        {
            unset($roles[$key]);
        }
        $this->setRoles($roles);
        return $this;
    }


    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getGold(): ?int
    {
        return $this->gold;
    }

    public function setGold(int $gold): static
    {
        $this->gold = $gold;

        return $this;
    }

    public function getStage(): ?int
    {
        return $this->stage;
    }

    public function setStage(int $stage): static
    {
        $this->stage = $stage;

        return $this;
    }

    public function getRegisterDate(): ?\DateTimeInterface
    {
        return $this->registerDate;
    }

    public function setRegisterDate(\DateTimeInterface $registerDate): static
    {
        $this->registerDate = $registerDate;

        return $this;
    }

    /**
     * @return Collection<int, HaveItem>
     */
    public function getHaveItem(): Collection
    {
        return $this->have_item;
    }

    public function addHaveItem(HaveItem $haveItem): static
    {
        if (!$this->have_item->contains($haveItem)) {
            $this->have_item->add($haveItem);
            $haveItem->setPlayer($this);
        }

        return $this;
    }

    public function removeHaveItem(HaveItem $haveItem): static
    {
        if ($this->have_item->removeElement($haveItem)) {
            // set the owning side to null (unless already changed)
            if ($haveItem->getPlayer() === $this) {
                $haveItem->setPlayer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DemonPlayer>
     */
    public function getDemonPlayer(): Collection
    {
        return $this->Demon_Player;
    }

    public function addDemonPlayer(DemonPlayer $demonPlayer): static
    {
        if (!$this->Demon_Player->contains($demonPlayer)) {
            $this->Demon_Player->add($demonPlayer);
            $demonPlayer->setPlayer($this);
        }

        return $this;
    }

    public function removeDemonPlayer(DemonPlayer $demonPlayer): static
    {
        if ($this->Demon_Player->removeElement($demonPlayer)) {
            // set the owning side to null (unless already changed)
            if ($demonPlayer->getPlayer() === $this) {
                $demonPlayer->setPlayer(null);
            }
        }

        return $this;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Suggestion>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Suggestion $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
        }

        return $this;
    }

    public function removeLike(Suggestion $like): static
    {
        $this->likes->removeElement($like);

        return $this;
    }

    /**
     * @return Collection<int, Suggestion>
     */
    public function getSuggestions(): Collection
    {
        return $this->suggestions;
    }

    public function addSuggestion(Suggestion $suggestion): static
    {
        if (!$this->suggestions->contains($suggestion)) {
            $this->suggestions->add($suggestion);
            $suggestion->setPlayerSuggestion($this);
        }

        return $this;
    }

    public function removeSuggestion(Suggestion $suggestion): static
    {
        if ($this->suggestions->removeElement($suggestion)) {
            // set the owning side to null (unless already changed)
            if ($suggestion->getPlayerSuggestion() === $this) {
                $suggestion->setPlayerSuggestion(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}
