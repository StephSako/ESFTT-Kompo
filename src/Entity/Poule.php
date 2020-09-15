<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PouleRepository")
 * @ORM\Table(name="prive_poule")
 */
class Poule
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id", nullable=false)
     */
    private $idPoule;

    /**
     * @Assert\Length(
     *      min = 1,
     *      max = 1,
     *      minMessage = "La poule doit contenir exactement {{ limit }} lettre",
     *      maxMessage = "La poule doit contenir exactement {{ limit }} lettre"
     * )
     *
     * @ORM\Column(type="string", name="poule", nullable=false)
     */
    private $poule;

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
     * @return mixed
     */
    public function getPoule()
    {
        return $this->poule;
    }

    /**
     * @param mixed $poule
     * @return Poule
     */
    public function setPoule($poule): self
    {
        $this->poule = $poule;
        return $this;
    }
}