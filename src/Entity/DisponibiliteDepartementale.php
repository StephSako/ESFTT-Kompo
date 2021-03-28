<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DisponibiliteDepartementaleRepository")
 * @ORM\Table(
 *     name="prive_disponibilite_departementale",
 *     indexes={
 *         @Index(name="IDX_dispo_dep_id_j", columns={"id_journee"}),
 *         @Index(name="IDX_dispo_dep_id_c", columns={"id_competiteur"})
 * })
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
     * @var Competiteur
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur", inversedBy="disposDepartementale")
     * @ORM\JoinColumn(name="id_competiteur", referencedColumnName="id_competiteur", nullable=false)
     */
    private $idCompetiteur;

    /**
     * @var JourneeDepartementale
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\JourneeDepartementale")
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
     * @return DisponibiliteDepartementale
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
    public function setDisponibiliteDepartementale(bool $disponibilite): self
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }
}