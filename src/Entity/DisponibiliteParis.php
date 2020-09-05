<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DisponibiliteParisRepository")
 * @ORM\Table(name="prive_disponibilite_paris")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur", inversedBy="disposParis")
     * @ORM\JoinColumn(name="id_competiteur", referencedColumnName="id_competiteur")
     */
    private Competiteur $idCompetiteur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JourneeParis", cascade={"persist"})
     * @ORM\JoinColumn(name="id_journee", referencedColumnName="id_journee")
     * @var JourneeParis
     */
    private JourneeParis $idJournee;

    /**
     * @ORM\Column(type="boolean", name="disponibilite", nullable=false)
     */
    private bool $disponibilite;

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