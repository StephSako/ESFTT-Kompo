<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TitularisationRepository")
 * @ORM\Table(
 *     name="prive_titularisation",
 *     indexes={
 *         @Index(name="IDX_titu_champ", columns={"id_championnat"}),
 *         @Index(name="IDX_titu_c", columns={"id_competiteur"}),
 *         @Index(name="IDX_titu_e", columns={"id_equipe"})
 *     },
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_titu", columns={"id_competiteur", "id_championnat"})
 *     }
 * )
 */
class Titularisation
{

    /**
     * Titularisation constructor.
     * @param Competiteur $competiteur
     * @param Equipe $equipe
     * @param Championnat $type
     */
    public function __construct(Competiteur $competiteur, Equipe $equipe, Championnat $type)
    {
        $this
            ->setIdCompetiteur($competiteur)
            ->setIdChampionnat($type)
            ->setIdEquipe($equipe);
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_titularisation")
     */
    private $idTitularisation;

    /**
     * @var Competiteur
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur", inversedBy="titularisations", cascade={"persist"})
     * @ORM\JoinColumn(name="id_competiteur", referencedColumnName="id_competiteur", nullable=false)
     */
    private $idCompetiteur;

    /**
     * @var Championnat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Championnat", inversedBy="titularisations", cascade={"persist"})
     * @ORM\JoinColumn(name="id_championnat", referencedColumnName="id_championnat", nullable=false)
     */
    private $idChampionnat;

    /**
     * @var Equipe
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Equipe", inversedBy="titularisations", cascade={"persist"})
     * @ORM\JoinColumn(name="id_equipe", referencedColumnName="id_equipe", nullable=false)
     */
    private $idEquipe;

    /**
     * @return mixed
     */
    public function getIdTitularisation()
    {
        return $this->idTitularisation;
    }

    /**
     * @param mixed $idTitularisation
     * @return Titularisation
     */
    public function setIdTitularisation($idTitularisation): self
    {
        $this->idTitularisation = $idTitularisation;
        return $this;
    }

    /**
     * @return Competiteur
     */
    public function getIdCompetiteur(): Competiteur
    {
        return $this->idCompetiteur;
    }

    /**
     * @param Competiteur $idCompetiteur
     * @return Titularisation
     */
    public function setIdCompetiteur(Competiteur $idCompetiteur): self
    {
        $this->idCompetiteur = $idCompetiteur;
        return $this;
    }

    /**
     * @return Championnat
     */
    public function getIdChampionnat(): Championnat
    {
        return $this->idChampionnat;
    }

    /**
     * @param Championnat $idChampionnat
     * @return Titularisation
     */
    public function setIdChampionnat(Championnat $idChampionnat): self
    {
        $this->idChampionnat = $idChampionnat;
        return $this;
    }

    /**
     * @return Equipe
     */
    public function getIdEquipe(): Equipe
    {
        return $this->idEquipe;
    }

    /**
     * @param Equipe $idEquipe
     * @return Titularisation
     */
    public function setIdEquipe(Equipe $idEquipe): self
    {
        $this->idEquipe = $idEquipe;
        return $this;
    }
}