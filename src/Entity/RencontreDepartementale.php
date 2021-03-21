<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RencontreDepartementaleRepository")
 * @ORM\Table(
 *     name="prive_rencontre_departementale",
 *     indexes={
 *         @Index(name="IDX_48DF62DE14128E72", columns={"id_joueur_4"}),
 *         @Index(name="IDX_48DF62DE27E0FF8", columns={"id_equipe"}),
 *         @Index(name="IDX_48DF62DE28A339D", columns={"id_journee"}),
 *         @Index(name="IDX_48DF62DE64787AFD", columns={"id_joueur_1"}),
 *         @Index(name="IDX_48DF62DE8A761BD1", columns={"id_joueur_3"}),
 *         @Index(name="IDX_48DF62DEFD712B47", columns={"id_joueur_2"})
 * })
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
     * @ORM\ManyToOne(targetEntity="App\Entity\JourneeDepartementale", inversedBy="rencontres")
     * @ORM\JoinColumn(name="id_journee", referencedColumnName="id_journee", nullable=false)
     * @var JourneeDepartementale
     */
    private $idJournee;

    /**
     * @var EquipeDepartementale
     * @ORM\ManyToOne(targetEntity="App\Entity\EquipeDepartementale", inversedBy="rencontresDepartementales")
     * @ORM\JoinColumn(name="id_equipe", referencedColumnName="id_equipe", nullable=false)
     */
    private $idEquipe;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_1", nullable=true, referencedColumnName="id_competiteur", unique=false, onDelete="SET NULL")
     * @var Competiteur|null
     */
    private $idJoueur1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_2", nullable=true, referencedColumnName="id_competiteur", unique=false, onDelete="SET NULL")
     * @var Competiteur|null
     */
    private $idJoueur2;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_3", nullable=true, referencedColumnName="id_competiteur", unique=false, onDelete="SET NULL")
     * @var Competiteur|null
     */
    private $idJoueur3;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_4", nullable=true, referencedColumnName="id_competiteur", unique=false, onDelete="SET NULL")
     * @var Competiteur|null
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
     * @var String|null
     *
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "L'adversaire doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @ORM\Column(name="adversaire", nullable=true, type="string", length=100)
     */
    private $adversaire;

    /**
     * @var boolean
     * @ORM\Column(name="exempt", type="boolean", nullable=false)
     */
    private $exempt;

    /**
     * Récupère le getter du joueur au placement dans la compo passé en paramètre
     * @param int $n
     * @return Competiteur|null
     */
    public function getIdJoueurN(int $n): ?Competiteur
    {
        if ($n == 1) return $this->getIdJoueur1();
        else if ($n == 2) return $this->getIdJoueur2();
        else if ($n == 3) return $this->getIdJoueur3();
        else if ($n == 4) return $this->getIdJoueur4();
        else return null;
    }

    /**
     * @param int $n
     * @param $val
     * @return RencontreDepartementale
     */
    public function setIdJoueurN(int $n, $val): self
    {
        if ($n == 1) return $this->setIdJoueur1($val);
        else if ($n == 2) return $this->setIdJoueur2($val);
        else if ($n == 3) return $this->setIdJoueur3($val);
        else if ($n == 4) return $this->setIdJoueur4($val);
        else return $this;
    }

    /**
     * @return bool
     */
    public function getDomicile(): bool
    {
        return $this->domicile;
    }

    /**
     * @param bool $domicile
     * @return RencontreDepartementale
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
    public function setIdEquipe(EquipeDepartementale $idEquipe): self
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
     * @param int $nbJoueurs
     * @return bool
     */
    public function getIsEmpty(int $nbJoueurs): bool
    {
        $isEmpty = array();
        for ($i = 1; $i <= $nbJoueurs; $i++){
            array_push($isEmpty, $this->getIdJoueurN($i));
        }
        return !in_array(true, $isEmpty);
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

    /**
     * @return bool
     */
    public function isReporte(): bool
    {
        return $this->reporte;
    }

    /**
     * @param bool $reporte
     * @return RencontreDepartementale
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
     * @return RencontreDepartementale
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
        return [$this->getIdJoueur1(), $this->getIdJoueur2(), $this->getIdJoueur3(), $this->getIdJoueur4()];
    }

    /**
     * Indique si la rencontre est entièrement composée
     * @return bool
     */
    public function isFull(): bool
    {
        return ($this->getIdJoueur1() && $this->getIdJoueur2() && $this->getIdJoueur3() && $this->getIdJoueur4());
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