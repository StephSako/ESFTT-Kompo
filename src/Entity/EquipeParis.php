<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EquipeParisRepository")
 * @ORM\Table(name="equipe_paris")
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
     * @ORM\Column(name="poule", type="integer", length=11)
     */
    private $poule;

    /**
     * @ORM\Column(name="division", type="string", length=15)
     */
    private $division;

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
    public function getPoule()
    {
        return $this->poule;
    }

    /**
     * @param mixed $poule
     * @return EquipeParis
     */
    public function setPoule($poule): self
    {
        $this->poule = $poule;
        return $this;
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
     * @return mixed
     */
    public function getDivision()
    {
        return $this->division;
    }


}