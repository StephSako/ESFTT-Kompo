<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Cocur\Slugify\Slugify;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChampionnatRepository")
 * @ORM\Table(
 *     name="prive_championnat",
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_champ_nom", columns={"nom"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"nom"}
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
     * @return string
     */
    public function getSlug(): string
    {
        return (new Slugify())->slugify($this->nom);
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
        return $this->j2Rule;
    }

    /**
     * @param bool $j2Rule
     * @return Championnat
     */
    public function setJ2Rule(bool $j2Rule): self
    {
        $this->j2Rule = $j2Rule;
        return $this;
    }
}