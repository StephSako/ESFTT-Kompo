<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EquipeParisRepository")
 * @ORM\Table(
 *     name="prive_equipe_paris",
 *     indexes={
 *         @Index(name="IDX_4F1610B1149AAA70", columns={"id_poule"}),
 *         @Index(name="IDX_4F1610B140CCAB81", columns={"id_division"})
 * })
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
     * @var Division|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Division", inversedBy="equipesParis")
     * @ORM\JoinColumn(name="id_division", nullable=true, referencedColumnName="id_division", onDelete="SET NULL")
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
}