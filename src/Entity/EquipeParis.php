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
     * @var string
     * @ORM\Column(type="string", name="lien_division", nullable=false, length=50)
     */
    private $lienDivision;

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
    public function setIdEquipe($idEquipe)
    {
        $this->idEquipe = $idEquipe;
        return $this;
    }

    /**
     * @return Division
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * @param mixed $division
     * @return EquipeParis
     */
    public function setDivision($division)
    {
        $this->division = $division;
        return $this;
    }

    /**
     * @return Poule
     */
    public function getPoule()
    {
        return $this->poule;
    }

    /**
     * @param mixed $poule
     * @return EquipeParis
     */
    public function setPoule($poule)
    {
        $this->poule = $poule;
        return $this;
    }

    /**
     * @param mixed $lienDivision
     * @return EquipeParis
     */
    public function setLienDivision($lienDivision)
    {
        $this->lienDivision = $lienDivision;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLienDivision()
    {
        return $this->lienDivision;
    }
}