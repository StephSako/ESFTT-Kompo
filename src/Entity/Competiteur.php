<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CompetiteurRepository::class)
 * @ORM\Table(name="competiteur")
 */
class Competiteur
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_competiteur")
     */
    private $idCompetiteur;

    /**
     * @ORM\Column(name="license", type="integer")
     */
    private $license;

    /**
     * @ORM\Column(type="string", length=60, name="nom")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=1, name="role")
     */
    private $role;

    public function getIdCompetiteur(): ?int
    {
        return $this->idCompetiteur;
    }

    public function getLicense(): ?int
    {
        return $this->license;
    }

    public function setLicense(int $license): self
    {
        $this->license = $license;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }
}