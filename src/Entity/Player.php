<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?int $gold = null;

    #[ORM\Column]
    private ?int $stage = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $registerDate = null;

    #[ORM\ManyToOne(inversedBy: 'PlayersSuggestions')]
    private ?Suggestion $send = null;

    #[ORM\ManyToMany(targetEntity: Suggestion::class, inversedBy: 'PlayersLikes')]
    private Collection $likes;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: HaveItem::class)]
    private Collection $have_item;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: DemonPlayer::class)]
    private Collection $Demon_Player;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->have_item = new ArrayCollection();
        $this->Demon_Player = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
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

    public function getSend(): ?Suggestion
    {
        return $this->send;
    }

    public function setSend(?Suggestion $send): static
    {
        $this->send = $send;

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
}
