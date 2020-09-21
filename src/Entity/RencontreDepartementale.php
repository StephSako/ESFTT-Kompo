<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RencontreDepartementaleRepository")
 * @ORM\Table(name="prive_rencontre_departementale")
 */
class RencontreDepartementale
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
     * @var boolean
     * @ORM\Column(name="exempt", type="boolean", nullable=false)
     */
    private $exempt;

    /**
     * @return bool
     */
    public function getdomicile(): bool
    {
        return $this->domicile;
    }

    /**
     * @param bool $domicile
     * @return RencontreDepartementale
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
     * @return RencontreDepartementale
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
     * @return RencontreDepartementale
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
     * @return RencontreDepartementale
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
     * @return RencontreDepartementale
     */
    public function setIdJoueur4(?Competiteur $idJoueur4): self
    {
        $this->idJoueur4 = $idJoueur4;
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
     * @return RencontreDepartementale
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
     * @return RencontreDepartementale
     */
    public function setIdJournee(JourneeDepartementale $idJournee): self
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
     * @return RencontreDepartementale
     */
    public function setAdversaire(?string $adversaire): self
    {
        $this->adversaire = $adversaire;
        return $this;
    }

    /**
     * @param EquipeDepartementale $idEquipe
     * @return RencontreDepartementale
     */
    public function setIdEquipe(EquipeDepartementale $idEquipe): RencontreDepartementale
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

    /**
     * @return bool
     */
    public function getIsEmpty() {
        return (!$this->getIdJoueur1() && !$this->getIdJoueur2() && !$this->getIdJoueur3() && !$this->getIdJoueur4());
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
     * @return RencontreDepartementale
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
     * @return RencontreDepartementale
     */
    public function setExempt(bool $exempt): self
    {
        $this->exempt = $exempt;
        return $this;
    }
}