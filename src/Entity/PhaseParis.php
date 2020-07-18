<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PhaseParisRepository")
 * @ORM\Table(name="phase_paris")
 */
class PhaseParis
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JourneeParis", cascade={"persist"})
     * @ORM\JoinColumn(name="id_journee", referencedColumnName="id_journee")
     * @var JourneeParis
     */
    private $idJournee;

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
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur", cascade={"persist"})
     * @ORM\JoinColumn(name="id_joueur_5", referencedColumnName="id_competiteur")
     * @var Competiteur
     */
    private $idJoueur5;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur", cascade={"persist"})
     * @ORM\JoinColumn(name="id_joueur_6", referencedColumnName="id_competiteur")
     * @var Competiteur
     */
    private $idJoueur6;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur", cascade={"persist"})
     * @ORM\JoinColumn(name="id_joueur_7", referencedColumnName="id_competiteur")
     * @var Competiteur
     */
    private $idJoueur7;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur", cascade={"persist"})
     * @ORM\JoinColumn(name="id_joueur_8", referencedColumnName="id_competiteur")
     * @var Competiteur
     */
    private $idJoueur8;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur", cascade={"persist"})
     * @ORM\JoinColumn(name="id_joueur_9", referencedColumnName="id_competiteur")
     * @var Competiteur
     */
    private $idJoueur9;

    /**
     * @var EquipeParis
     * @ORM\OneToOne(targetEntity="App\Entity\EquipeParis")
     * @ORM\JoinColumn(name="id_equipe", referencedColumnName="id_equipe")
     */
    private $idEquipe;

    /**
     * @var String
     * @ORM\Column(name="lieu", type="string", length=100)
     */
    private $lieu;

    /**
     * @var String
     * @ORM\Column(name="adversaire", type="string", length=100)
     */
    private $adversaire;

    /**
     * @return String|null
     */
    public function getLieu(): ?String
    {
        return $this->lieu;
    }

    /**
     * @param String $lieu
     */
    public function setLieu(String $lieu): void
    {
        $this->lieu = $lieu;
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
     * @return PhaseParis
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
     * @return PhaseParis
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
     * @return PhaseParis
     */
    public function setIdJoueur3(?Competiteur $idJoueur3): self
    {
        $this->idJoueur3 = $idJoueur3;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur7(): ?Competiteur
    {
        return $this->idJoueur7;
    }

    /**
     * @param Competiteur|null $idJoueur7
     * @return PhaseParis
     */
    public function setIdJoueur7(?Competiteur $idJoueur7): self
    {
        $this->idJoueur7 = $idJoueur7;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur8(): ?Competiteur
    {
        return $this->idJoueur8;
    }

    /**
     * @param Competiteur|null $idJoueur8
     * @return PhaseParis
     */
    public function setIdJoueur8(?Competiteur $idJoueur8): self
    {
        $this->idJoueur8 = $idJoueur8;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur9(): ?Competiteur
    {
        return $this->idJoueur9;
    }

    /**
     * @param Competiteur|null $idJoueur9
     * @return PhaseParis
     */
    public function setIdJoueur9(?Competiteur $idJoueur9): self
    {
        $this->idJoueur9 = $idJoueur9;
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
     * @return PhaseParis
     */
    public function setIdJoueur4(?Competiteur $idJoueur4): self
    {
        $this->idJoueur4 = $idJoueur4;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur5(): ?Competiteur
    {
        return $this->idJoueur5;
    }

    /**
     * @param Competiteur|null $idJoueur5
     * @return PhaseParis
     */
    public function setIdJoueur5(?Competiteur $idJoueur5): self
    {
        $this->idJoueur5 = $idJoueur5;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur6(): ?Competiteur
    {
        return $this->idJoueur6;
    }

    /**
     * @param Competiteur|null $idJoueur6
     * @return PhaseParis
     */
    public function setIdJoueur6(?Competiteur $idJoueur6): self
    {
        $this->idJoueur6 = $idJoueur6;
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
     * @return PhaseParis
     */
    public function setId($id): self
    {
        $this->id = $id;
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
     * @return PhaseParis
     */
    public function setIdJournee(JourneeParis $idJournee): self
    {
        $this->idJournee = $idJournee;
        return $this;
    }

    /**
     * @return string
     */
    public function getDivision(): string
    {
        if ($this->getIdEquipe() === 1) return "PR";
        else if ($this->getIdEquipe() === 2) return "D2";
        else return '';
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
     * @return PhaseParis
     */
    public function setAdversaire(string $adversaire): self
    {
        $this->adversaire = $adversaire;
        return $this;
    }

    /**
     * @param EquipeParis $idEquipe
     * @return PhaseParis
     */
    public function setIdEquipe(EquipeParis $idEquipe): PhaseParis
    {
        $this->idEquipe = $idEquipe;
        return $this;
    }

    /**
     * @return EquipeParis
     */
    public function getIdEquipe(): EquipeParis
    {
        return $this->idEquipe;
    }
}