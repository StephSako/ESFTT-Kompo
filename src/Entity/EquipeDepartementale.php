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
     * @ORM\ManyToOne(targetEntity="App\Entity\Division", cascade={"persist"})
     * @ORM\JoinColumn(name="id_division", referencedColumnName="id")
     */
    private $division;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Poule", cascade={"persist"})
     * @ORM\JoinColumn(name="id_poule", referencedColumnName="id")
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
    public function setIdEquipe($idEquipe): EquipeDepartementale
    {
        $this->idEquipe = $idEquipe;
        return $this;
    }

    /**
     * @return Division
     */
    public function getDivision(): Division
    {
        return $this->division;
    }

    /**
     * @param mixed $division
     * @return EquipeDepartementale
     */
    public function setDivision($division): EquipeDepartementale
    {
        $this->division = $division;
        return $this;
    }

    /**
     * @return Poule
     */
    public function getPoule(): Poule
    {
        return $this->poule;
    }

    /**
     * @param mixed $poule
     * @return EquipeDepartementale
     */
    public function setPoule($poule): EquipeDepartementale
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
    public function setRencontresDepartementales($rencontresDepartementales)
    {
        $this->rencontresDepartementales = $rencontresDepartementales;
        return $this;
    }
}