<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EquipeParisRepository")
 * @ORM\Table(name="prive_equipe_paris")
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
    public function setIdEquipe($idEquipe): EquipeParis
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
     * @return EquipeParis
     */
    public function setDivision($division): EquipeParis
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
     * @return EquipeParis
     */
    public function setPoule($poule): EquipeParis
    {
        $this->poule = $poule;
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
    public function setRencontresParis($rencontresParis)
    {
        $this->rencontresParis = $rencontresParis;
        return $this;
    }
}