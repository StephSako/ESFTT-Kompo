<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RencontreRepository")
 * @ORM\Table(
 *     name="prive_rencontre",
 *     indexes={
 *         @Index(name="IDX_renc_j0", columns={"id_joueur_0"}),
 *         @Index(name="IDX_renc_j1", columns={"id_joueur_1"}),
 *         @Index(name="IDX_renc_j2", columns={"id_joueur_2"}),
 *         @Index(name="IDX_renc_j3", columns={"id_joueur_3"}),
 *         @Index(name="IDX_renc_j4", columns={"id_joueur_4"}),
 *         @Index(name="IDX_renc_j5", columns={"id_joueur_5"}),
 *         @Index(name="IDX_renc_j6", columns={"id_joueur_6"}),
 *         @Index(name="IDX_renc_j7", columns={"id_joueur_7"}),
 *         @Index(name="IDX_renc_j8", columns={"id_joueur_8"}),
 *         @Index(name="IDX_renc_eq", columns={"id_equipe"}),
 *         @Index(name="IDX_renc_champ", columns={"id_championnat"}),
 *         @Index(name="IDX_renc_j", columns={"id_journee"})
 *     },
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_renc", columns={"id_equipe", "id_journee", "id_championnat"})
 *     }
 * )
 */
class Rencontre
{

    /**
     * Rencontre constructor.
     * @param Championnat $type
     */
    public function __construct(Championnat $type)
    {
        $this->setIdChampionnat($type);
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var Journee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Journee", inversedBy="rencontres")
     * @ORM\JoinColumn(name="id_journee", referencedColumnName="id_journee", nullable=false)
     */
    private $idJournee;

    /**
     * @var Championnat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Championnat", inversedBy="rencontres")
     * @ORM\JoinColumn(name="id_championnat", referencedColumnName="id_championnat", nullable=false)
     */
    private $idChampionnat;

    /**
     * @var boolean
     *
     * @ORM\Column(name="exempt", type="boolean", nullable=false)
     */
    private $exempt;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_0", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur0;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_1", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur1;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_2", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur2;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_3", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur3;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_4", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur4;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_5", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur5;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_6", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur6;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_7", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur7;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_8", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur8;

    /**
     * @var Equipe
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Equipe", inversedBy="rencontres")
     * @ORM\JoinColumn(name="id_equipe", referencedColumnName="id_equipe", nullable=false)
     */
    private $idEquipe;

    /**
     * @var boolean
     *
     * @ORM\Column(name="domicile", type="boolean", nullable=false)
     */
    private $domicile;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hosted", type="boolean", nullable=false)
     */
    private $hosted;

    /**
     * @var boolean
     *
     * @ORM\Column(name="reporte", type="boolean", nullable=false)
     */
    private $reporte;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="date", name="date_report", nullable=false)
     */
    private $dateReport;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "L'adversaire doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @ORM\Column(name="adversaire", nullable=true, type="string", length=50)
     */
    private $adversaire;

    /**
     * @param int $n
     * @return Competiteur|null
     */
    public function getIdJoueurN(int $n): ?Competiteur
    {
        if ($n == 0) return $this->getIdJoueur0();
        else if ($n == 1) return $this->getIdJoueur1();
        else if ($n == 2) return $this->getIdJoueur2();
        else if ($n == 3) return $this->getIdJoueur3();
        else if ($n == 4) return $this->getIdJoueur4();
        else if ($n == 5) return $this->getIdJoueur5();
        else if ($n == 6) return $this->getIdJoueur6();
        else if ($n == 7) return $this->getIdJoueur7();
        else if ($n == 8) return $this->getIdJoueur8();
        else return null;
    }

    /**
     * Retourne la liste des joueurs sélectionnés d'une rencontre
     * @return int[]
     */
    public function getSelectedPlayers(): array
    {
        $players = [];
        if ($this->getIdJoueur0()) array_push($players, $this->getIdJoueur0()->getIdCompetiteur());
        if ($this->getIdJoueur1()) array_push($players, $this->getIdJoueur1()->getIdCompetiteur());
        if ($this->getIdJoueur2()) array_push($players, $this->getIdJoueur2()->getIdCompetiteur());
        if ($this->getIdJoueur3()) array_push($players, $this->getIdJoueur3()->getIdCompetiteur());
        if ($this->getIdJoueur4()) array_push($players, $this->getIdJoueur4()->getIdCompetiteur());
        if ($this->getIdJoueur5()) array_push($players, $this->getIdJoueur5()->getIdCompetiteur());
        if ($this->getIdJoueur6()) array_push($players, $this->getIdJoueur6()->getIdCompetiteur());
        if ($this->getIdJoueur7()) array_push($players, $this->getIdJoueur7()->getIdCompetiteur());
        if ($this->getIdJoueur8()) array_push($players, $this->getIdJoueur8()->getIdCompetiteur());
        return $players;
    }

    /**
     * @param int $n
     * @return Rencontre
     */
    public function setIdJoueurNToNull(int $n): self
    {
        if ($n == 0) return $this->setIdJoueur0(null);
        else if ($n == 1) return $this->setIdJoueur1(null);
        else if ($n == 2) return $this->setIdJoueur2(null);
        else if ($n == 3) return $this->setIdJoueur3(null);
        else if ($n == 4) return $this->setIdJoueur4(null);
        else if ($n == 5) return $this->setIdJoueur5(null);
        else if ($n == 6) return $this->setIdJoueur6(null);
        else if ($n == 7) return $this->setIdJoueur7(null);
        else if ($n == 8) return $this->setIdJoueur8(null);
        else return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur0(): ?Competiteur
    {
        return $this->idJoueur0;
    }

    /**
     * @param Competiteur|null $idJoueur0
     * @return Rencontre
     */
    public function setIdJoueur0(?Competiteur $idJoueur0): Rencontre
    {
        $this->idJoueur0 = $idJoueur0;
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
     * @return Rencontre
     */
    public function setIdJoueur1(?Competiteur $idJoueur1): Rencontre
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
     * @return Rencontre
     */
    public function setIdJoueur2(?Competiteur $idJoueur2): Rencontre
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
     * @return Rencontre
     */
    public function setIdJoueur3(?Competiteur $idJoueur3): Rencontre
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
     * @return Rencontre
     */
    public function setIdJoueur4(?Competiteur $idJoueur4): Rencontre
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
     * @return Rencontre
     */
    public function setIdJoueur5(?Competiteur $idJoueur5): Rencontre
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
     * @return Rencontre
     */
    public function setIdJoueur6(?Competiteur $idJoueur6): Rencontre
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
     * @return Rencontre
     */
    public function setIdJoueur7(?Competiteur $idJoueur7): Rencontre
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
     * @return Rencontre
     */
    public function setIdJoueur8(?Competiteur $idJoueur8): Rencontre
    {
        $this->idJoueur8 = $idJoueur8;
        return $this;
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
     * @return Rencontre
     */
    public function setDomicile(bool $domicile): self
    {
        $this->domicile = $domicile;
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
     * @return Rencontre
     */
    public function setId($id): self
    {
        $this->id = $id;
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
     * @return Rencontre
     */
    public function setIdJournee(Journee $idJournee): self
    {
        $this->idJournee = $idJournee;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdversaire(): ?string
    {
        return $this->adversaire;
    }

    /**
     * @param string|null $adversaire
     * @return Rencontre
     */
    public function setAdversaire(?string $adversaire): self
    {
        $this->adversaire = ($adversaire == null ? null : mb_convert_case($adversaire, MB_CASE_TITLE, "UTF-8"));
        return $this;
    }

    /**
     * @param Equipe $idEquipe
     * @return Rencontre
     */
    public function setIdEquipe(Equipe $idEquipe): self
    {
        $this->idEquipe = $idEquipe;
        return $this;
    }

    /**
     * @return Equipe
     */
    public function getIdEquipe(): Equipe
    {
        return $this->idEquipe;
    }

    /**
     * @return bool
     */
    public function getIsEmpty(): bool
    {
        $isEmpty = array();
        for ($i = 0; $i < $this->getIdEquipe()->getIdDivision()->getNbJoueurs(); $i++){
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
     * @return Rencontre
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
     * @return Rencontre
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
     * @return Rencontre
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
     * @return Rencontre
     */
    public function setDateReport(DateTime $dateReport): self
    {
        $this->dateReport = $dateReport;
        return $this;
    }

    /**
     * @return Championnat
     */
    public function getIdChampionnat(): Championnat
    {
        return $this->idChampionnat;
    }

    /**
     * @param Championnat $idChampionnat
     * @return Rencontre
     */
    public function setIdChampionnat(Championnat $idChampionnat): self
    {
        $this->idChampionnat = $idChampionnat;
        return $this;
    }

    /**
     * Liste des joueurs sélectionnés dans une composition d'équipe
     * @return Competiteur[]|null[]
     */
    public function getListSelectedPlayers(): array
    {
        $joueurs = array();
        for ($i = 0; $i < $this->getIdEquipe()->getIdDivision()->getNbJoueurs(); $i++){
            if ($this->getIdJoueurN($i))array_push($joueurs, $this->getIdJoueurN($i));
        }
        return $joueurs;
    }

    /**
     * Liste des adresses mail et numéros de téléphone des joueurs sélectionnés et contactables
     * @param int $idRedacteur
     * @return array
     */
    public function getListContactSelectedPlayers(int $idRedacteur): array
    {
        $joueurs = $this->getListSelectedPlayers();

        $mails = [];
        $contactablesMails = [];
        $notContactablesMails = [];
        foreach ($joueurs as $joueur) {
            if ($joueur->getIdCompetiteur() != $idRedacteur){
                if ($joueur->getFirstContactableMail()){
                    array_push($contactablesMails, $joueur);
                    array_push($mails, $joueur->getFirstContactableMail());
                }
                else array_push($notContactablesMails, $joueur);
            }
        }
        $response['mail']['toString'] = implode(',', $mails);
        $response['mail']['contactables'] = $contactablesMails;
        $response['mail']['notContactables'] = $notContactablesMails;

        $phoneNumbers = [];
        $contactablesPhoneNumbers = [];
        $notContactablesPhoneNumbers = [];
        foreach ($joueurs as $joueur) {
            if ($joueur->getIdCompetiteur() != $idRedacteur){
                if ($joueur->getFirstContactablePhoneNumber()){
                    array_push($contactablesPhoneNumbers, $joueur);
                    array_push($phoneNumbers, $joueur->getFirstContactablePhoneNumber());
                }
                else array_push($notContactablesPhoneNumbers, $joueur);
            }
        }
        $response['sms']['toString'] = implode(',', $phoneNumbers);
        $response['sms']['contactables'] = $contactablesPhoneNumbers;
        $response['sms']['notContactables'] = $notContactablesPhoneNumbers;

        $minusNbJoueurs = count(array_filter($joueurs, function($joueur) use ($idRedacteur) {
            return $joueur->getIdCompetiteur() == $idRedacteur;
        })) > 0 ? 1 : 0;
        $response['nbJoueurs'] = count($joueurs) - $minusNbJoueurs;
        $response['nbJoueursWithoutMe'] = count($contactablesMails) + count($notContactablesMails) + count($notContactablesPhoneNumbers) + count($contactablesPhoneNumbers);

        return $response;
    }
}