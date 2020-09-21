<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RencontreParisRepository")
 * @ORM\Table(name="prive_rencontre_paris")
 */
class RencontreParis
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
     * @var boolean
     * @ORM\Column(name="exempt", type="boolean", nullable=false)
     */
    private $exempt;

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
     * @var boolean
     * @ORM\Column(name="domicile", type="boolean", nullable=false)
     */
    private $domicile;

    /**
     * @var boolean
     * @ORM\Column(name="hosted", type="boolean", nullable=false)
     */
    private $hosted;

    /**
     * @var String
     *
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "L'adversaire doit contenir au maximum {{ limit }} caractères"
     * )
     *
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
     * @return RencontreParis
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
     * @return RencontreParis
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
     * @return RencontreParis
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
     * @return RencontreParis
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
     * @return RencontreParis
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
     * @return RencontreParis
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
     * @return RencontreParis
     */
    public function setIdJoueur6(?Competiteur $idJoueur6): self
    {
        $this->idJoueur6 = $idJoueur6;
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
     * @return RencontreParis
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
     * @return RencontreParis
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
     * @return RencontreParis
     */
    public function setIdJoueur9(?Competiteur $idJoueur9): self
    {
        $this->idJoueur9 = $idJoueur9;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return RencontreParis
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
     * @return RencontreParis
     */
    public function setIdJournee(JourneeParis $idJournee): self
    {
        $this->idJournee = $idJournee;
        return $this;
    }

    /**
     * @return String|null
     */
    public function getAdversaire(): ?string
    {
        return $this->adversaire;
    }

    /**
     * @param String|null $adversaire
     * @return RencontreParis
     */
    public function setAdversaire(?string $adversaire): self
    {
        $this->adversaire = $adversaire;
        return $this;
    }

    /**
     * @param EquipeParis $idEquipe
     * @return RencontreParis
     */
    public function setIdEquipe(EquipeParis $idEquipe): RencontreParis
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

    /**
     * @return bool
     */
    public function getIsEmptyHaut() {
        return (!$this->getIdJoueur1() && !$this->getIdJoueur2() && !$this->getIdJoueur3() && !$this->getIdJoueur4() && !$this->getIdJoueur5() && !$this->getIdJoueur6() && !$this->getIdJoueur7() && !$this->getIdJoueur8() && !$this->getIdJoueur9());
    }

    /**
     * @return bool
     */
    public function getIsEmptyBas() {
        return (!$this->getIdJoueur1() && !$this->getIdJoueur2() && !$this->getIdJoueur3());
    }

    /**
     * @return bool
     */
    public function isHosted(): bool
    {
        return $this->hosted;
    }

    /**
     * @param bool $hosted
     * @return RencontreParis
     */
    public function setHosted(bool $hosted): self
    {
        $this->hosted = $hosted;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExempt(): bool
    {
        return $this->exempt;
    }

    /**
     * @param bool $exempt
     * @return RencontreParis
     */
    public function setExempt(bool $exempt): self
    {
        $this->exempt = $exempt;
        return $this;
    }
}