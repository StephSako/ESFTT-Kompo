<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EquipeDepartementaleRepository")
 * @ORM\Table(
 *     name="prive_equipe_departementale",
 *     indexes={
 *         @Index(name="IDX_C1F64C55149AAA70", columns={"id_poule"}),
 *         @Index(name="IDX_C1F64C5540CCAB81", columns={"id_division"})
 *     },
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_C1F64C55F55AE19E", columns={"numero"})
 *     })
 */
class EquipeDepartementale
{
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
     * @ORM\Column(type="integer", name="numero", nullable=false, unique=true)
     */
    private $numero;

    /**
     * @var Division|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Division", inversedBy="equipesDepartementales")
     * @ORM\JoinColumn(name="id_division", nullable=true, referencedColumnName="id_division", onDelete="SET NULL")
     */
    private $idDivision;

    /**
     * @var Poule|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Poule", cascade={"persist"})
     * @ORM\JoinColumn(name="id_poule", nullable=true, referencedColumnName="id_poule")
     */
    private $idPoule;

    /**
     * Liste des rencontres de l'équipe
     * @ORM\OneToMany(targetEntity="App\Entity\RencontreDepartementale", mappedBy="idEquipe", cascade={"remove"}, orphanRemoval=true)
     */
    private $rencontresDepartementales;

    /**
     * @return mixed
     */
    public function getIdEquipe()
    {
        return $this->idEquipe;
    }

    /**
     * @param mixed $idEquipe
     * @return EquipeDepartementale
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
     * @return EquipeDepartementale
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
     * @return EquipeDepartementale
     */
    public function setIdPoule(?Poule $idPoule): self
    {
        $this->idPoule = $idPoule;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRencontresDepartementales()
    {
        return $this->rencontresDepartementales;
    }

    /**
     * @param mixed $rencontresDepartementales
     * @return EquipeDepartementale
     */
    public function setRencontresDepartementales($rencontresDepartementales): self
    {
        $this->rencontresDepartementales = $rencontresDepartementales;
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
     * @return EquipeDepartementale
     */
    public function setNumero(int $numero): self
    {
        $this->numero = $numero;
        return $this;
    }
}