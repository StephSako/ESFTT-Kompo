<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DisponibiliteParisRepository")
 * @ORM\Table(
 *     name="prive_disponibilite_paris",
 *     indexes={
 *         @Index(name="IDX_A343DC8A28A339D", columns={"id_journee"}),
 *         @Index(name="IDX_A343DC8A2EBEB6", columns={"id_competiteur"})
 * })
 */
class DisponibiliteParis
{
    public function __construct(Competiteur $competiteur, JourneeParis $idJournee, bool $disponibilite)
    {
        $this
            ->setIdCompetiteur($competiteur)
            ->setDisponibiliteParis($disponibilite)
            ->setIdJournee($idJournee);
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur", inversedBy="disposParis")
     * @ORM\JoinColumn(name="id_competiteur", referencedColumnName="id_competiteur", nullable=false)
     */
    private $idCompetiteur;

    /**
     * @var JourneeParis
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\JourneeParis",)
     * @ORM\JoinColumn(name="id_journee", referencedColumnName="id_journee", nullable=false)
     * @var JourneeParis
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
     * @return DisponibiliteParis
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
     * @return DisponibiliteParis
     */
    public function setIdCompetiteur(Competiteur $idCompetiteur): self
    {
        $this->idCompetiteur = $idCompetiteur;
        return $this;
    }

    /**
     * @return JourneeParis
     */
    public function getIdJournee(): JourneeParis
    {
        return $this->idJournee;
    }

    /**
     * @param JourneeParis $idJournee
     * @return DisponibiliteParis
     */
    public function setIdJournee(JourneeParis $idJournee): self
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
    public function setDisponibiliteParis(bool $disponibilite): self
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }
}