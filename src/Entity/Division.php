<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DivisionRepository")
 * @ORM\Table(name="prive_division")
 */
class Division
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id", nullable=false)
     */
    private $idDivision;

    /**
     * @Assert\Length(
     *      min = 1,
     *      max = 2,
     *      minMessage = "Le diminituif de la division doit contenir au moins {{ limit }} lettres",
     *      maxMessage = "Le diminituif de la division doit contenir au maximum {{ limit }} lettres"
     * )
     *
     * @ORM\Column(type="string", name="short_name", nullable=false)
     */
    private $shortName;

    /**
     * @Assert\Length(
     *      min = 5,
     *      max = 25,
     *      minMessage = "Le nom de la division doit contenir au moins {{ limit }} lettres",
     *      maxMessage = "Le nom de la division doit contenir au maximum {{ limit }} lettres"
     * )
     *
     * @ORM\Column(type="string", name="long_name", nullable=false)
     */
    private $longName;

    /**
     * @return mixed
     */
    public function getIdDivision()
    {
        return $this->idDivision;
    }

    /**
     * @param mixed $idDivision
     * @return Division
     */
    public function setIdDivision($idDivision): self
    {
        $this->idDivision = $idDivision;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param mixed $shortName
     * @return Division
     */
    public function setShortName($shortName): self
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLongName()
    {
        return $this->longName;
    }

    /**
     * @param mixed $longName
     * @return Division
     */
    public function setLongName($longName): self
    {
        $this->longName = $longName;
        return $this;
    }
}