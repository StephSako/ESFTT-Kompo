<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DisponibiliteDepartementaleRepository")
 * @ORM\Table(name="disponibilite_departementale")
 */
class DisponibiliteDepartementale
{
    public function __construct(Competiteur $competiteur, $idJournee, bool $disponibilite)
    {
        $this
            ->setIdCompetiteur($competiteur)
            ->setDisponibiliteDepartementale($disponibilite)
            ->setIdJournee($idJournee);
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_disponibilite")
     */
    private $idDisponibilite;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur", inversedBy="disposDepartementales")
     * @ORM\JoinColumn(name="id_competiteur", referencedColumnName="id_competiteur")
     */
    private $idCompetiteur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JourneeDepartementale", cascade={"persist"})
     * @ORM\JoinColumn(name="id_journee", referencedColumnName="id_journee")
     * @var JourneeDepartementale
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
     * @return DisponibiliteDepartementale
     */
    public function setIdCompetiteur(Competiteur $idCompetiteur): self
    {
        $this->idCompetiteur = $idCompetiteur;
        return $this;
    }

    /**
     * @return JourneeDepartementale
     */
    public function getIdJournee(): JourneeDepartementale
    {
        return $this->idJournee;
    }

    /**
     * @param JourneeDepartementale $idJournee
     * @return DisponibiliteDepartementale
     */
    public function setIdJournee(JourneeDepartementale $idJournee): self
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
    public function setDisponibiliteDepartementale(bool $disponibilite): self
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }
}