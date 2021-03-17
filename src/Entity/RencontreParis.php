<?php

namespace App\Entity;

use DateTime;
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
     * @ORM\ManyToOne(targetEntity="App\Entity\JourneeParis")
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
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_1", nullable=true, referencedColumnName="id_competiteur")
     * @var Competiteur|null
     */
    private $idJoueur1;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_2", nullable=true, referencedColumnName="id_competiteur")
     * @var Competiteur|null
     */
    private $idJoueur2;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_3", nullable=true, referencedColumnName="id_competiteur")
     * @var Competiteur|null
     */
    private $idJoueur3;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_4", nullable=true, referencedColumnName="id_competiteur")
     * @var Competiteur|null
     */
    private $idJoueur4;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_5", nullable=true, referencedColumnName="id_competiteur")
     * @var Competiteur|null
     */
    private $idJoueur5;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_6", nullable=true, referencedColumnName="id_competiteur")
     * @var Competiteur|null
     */
    private $idJoueur6;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_7", nullable=true, referencedColumnName="id_competiteur")
     * @var Competiteur|null
     */
    private $idJoueur7;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_8", nullable=true, referencedColumnName="id_competiteur")
     * @var Competiteur|null
     */
    private $idJoueur8;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_9", nullable=true, referencedColumnName="id_competiteur")
     * @var Competiteur|null
     */
    private $idJoueur9;

    /**
     * @var EquipeParis
     * @ORM\ManyToOne(targetEntity="App\Entity\EquipeParis", inversedBy="rencontresParis")
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
     * @var boolean
     * @ORM\Column(name="reporte", type="boolean", nullable=false)
     */
    private $reporte;

    /**
     * @var DateTime
     * @ORM\Column(type="date", name="date_report", nullable=false)
     */
    private $dateReport;

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
    public function getDomicile(): bool
    {
        return $this->domicile;
    }

    /**
     * @param bool $domicile
     * @return RencontreParis
     */
    public function setDomicile(bool $domicile): self
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
    public function setIdEquipe(EquipeParis $idEquipe): self
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
    public function getIsEmptyHaut(): bool
    {
        return (!$this->getIdJoueur1() && !$this->getIdJoueur2() && !$this->getIdJoueur3() && !$this->getIdJoueur4() && !$this->getIdJoueur5() && !$this->getIdJoueur6() && !$this->getIdJoueur7() && !$this->getIdJoueur8() && !$this->getIdJoueur9());
    }

    /**
     * @return bool
     */
    public function getIsEmptyBas(): bool
    {
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

    /**
     * @return bool
     */
    public function isReporte(): bool
    {
        return $this->reporte;
    }

    /**
     * @param bool $reporte
     * @return RencontreParis
     */
    public function setReporte(bool $reporte): self
    {
        $this->reporte = $reporte;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateReport(): DateTime
    {
        return $this->dateReport;
    }

    /**
     * @param DateTime $dateReport
     * @return RencontreParis
     */
    public function setDateReport(DateTime $dateReport): self
    {
        $this->dateReport = $dateReport;
        return $this;
    }

    /**
     * Liste des joueurs sélectionnés dans une composition d'équipe
     * @return Competiteur[]|null[]
     */
    public function getListSelectedPlayers(){
        return [$this->getIdJoueur1(), $this->getIdJoueur2(), $this->getIdJoueur3(), $this->getIdJoueur4(), $this->getIdJoueur5(), $this->getIdJoueur6(), $this->getIdJoueur7(), $this->getIdJoueur8(), $this->getIdJoueur9()];
    }

    /**
     * Indique si la rencontre est entièrement composée
     * @return bool
     */
    public function isFull() {
        if ($this->idEquipe->getIdEquipe() == 1) return ($this->getIdJoueur1() && $this->getIdJoueur2() && $this->getIdJoueur3() && $this->getIdJoueur4() && $this->getIdJoueur5() && $this->getIdJoueur6() && $this->getIdJoueur7() && $this->getIdJoueur8() && $this->getIdJoueur9());
        else if ($this->idEquipe->getIdEquipe() == 2) return ($this->getIdJoueur1() && $this->getIdJoueur2() && $this->getIdJoueur3());
        else return false;
    }

    /**
     * Liste des numéros de téléphone des joueurs sélectionnés
     * @param int $idRedacteur
     * @return string
     */
    public function getListPhoneNumbersSelectedPlayers(int $idRedacteur): string
    {
        $phoneNumbers = [];

        foreach ($this->getListSelectedPlayers() as $joueur) {
            if ($joueur && $joueur->getIdCompetiteur() != $idRedacteur){
                if ($joueur->isContactablePhoneNumber() && $joueur->getPhoneNumber() && $joueur->getPhoneNumber() != "") array_push($phoneNumbers, $joueur->getPhoneNumber());
                if ($joueur->isContactablePhoneNumber2() && $joueur->getPhoneNumber2() && $joueur->getPhoneNumber2() != "") array_push($phoneNumbers, $joueur->getPhoneNumber2());
            }
        }

        return implode(",", $phoneNumbers);
    }
}