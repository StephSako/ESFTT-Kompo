<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChampionnatRepository")
 * @ORM\Table(
 *     name="prive_championnat",
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_champ_nom", columns={"nom"}),
 *          @UniqueConstraint(name="UNIQ_champ_slug", columns={"slug"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"nom", "slug"}
 * )
 */
class Championnat
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_championnat", nullable=false)
     */
    private $idChampionnat;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Le nom doit contenir au moins {{ limit }} caractères",
     *      maxMessage = "Le nom doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @ORM\Column(type="string", name="nom", nullable=false, length=50)
     */
    private $nom;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="j2rule", nullable=false)
     */
    private $j2Rule;

    /**
     * @return mixed
     */
    public function getIdChampionnat()
    {
        return $this->idChampionnat;
    }

    /**
     * @param mixed $idChampionnat
     * @return Championnat
     */
    public function setIdChampionnat($idChampionnat): self
    {
        $this->idChampionnat = $idChampionnat;
        return $this;
    }

    /**
     * @return string
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * @param string|null $nom
     * @return Championnat
     */
    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * @return bool
     */
    public function isJ2Rule(): bool
    {
        return $this->j2_rule;
    }

    /**
     * @param bool $j2_rule
     * @return Championnat
     */
    public function setJ2Rule(bool $j2_rule): self
    {
        $this->j2_rule = $j2_rule;
        return $this;
    }
}