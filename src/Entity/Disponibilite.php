<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DisponibiliteRepository")
 * @ORM\Table(name="disponibilite")
 */
class Disponibilite
{
    public function __construct(Competiteur $competiteur, Journee $idJournee, bool $disponibilite)
    {
        $this
            ->setIdCompetiteur($competiteur)
            ->setDisponibilite($disponibilite)
            ->setIdJournee($idJournee);
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_disponibilite")
     */
    private $idDisponibilite;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur", inversedBy="dispos")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_competiteur", referencedColumnName="id_competiteur")
     * })
     */
    private $idCompetiteur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Journee", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_journee", referencedColumnName="id_journee")
     * })
     * @var Journee
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
    public function setDisponibilite(bool $disponibilite): self
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }
}