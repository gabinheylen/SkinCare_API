<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['user:read', 'user:write', 'profil:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups(['user:read', 'user:write', 'profil:read'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    #[ORM\Column(length: 255)]
    private ?string $Prenom = null;

    #[ORM\Column]
    private ?int $Age = null;

    #[ORM\Column(length: 255)]
    private ?string $Sexe = null;

    #[ORM\Column(length: 255)]
    private ?string $Preferences = null;

    #[ORM\OneToMany(targetEntity: NoteProduit::class, mappedBy: 'user')]
    private Collection $Notes_produits;

    #[ORM\ManyToOne(inversedBy: 'Note_user')]
    private ?Produit $produit = null;

    /**
     * @var Collection<int, ProduitAimes>
     */
    #[ORM\OneToMany(targetEntity: ProduitAimes::class, mappedBy: 'user')]
    private Collection $produitAimes;

    /**
     * @var Collection<int, MesProduits>
     */
    #[ORM\OneToMany(targetEntity: MesProduits::class, mappedBy: 'user')]
    private Collection $mesProduits;

    /**
     * @var Collection<int, ProfilDermatologique>
     */
    #[ORM\OneToMany(targetEntity: ProfilDermatologique::class, mappedBy: 'user')]
    #[Groups(['user'])]
    private Collection $profilDermatologiques;

    public function __construct()
    {
        $this->produitAimes = new ArrayCollection();
        $this->mesProduits = new ArrayCollection();
        $this->profilDermatologiques = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->Prenom;
    }

    public function setPrenom(string $Prenom): static
    {
        $this->Prenom = $Prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
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

    public function getAge(): ?int
    {
        return $this->Age;
    }

    public function setAge(int $Age): static
    {
        $this->Age = $Age;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->Sexe;
    }

    public function setSexe(string $Sexe): static
    {
        $this->Sexe = $Sexe;

        return $this;
    }

    public function getPreferences(): ?string
    {
        return $this->Preferences;
    }

    public function setPreferences(string $Preferences): static
    {
        $this->Preferences = $Preferences;

        return $this;
    }

    /**
     * @return Collection<int, NoteProduit>
     */
    public function getNotesProduits(): Collection
    {
        return $this->Notes_produits;
    }

    public function addNotesProduit(NoteProduit $notesProduit): static
    {
        if (!$this->Notes_produits->contains($notesProduit)) {
            $this->Notes_produits->add($notesProduit);
            $notesProduit->setUser($this);
        }

        return $this;
    }

    public function removeNotesProduit(NoteProduit $notesProduit): static
    {
        if ($this->Notes_produits->removeElement($notesProduit)) {
            // set the owning side to null (unless already changed)
            if ($notesProduit->getUser() === $this) {
                $notesProduit->setUser(null);
            }
        }

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }

    /**
     * @return Collection<int, ProduitAimes>
     */
    public function getProduitAimes(): Collection
    {
        return $this->produitAimes;
    }

    public function addProduitAime(ProduitAimes $produitAime): static
    {
        if (!$this->produitAimes->contains($produitAime)) {
            $this->produitAimes->add($produitAime);
            $produitAime->setUser($this);
        }

        return $this;
    }

    public function removeProduitAime(ProduitAimes $produitAime): static
    {
        if ($this->produitAimes->removeElement($produitAime)) {
            // set the owning side to null (unless already changed)
            if ($produitAime->getUser() === $this) {
                $produitAime->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MesProduits>
     */
    public function getMesProduits(): Collection
    {
        return $this->mesProduits;
    }

    public function addMesProduit(MesProduits $mesProduit): static
    {
        if (!$this->mesProduits->contains($mesProduit)) {
            $this->mesProduits->add($mesProduit);
            $mesProduit->setUser($this);
        }

        return $this;
    }

    public function removeMesProduit(MesProduits $mesProduit): static
    {
        if ($this->mesProduits->removeElement($mesProduit)) {
            // set the owning side to null (unless already changed)
            if ($mesProduit->getUser() === $this) {
                $mesProduit->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProfilDermatologique>
     */
    public function getProfilDermatologiques(): Collection
    {
        return $this->profilDermatologiques;
    }

    public function addProfilDermatologique(ProfilDermatologique $profilDermatologique): static
    {
        if (!$this->profilDermatologiques->contains($profilDermatologique)) {
            $this->profilDermatologiques->add($profilDermatologique);
            $profilDermatologique->setUser($this);
        }

        return $this;
    }

    public function removeProfilDermatologique(ProfilDermatologique $profilDermatologique): static
    {
        if ($this->profilDermatologiques->removeElement($profilDermatologique)) {
            // set the owning side to null (unless already changed)
            if ($profilDermatologique->getUser() === $this) {
                $profilDermatologique->setUser(null);
            }
        }

        return $this;
    }
}