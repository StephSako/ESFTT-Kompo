<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EquipeDepartementaleRepository")
 * @ORM\Table(name="prive_equipe_departementale")
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
     * @var Division|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Division", cascade={"persist"})
     * @ORM\JoinColumn(name="id_division", nullable=true, referencedColumnName="id")
     */
    private $division;

    /**
     * @var Poule|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Poule", cascade={"persist"})
     * @ORM\JoinColumn(name="id_poule", nullable=true, referencedColumnName="id")
     */
    private $poule;

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
    public function getDivision(): ?Division
    {
        return $this->division;
    }

    /**
     * @param Division|null $division
     * @return EquipeDepartementale
     */
    public function setDivision(?Division $division): self
    {
        $this->division = $division;
        return $this;
    }

    /**
     * @return Poule|null
     */
    public function getPoule(): ?Poule
    {
        return $this->poule;
    }

    /**
     * @param Poule|null $poule
     * @return EquipeDepartementale
     */
    public function setPoule(?Poule $poule): self
    {
        $this->poule = $poule;
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
}