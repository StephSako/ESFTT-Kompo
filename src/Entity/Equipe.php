<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EquipeRepository")
 * @ORM\Table(
 *     name="prive_equipe",
 *     indexes={
 *         @Index(name="IDX_eq_div", columns={"id_division"}),
 *         @Index(name="IDX_eq_p", columns={"id_poule"}),
 *         @Index(name="IDX_eq_champ", columns={"id_championnat"})
 *     },
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_equipe", columns={"id_championnat", "numero"})
 *     }
 * )
 */
class Equipe
{

    public function __construct(Championnat $type)
    {
        $this->setIdChampionnat($type);
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_equipe")
     */
    private $idEquipe;

    /**
     * @var int
     *
     * @Assert\GreaterThanOrEqual(
     *     value = 1,
     *     message = "Le numéro d'équipe doit être supérieur à {{ value }}"
     * )
     *
     * @Assert\LessThanOrEqual(
     *     value = 100,
     *     message = "Le numéro d'équipe doit être inférieur à {{ value }}"
     * )
     *
     * @ORM\Column(type="integer", name="numero", nullable=false)
     */
    private $numero;

    /**
     * @var Division|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Division", inversedBy="equipes")
     * @ORM\JoinColumn(name="id_division", nullable=true, referencedColumnName="id_division")
     */
    private $idDivision;

    /**
     * @var Championnat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Championnat", inversedBy="equipes")
     * @ORM\JoinColumn(name="id_championnat", referencedColumnName="id_championnat", nullable=false)
     */
    private $idChampionnat;

    /**
     * @var Poule|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Poule", inversedBy="equipes")
     * @ORM\JoinColumn(name="id_poule", nullable=true, referencedColumnName="id_poule")
     */
    private $idPoule;

    /**
     * @var Collection
     *
     * Liste des rencontres de l'équipe
     * @ORM\OneToMany(targetEntity="App\Entity\Rencontre", mappedBy="idEquipe", cascade={"remove"}, orphanRemoval=true)
     */
    private $rencontres;

    /**
     * @return Championnat
     */
    public function getIdChampionnat(): Championnat
    {
        return $this->idChampionnat;
    }

    /**
     * @param Championnat $idChampionnat
     * @return Equipe
     */
    public function setIdChampionnat(Championnat $idChampionnat): self
    {
        $this->idChampionnat = $idChampionnat;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdEquipe()
    {
        return $this->idEquipe;
    }

    /**
     * @param mixed $idEquipe
     * @return Equipe
     */
    public function setIdEquipe($idEquipe): self
    {
        $this->idEquipe = $idEquipe;
        return $this;
    }

    /**
     * @return Division|null
     */
    public function getIdDivision(): ?Division
    {
        return $this->idDivision;
    }

    /**
     * @param Division|null $idDivision
     * @return Equipe
     */
    public function setIdDivision(?Division $idDivision): self
    {
        $this->idDivision = $idDivision;
        return $this;
    }

    /**
     * @return Poule|null
     */
    public function getIdPoule(): ?Poule
    {
        return $this->idPoule;
    }

    /**
     * @param Poule|null $idPoule
     * @return Equipe
     */
    public function setIdPoule(?Poule $idPoule): self
    {
        $this->idPoule = $idPoule;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getRencontres(): Collection
    {
        return $this->rencontres;
    }

    /**
     * @param Collection $rencontres
     * @return Equipe
     */
    public function setRencontres(Collection $rencontres): self
    {
        $this->rencontres = $rencontres;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumero(): int
    {
        return $this->numero;
    }

    /**
     * @param int $numero
     * @return Equipe
     */
    public function setNumero(int $numero): self
    {
        $this->numero = $numero;
        return $this;
    }
}