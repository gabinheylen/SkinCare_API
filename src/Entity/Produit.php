<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Validator\ProductDetailsValidator;
use App\Validator\Constraints as CustomAssert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]

    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    #[ORM\Column(length: 255)]
    private ?string $Marque = null;

    #[ORM\Column(length: 255)]
    private ?string $Description = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $Images = [];

    #[ORM\ManyToMany(targetEntity: Ingredient::class, inversedBy: 'produits')]
    private Collection $Ingredients;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'produit')]
    private Collection $Note_user;

    #[ORM\Column(length: 255)]
    private ?string $Code = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[CustomAssert\ProduitDetailsConstraint]
    private ?array $details = null;

    /**
     * @var Collection<int, ProduitAimes>
     */
    #[ORM\OneToMany(targetEntity: ProduitAimes::class, mappedBy: 'produit')]
    private Collection $produitAimes;

    /**
     * @var Collection<int, MesProduits>
     */
    #[ORM\OneToMany(targetEntity: MesProduits::class, mappedBy: 'produit')]
    private Collection $mesProduits;

    public function __construct()
    {
        $this->Ingredients = new ArrayCollection();
        $this->Note_user = new ArrayCollection();
        $this->produitAimes = new ArrayCollection();
        $this->mesProduits = new ArrayCollection();
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

    public function getMarque(): ?string
    {
        return $this->Marque;
    }

    public function setMarque(string $Marque): static
    {
        $this->Marque = $Marque;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getImages(): array
    {
        return $this->Images;
    }

    public function setImages(array $Images): static
    {
        $this->Images = $Images;

        return $this;
    }

    /**
     * @return Collection<int, Ingredient>
     */
    public function getIngredients(): Collection
    {
        return $this->Ingredients;
    }

    public function addIngredient(Ingredient $ingredient): static
    {
        if (!$this->Ingredients->contains($ingredient)) {
            $this->Ingredients->add($ingredient);
        }

        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): static
    {
        $this->Ingredients->removeElement($ingredient);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getNoteUser(): Collection
    {
        return $this->Note_user;
    }

    public function addNoteUser(User $noteUser): static
    {
        if (!$this->Note_user->contains($noteUser)) {
            $this->Note_user->add($noteUser);
            $noteUser->setProduit($this);
        }

        return $this;
    }

    public function removeNoteUser(User $noteUser): static
    {
        if ($this->Note_user->removeElement($noteUser)) {
            // set the owning side to null (unless already changed)
            if ($noteUser->getProduit() === $this) {
                $noteUser->setProduit(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->Code;
    }

    public function setCode(string $Code): static
    {
        $this->Code = $Code;

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
            $produitAime->setProduit($this);
        }

        return $this;
    }

    public function removeProduitAime(ProduitAimes $produitAime): static
    {
        if ($this->produitAimes->removeElement($produitAime)) {
            // set the owning side to null (unless already changed)
            if ($produitAime->getProduit() === $this) {
                $produitAime->setProduit(null);
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
            $mesProduit->setProduit($this);
        }

        return $this;
    }

    public function removeMesProduit(MesProduits $mesProduit): static
    {
        if ($this->mesProduits->removeElement($mesProduit)) {
            // set the owning side to null (unless already changed)
            if ($mesProduit->getProduit() === $this) {
                $mesProduit->setProduit(null);
            }
        }

        return $this;
    }
    
    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function setDetails(?array $details): self
    {
        $this->details = $details;
        return $this;
    }
}