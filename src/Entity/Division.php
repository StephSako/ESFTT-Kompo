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
     * @ORM\Column(type="integer", name="id_division", nullable=false)
     */
    private $idDivision;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 2,
     *      minMessage = "Le diminitif de la division doit contenir au moins {{ limit }} lettres",
     *      maxMessage = "Le diminitif de la division doit contenir au maximum {{ limit }} lettres"
     * )
     *
     * @ORM\Column(type="string", name="short_name", nullable=false, length=2)
     */
    private $shortName;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 25,
     *      minMessage = "Le nom de la division doit contenir au moins {{ limit }} lettres",
     *      maxMessage = "Le nom de la division doit contenir au maximum {{ limit }} lettres"
     * )
     *
     * @ORM\Column(type="string", name="long_name", nullable=false, length=25)
     */
    private $longName;

    /**
     * @var int|null
     *
     * @Assert\LessThanOrEqual(
     *     value = 4,
     *     message = "Le nombre maximal de joueurs est {{ value }}"
     * )
     *
     * @ORM\Column(type="integer", name="nb_joueurs_champ_departementale", nullable=true)
     */
    private $nbJoueursChampDepartementale;

    /**
     * @var int|null
     *
     * @Assert\LessThanOrEqual(
     *     value = 9,
     *     message = "Le nombre maximal de joueurs est {{ value }}"
     * )
     *
     * @ORM\Column(type="integer", name="nb_joueurs_champ_paris", nullable=true)
     */
    private $nbJoueursChampParis;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EquipeDepartementale", mappedBy="idDivision")
     */
    protected $equipesDepartementales;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EquipeParis", mappedBy="idDivision")
     */
    protected $equipesParis;

    /**
     * @return mixed
     */
    public function getIdDivision()
    {
        return $this->idDivision;
    }

    /**
     * @return int|null
     */
    public function getNbJoueursChampDepartementale(): ?int
    {
        return $this->nbJoueursChampDepartementale;
    }

    /**
     * @param int|null $nbJoueursChampDepartementale
     * @return Division
     */
    public function setNbJoueursChampDepartementale(?int $nbJoueursChampDepartementale): self
    {
        $this->nbJoueursChampDepartementale = $nbJoueursChampDepartementale;
        return $this;
    }

    public function getNbJoueursChamp(string $type): ?int
    {
        return ($type == 'departementale' ? $this->nbJoueursChampDepartementale : $this->nbJoueursChampParis);
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
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     * @return Division
     */
    public function setShortName(string $shortName): self
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLongName(): string
    {
        return $this->longName;
    }

    /**
     * @param string $longName
     * @return Division
     */
    public function setLongName(string $longName): self
    {
        $this->longName = $longName;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNbJoueursChampParis(): ?int
    {
        return $this->nbJoueursChampParis;
    }

    /**
     * @param int|null $nbJoueursChampParis
     * @return Division
     */
    public function setNbJoueursChampParis(?int $nbJoueursChampParis): self
    {
        $this->nbJoueursChampParis = $nbJoueursChampParis;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEquipesDepartementales()
    {
        return $this->equipesDepartementales;
    }

    /**
     * @param mixed $equipesDepartementales
     * @return Division
     */
    public function setEquipesDepartementales($equipesDepartementales): self
    {
        $this->equipesDepartementales = $equipesDepartementales;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEquipesParis()
    {
        return $this->equipesParis;
    }

    /**
     * @param mixed $equipesParis
     * @return Division
     */
    public function setEquipesParis($equipesParis): self
    {
        $this->equipesParis = $equipesParis;
        return $this;
    }
}