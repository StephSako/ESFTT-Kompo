<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EquipeParisRepository")
 * @ORM\Table(
 *     name="prive_equipe_paris",
 *     indexes={
 *         @Index(name="IDX_eq_par_id_p", columns={"id_poule"}),
 *         @Index(name="IDX_eq_par_id_d", columns={"id_division"})
 *     },
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_eq_par_num", columns={"numero"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"numero"}
 * )
 */
class EquipeParis
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
     * @ORM\Column(type="integer", name="numero", nullable=false)
     */
    private $numero;

    /**
     * @var Division|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Division", inversedBy="equipesParis")
     * @ORM\JoinColumn(name="id_division", nullable=true, referencedColumnName="id_division")
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
     * @ORM\OneToMany(targetEntity="App\Entity\RencontreParis", mappedBy="idEquipe", cascade={"remove"}, orphanRemoval=true)
     */
    private $rencontresParis;

    /**
     * @return mixed
     */
    public function getIdEquipe()
    {
        return $this->idEquipe;
    }

    /**
     * @param mixed $idEquipe
     * @return EquipeParis
     */
    public function setIdEquipe($idEquipe): self
    {
        $this->idEquipe = $idEquipe;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRencontresParis()
    {
        return $this->rencontresParis;
    }

    /**
     * @param mixed $rencontresParis
     * @return EquipeParis
     */
    public function setRencontresParis($rencontresParis): self
    {
        $this->rencontresParis = $rencontresParis;
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
     * @return EquipeParis
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
     * @return EquipeParis
     */
    public function setIdPoule(?Poule $idPoule): self
    {
        $this->idPoule = $idPoule;
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
     * @return EquipeParis
     */
    public function setNumero(int $numero): self
    {
        $this->numero = $numero;
        return $this;
    }
}