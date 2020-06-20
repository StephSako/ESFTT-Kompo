<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FirstPhaseRepository")
 * @ORM\Table(name="phase_1")
 */
class FirstPhase
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Journee", cascade={"persist"})
     * @ORM\JoinColumn(name="id_journee", referencedColumnName="id_journee")
     * @var Journee
     */
    private $idJournee;

    /**
     * @ORM\Column(type="integer", name="id_equipe")
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
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur", cascade={"persist"})
     * @ORM\JoinColumn(name="id_capitaine", referencedColumnName="id_competiteur")
     * @var Competiteur
     */
    private $idCapitaine;

    /**
     * @return int|null
     */
    public function getIdEquipe(): ?int
    {
        return $this->idEquipe;
    }

    /**
     * @param mixed $idEquipe
     */
    public function setIdEquipe($idEquipe): void
    {
        $this->idEquipe = $idEquipe;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur1(): ?Competiteur
    {
        return $this->idJoueur1;
    }

    /**
     * @param Competiteur $idJoueur1
     */
    public function setIdJoueur1(Competiteur $idJoueur1): void
    {
        $this->idJoueur1 = $idJoueur1;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur2(): ?Competiteur
    {
        return $this->idJoueur2;
    }

    /**
     * @param Competiteur $idJoueur2
     */
    public function setIdJoueur2(Competiteur $idJoueur2): void
    {
        $this->idJoueur2 = $idJoueur2;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur3(): ?Competiteur
    {
        return $this->idJoueur3;
    }

    /**
     * @param Competiteur $idJoueur3
     */
    public function setIdJoueur3(Competiteur $idJoueur3): void
    {
        $this->idJoueur3 = $idJoueur3;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur4(): ?Competiteur
    {
        return $this->idJoueur4;
    }

    /**
     * @param Competiteur $idJoueur4
     */
    public function setIdJoueur4(Competiteur $idJoueur4): void
    {
        $this->idJoueur4 = $idJoueur4;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdCapitaine(): ?Competiteur
    {
        return $this->idCapitaine;
    }

    /**
     * @param Competiteur $idCapitaine
     */
    public function setIdCapitaine(Competiteur $idCapitaine): void
    {
        $this->idCapitaine = $idCapitaine;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
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
     */
    public function setIdJournee(Journee $idJournee): void
    {
        $this->idJournee = $idJournee;
    }

}