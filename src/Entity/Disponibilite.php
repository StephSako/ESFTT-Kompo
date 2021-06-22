<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DisponibiliteRepository")
 * @ORM\Table(
 *     name="prive_disponibilite",
 *     indexes={
 *         @Index(name="IDX_dispo_champ", columns={"id_championnat"}),
 *         @Index(name="IDX_dispo_c", columns={"id_journee"}),
 *         @Index(name="IDX_dispo_j", columns={"id_competiteur"})
 *     },
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_dispo", columns={"id_competiteur", "id_championnat", "id_journee"})
 *     }
 * )
 */
class Disponibilite
{

    /**
     * Disponibilite constructor.
     * @param Competiteur $competiteur
     * @param Journee $journee
     * @param bool $disponibilite
     * @param Championnat$type
     */
    public function __construct(Competiteur $competiteur, Journee $journee, bool $disponibilite, Championnat $type)
    {
        $this
            ->setIdCompetiteur($competiteur)
            ->setDisponibilite($disponibilite)
            ->setIdChampionnat($type)
            ->setIdJournee($journee);
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_disponibilite")
     */
    private $idDisponibilite;

    /**
     * @var Competiteur
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur", inversedBy="dispos")
     * @ORM\JoinColumn(name="id_competiteur", referencedColumnName="id_competiteur", nullable=false)
     */
    private $idCompetiteur;

    /**
     * @var Championnat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Championnat", inversedBy="dispos")
     * @ORM\JoinColumn(name="id_championnat", referencedColumnName="id_championnat", nullable=false)
     */
    private $idChampionnat;

    /**
     * @var Journee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Journee")
     * @ORM\JoinColumn(name="id_journee", referencedColumnName="id_journee", nullable=false)
     */
    private $idJournee;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="disponibilite", nullable=false)
     */
    private $disponibilite;

    /**
     * @return mixed
     */
    public function getIdDisponibilite()
    {
        return $this->idDisponibilite;
    }

    /**
     * @param mixed $idDisponibilite
     * @return Disponibilite
     */
    public function setIdDisponibilite($idDisponibilite): self
    {
        $this->idDisponibilite = $idDisponibilite;
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
     * @return Disponibilite
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
     * @return Disponibilite
     */
    public function setIdChampionnat(Championnat $idChampionnat): self
    {
        $this->idChampionnat = $idChampionnat;
        return $this;
    }

    /**
     * @return Journee
     */
    public function getIdJournee(): Journee
    {
        return $this->idJournee;
    }

    /**
     * @param Journee $idJournee
     * @return Disponibilite
     */
    public function setIdJournee(Journee $idJournee): self
    {
        $this->idJournee = $idJournee;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDisponibilite(): bool
    {
        return $this->disponibilite;
    }

    /**
     * @param bool $disponibilite
     * @return $this
     */
    public function setDisponibilite(bool $disponibilite): self
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }
}