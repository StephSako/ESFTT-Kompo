<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DisponibiliteParisRepository")
 * @ORM\Table(name="disponibilite_paris")
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
    private $idCompetiteur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JourneeParis", cascade={"persist"})
     * @ORM\JoinColumn(name="id_journee", referencedColumnName="id_journee")
     * @var JourneeParis
     */
    private $idJournee;

    /**
     * @ORM\Column(type="boolean", name="disponibilite")
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
     */
    public function setIdDisponibilite($idDisponibilite): void
    {
        $this->idDisponibilite = $idDisponibilite;
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
     * @return bool|null
     */
    public function getDisponibilite(): ?bool
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