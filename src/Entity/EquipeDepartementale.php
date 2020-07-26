<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EquipeDepartementaleRepository")
 * @ORM\Table(name="equipe_departementale")
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
     * @Assert\Length(
     *      max = 1,
     *      maxMessage = "Votre poule doit contenir au maximum {{ limit }} letttres"
     * )
     *
     * @ORM\Column(name="poule", type="string", length=1)
     */
    private $poule;

    /**
     * @Assert\Length(
     *      min = 1,
     *      max = 20,
     *      minMessage = "Votre division doit contenir au moins {{ limit }} letttres",
     *      maxMessage = "Votre division doit contenir au maximum {{ limit }} letttres"
     * )
     *
     * @ORM\Column(name="division", type="string", length=20)
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
     * @return EquipeDepartementale
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
     * @return EquipeDepartementale
     */
    public function setPoule($poule): self
    {
        $this->poule = $poule;
        return $this;
    }

    /**
     * @param mixed $division
     * @return EquipeDepartementale
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