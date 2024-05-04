<?php

namespace App\Entity;

use App\Repository\ProfilDermatologiqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfilDermatologiqueRepository::class)]
class ProfilDermatologique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $Type_de_peau = [];

    #[ORM\Column(type: Types::ARRAY)]
    private array $Sensibilite = [];

    #[ORM\Column(type: Types::ARRAY)]
    private array $Autre = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeDePeau(): array
    {
        return $this->Type_de_peau;
    }

    public function setTypeDePeau(array $Type_de_peau): static
    {
        $this->Type_de_peau = $Type_de_peau;

        return $this;
    }

    public function getSensibilite(): array
    {
        return $this->Sensibilite;
    }

    public function setSensibilite(array $Sensibilite): static
    {
        $this->Sensibilite = $Sensibilite;

        return $this;
    }

    public function getAutre(): array
    {
        return $this->Autre;
    }

    public function setAutre(array $Autre): static
    {
        $this->Autre = $Autre;

        return $this;
    }
}
