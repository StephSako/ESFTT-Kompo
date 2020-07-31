<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PhaseDepartementaleRepository")
 * @ORM\Table(name="phase_departementale")
 */
class PhaseDepartementale
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JourneeDepartementale", cascade={"persist"})
     * @ORM\JoinColumn(name="id_journee", referencedColumnName="id_journee")
     * @var JourneeDepartementale
     */
    private $idJournee;

    /**
     * @var EquipeDepartementale
     * @ORM\OneToOne(targetEntity="App\Entity\EquipeDepartementale")
     * @ORM\JoinColumn(name="id_equipe", referencedColumnName="id_equipe")
     */
    private $idEquipe;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur", cascade={"persist"})
     * @ORM\JoinColumn(name="id_joueur_1", referencedColumnName="id_competiteur")
     * @var Competiteur
     */
    private $idJoueur1;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur", cascade={"persist"})
     * @ORM\JoinColumn(name="id_joueur_2", referencedColumnName="id_competiteur")
     * @var Competiteur
     */
    private $idJoueur2;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur", cascade={"persist"})
     * @ORM\JoinColumn(name="id_joueur_3", referencedColumnName="id_competiteur")
     * @var Competiteur
     */
    private $idJoueur3;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur", cascade={"persist"})
     * @ORM\JoinColumn(name="id_joueur_4", referencedColumnName="id_competiteur")
     * @var Competiteur
     */
    private $idJoueur4;

    /**
     * @var boolean
     * @ORM\Column(name="domicile", type="boolean")
     */
    private $domicile;

    /**
     * @var String
     * @ORM\Column(name="adversaire", type="string", length=100)
     */
    private $adversaire;

    /**
     * @return bool
     */
    public function getdomicile(): bool
    {
        return $this->domicile;
    }

    /**
     * @param bool $domicile
     * @return PhaseDepartementale
     */
    public function setdomicile(bool $domicile): self
    {
        $this->domicile = $domicile;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur1(): ?Competiteur
    {
        return $this->idJoueur1;
    }

    /**
     * @param Competiteur|null $idJoueur1
     * @return PhaseDepartementale
     */
    public function setIdJoueur1(?Competiteur $idJoueur1): self
    {
        $this->idJoueur1 = $idJoueur1;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur2(): ?Competiteur
    {
        return $this->idJoueur2;
    }

    /**
     * @param Competiteur|null $idJoueur2
     * @return PhaseDepartementale
     */
    public function setIdJoueur2(?Competiteur $idJoueur2): self
    {
        $this->idJoueur2 = $idJoueur2;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur3(): ?Competiteur
    {
        return $this->idJoueur3;
    }

    /**
     * @param Competiteur|null $idJoueur3
     * @return PhaseDepartementale
     */
    public function setIdJoueur3(?Competiteur $idJoueur3): self
    {
        $this->idJoueur3 = $idJoueur3;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur4(): ?Competiteur
    {
        return $this->idJoueur4;
    }

    /**
     * @param Competiteur|null $idJoueur4
     * @return PhaseDepartementale
     */
    public function setIdJoueur4(?Competiteur $idJoueur4): self
    {
        $this->idJoueur4 = $idJoueur4;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return PhaseDepartementale
     */
    public function setId($id): self
    {
        $this->id = $id;
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
     * @return PhaseDepartementale
     */
    public function setIdJournee(JourneeDepartementale $idJournee): self
    {
        $this->idJournee = $idJournee;
        return $this;
    }

    /**
     * @return String
     */
    public function getAdversaire(): string
    {
        return $this->adversaire;
    }

    /**
     * @param String $adversaire
     * @return PhaseDepartementale
     */
    public function setAdversaire(string $adversaire): self
    {
        $this->adversaire = $adversaire;
        return $this;
    }

    /**
     * @param EquipeDepartementale $idEquipe
     * @return PhaseDepartementale
     */
    public function setIdEquipe(EquipeDepartementale $idEquipe): PhaseDepartementale
    {
        $this->idEquipe = $idEquipe;
        return $this;
    }

    /**
     * @return EquipeDepartementale
     */
    public function getIdEquipe(): EquipeDepartementale
    {
        return $this->idEquipe;
    }
}