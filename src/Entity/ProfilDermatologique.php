<?php
// src/Entity/ProfilDermatologique.php

namespace App\Entity;

use App\Repository\ProfilDermatologiqueRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\User;

#[ORM\Entity(repositoryClass: ProfilDermatologiqueRepository::class)]
class ProfilDermatologique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['profil:read', 'profil:write'])]
    private ?int $id = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['profil:read', 'profil:write'])]
    private ?array $profileData = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'profilDermatologiques')]
    #[Groups(['profil:read'])]
    private ?User $User = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfileData(): ?array
    {
        return $this->profileData;
    }

    public function setProfileData(?array $profileData): self
    {
        $this->profileData = $profileData;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }
}
