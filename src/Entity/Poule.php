<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PouleRepository")
 * @ORM\Table(
 *     name="prive_poule",
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_poule", columns={"poule"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"poule"}
 * )
 */
class Poule
{
    /**
     * @var Equipe[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Equipe", mappedBy="idPoule")
     */
    protected $equipes;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_poule", nullable=false)
     */
    private $idPoule;
    /**
     * @var string
     *
     * @Assert\Length(
     *      min  = 1,
     *      max  = 1,
     *      exactMessage = "La poule doit contenir exactement {{ limit }} lettre"
     * )
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @ORM\Column(type="string", name="poule", nullable=false, length=1)
     */
    private $poule;

    /**
     * @return Equipe[]|null
     */
    public function getEquipes(): ?array
    {
        return $this->equipes;
    }

    /**
     * @param Equipe[]|null $equipes
     * @return Poule
     */
    public function setEquipes(?array $equipes): self
    {
        $this->equipes = $equipes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdPoule()
    {
        return $this->idPoule;
    }

    /**
     * @param mixed $idPoule
     * @return Poule
     */
    public function setIdPoule($idPoule): self
    {
        $this->idPoule = $idPoule;
        return $this;
    }

    /**
     * @return string
     */
    public function getPoule(): string
    {
        return $this->poule;
    }

    /**
     * @param string|null $poule
     * @return Poule
     */
    public function setPoule(?string $poule): self
    {
        $this->poule = $poule;
        return $this;
    }
}