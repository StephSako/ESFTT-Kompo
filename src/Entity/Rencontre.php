<?php

namespace App\Entity;

use App\Controller\UtilController;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

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
    /** Lieu d'une rencontre */
    const LIEU_RENCONTRE = [
        'Indéfini' => null,
        'Domicile' => true,
        'Extérieur' => false
    ];
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
     * @var boolean|null
     *
     * @ORM\Column(name="domicile", type="boolean", nullable=true)
     */
    private $domicile;
    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "La ville hôte doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @ORM\Column(name="ville_host", nullable=true, type="string", length=200)
     */
    private $villeHost;
    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 300,
     *      maxMessage = "L'alerte doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @ORM\Column(name="consigne", nullable=true, type="string", length=200)
     */
    private $consigne;
    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 300,
     *      maxMessage = "L'adresse doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @ORM\Column(name="adresse", nullable=true, type="string", length=300)
     */
    private $adresse;
    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 300,
     *      maxMessage = "Le complément d'adresse doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @ORM\Column(name="complement_adresse", nullable=true, type="string", length=300)
     */
    private $complementAdresse;
    /**
     * @var string|null
     *
     * @ORM\Column(name="telephone", type="string", length=10, nullable=true)
     *
     * @Assert\Regex(
     *     pattern="/[0-9]{10}/",
     *     message="Le numéro de téléphone doit contenir 10 chiffres"
     * )
     */
    private $telephone;
    /**
     * @var string|null
     *
     * @ORM\Column(name="site", type="string", length=100, nullable=true)
     */
    private $site;
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
     * @var boolean
     *
     * @ORM\Column(name="validation_compo", type="boolean", nullable=false)
     */
    private $validationCompo;
    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Le log de mise à jour doit contenir au maximum {{ limit }} lettres"
     * )
     *
     * @ORM\Column(type="string", name="last_update", nullable=true, length=100)
     */
    private $lastUpdate;

    /**
     * Rencontre constructor.
     * @param Championnat $type
     */
    public function __construct(Championnat $type)
    {
        $this->setIdChampionnat($type);
    }

    /**
     * Retourne la liste des joueurs sélectionnés d'une rencontre
     * @return int[]
     */
    public function getSelectedPlayers(): array
    {
        $players = [];
        if ($this->getIdJoueur0()) $players[] = $this->getIdJoueur0()->getIdCompetiteur();
        if ($this->getIdJoueur1()) $players[] = $this->getIdJoueur1()->getIdCompetiteur();
        if ($this->getIdJoueur2()) $players[] = $this->getIdJoueur2()->getIdCompetiteur();
        if ($this->getIdJoueur3()) $players[] = $this->getIdJoueur3()->getIdCompetiteur();
        if ($this->getIdJoueur4()) $players[] = $this->getIdJoueur4()->getIdCompetiteur();
        if ($this->getIdJoueur5()) $players[] = $this->getIdJoueur5()->getIdCompetiteur();
        if ($this->getIdJoueur6()) $players[] = $this->getIdJoueur6()->getIdCompetiteur();
        if ($this->getIdJoueur7()) $players[] = $this->getIdJoueur7()->getIdCompetiteur();
        if ($this->getIdJoueur8()) $players[] = $this->getIdJoueur8()->getIdCompetiteur();
        return $players;
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
     * @return bool
     */
    public function getIsEmpty(): bool
    {
        $isEmpty = array();
        if ($this->getIdEquipe()->getIdDivision()) {
            for ($i = 0; $i < $this->getIdEquipe()->getIdDivision()->getNbJoueurs(); $i++) {
                $isEmpty[] = $this->getIdJoueurN($i);
            }
            return !in_array(true, $isEmpty);
        } else return true;
    }

    /**
     * @return Equipe
     */
    public function getIdEquipe(): Equipe
    {
        return $this->idEquipe;
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
     * Liste des adresses e-mail et numéros de téléphone des joueurs sélectionnés et contactables
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
            if ($joueur->getIdCompetiteur() != $idRedacteur) {
                if ($joueur->getFirstContactableMail()) {
                    $contactablesMails[] = $joueur;
                    $mails[] = $joueur->getFirstContactableMail();
                } else $notContactablesMails[] = $joueur;
            }
        }
        $response['mail']['toString'] = implode(',', $mails);
        $response['mail']['contactables'] = $contactablesMails;
        $response['mail']['notContactables'] = $notContactablesMails;

        $phoneNumbers = [];
        $contactablesPhoneNumbers = [];
        $notContactablesPhoneNumbers = [];
        foreach ($joueurs as $joueur) {
            if ($joueur->getIdCompetiteur() != $idRedacteur) {
                if ($joueur->getFirstContactablePhoneNumber()) {
                    $contactablesPhoneNumbers[] = $joueur;
                    $phoneNumbers[] = $joueur->getFirstContactablePhoneNumber();
                } else $notContactablesPhoneNumbers[] = $joueur;
            }
        }
        $response['sms']['toString'] = implode(',', $phoneNumbers);
        $response['sms']['contactables'] = $contactablesPhoneNumbers;
        $response['sms']['notContactables'] = $notContactablesPhoneNumbers;

        $response['nbJoueursWithoutMe'] = count($contactablesMails) + count($notContactablesMails) + count($notContactablesPhoneNumbers) + count($contactablesPhoneNumbers);

        return $response;
    }

    /**
     * Liste des joueurs sélectionnés dans la composition d'équipe
     * @return Competiteur[]|null[]
     */
    public function getListSelectedPlayers(): array
    {
        $joueurs = array();
        for ($i = 0; $i < $this->getIdEquipe()->getIdDivision()->getNbJoueurs(); $i++) {
            if ($this->getIdJoueurN($i)) $joueurs[] = $this->getIdJoueurN($i);
        }
        return $joueurs;
    }

    /**
     * Retourne le message pour alerter les joueurs de leur sélection
     * @return string
     */
    public function getObjetAlertPlayers(): string
    {
        $objet = "?subject=" . "RDV Ping Compétition " . $this->getIdChampionnat()->getNom();

        if (!$this->getIdJournee()->getUndefined()) {
            $objet .= ' - ';
            $objet .= (!$this->isReporte() ? $this->getIdJournee()->getDateJourneeFrench() : $this->getDateReportFrench());
        }
        return $objet;
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
     * @return string
     */
    public function getDateReportFrench(): string
    {
        return mb_convert_case(strftime("%A %d %B %Y", $this->getDateReport()->getTimestamp()), MB_CASE_TITLE, "UTF-8");
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
     * Retourne le message pour alerter les joueurs de leurs sélections
     * @param string $prenomSender
     * @return string
     */
    public function getMessageAlertPlayers(string $prenomSender): string
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');
        date_default_timezone_set('Europe/Paris');
        $br = '%0D%0A';
        $date = !$this->isReporte() ? $this->getIdJournee()->getDateJourneeFrench() : $this->getDateReportFrench();
        $message = "Salut, c'est " . $prenomSender . '.' . $br . $br;
        $message .= 'Vous êtes sélectionnés en équipe ' . $this->getIdEquipe()->getNumero() . ' pour le championnat : ' . $this->getIdChampionnat()->getNom();
        $message .= (!$this->getIdJournee()->getUndefined() ? ', le ' . $date : ', à une date indéterminée pour le moment') . ".";

        if ($this->isExempt()) $message .= $br . "Cependant, l'équipe " . $this->getIdEquipe()->getNumero() . " est exemptée pour cette journée ce qui signifie qu'il n'y aura donc pas match à cette date." . $br . $br . 'Bonne journée à vous.';
        else {
            $message .= $br . $br . 'Les joueurs sélectionnés sont :' . $br . implode($br, array_map(function ($joueur) {
                    return $joueur->getPrenom() . ' ' . $joueur->getNom();
                }, $this->getListSelectedPlayers())) . $br . $br;

            $message .= 'Merci de me confirmer votre disponibilité en réponse à ce message !' . $br . 'Vous avez rendez-vous à la ';

            if ($this->getDomicile() && !$this->getVilleHost()) $message .= 'salle Albert Marquet à 19h45';
            else $message .= 'gare de La Frette à 19h30';

            $message .= " et nous jouerons";
            if ($this->getDomicile() !== null) {
                $message .= ($this->getDomicile() && !$this->getVilleHost() ? " à domicile" : " à l'extérieur");
            } else $message .= " à un lieu indéterminé";

            $message .= " contre " . ($this->getAdversaire() ? $this->getAdversaire() . ($this->getVilleHost() ? ' (salle indisponible : rencontre à ' . $this->getVilleHost() . ')' : '') : 'une équipe indéterminée pour le moment');

            $message .= '.' . $br . $br;
            if ($this->getConsigne()) $message .= $this->getConsigne() . '.' . $br . $br;
            $message .= 'A ' . strtok($date, ' ') . ' !';
        }

        return $message;
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
     * @return bool|null
     */
    public function getDomicile(): ?bool
    {
        return $this->domicile;
    }

    /**
     * @param bool|null $domicile
     * @return Rencontre
     */
    public function setDomicile(?bool $domicile): self
    {
        $this->domicile = $domicile;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVilleHost(): ?string
    {
        return $this->villeHost;
    }

    /**
     * @param string|null $villeHost
     * @return Rencontre
     */
    public function setVilleHost(?string $villeHost): self
    {
        $this->villeHost = $villeHost;
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
     * @return string|null
     */
    public function getConsigne(): ?string
    {
        return $this->consigne;
    }

    /**
     * @param string|null $consigne
     * @return Rencontre
     */
    public function setConsigne(?string $consigne): Rencontre
    {
        $this->consigne = $consigne;
        return $this;
    }

    /**
     * Trier la composition d'équipe selon les classements dans l'ordre décroissant
     * @return void
     */
    public function sortComposition(): void
    {
        if ($this->getIdChampionnat()->isCompoSorted() && count($this->getListSelectedPlayers())
            && intval((new DateTime())->diff(max($this->getDateReport(), $this->getIdJournee()->getDateJournee()))->format('%R%a')) >= 0) {
            $compoToSort = $this->getListSelectedPlayers();
            $this->emptyCompo();
            usort($compoToSort, function ($joueur1, $joueur2) {
                return $joueur2->getClassementOfficiel() - $joueur1->getClassementOfficiel();
            });

            foreach ($compoToSort as $i => $joueur) {
                $this->setIdJoueurN($i, $joueur);
            }
        }
    }

    public function emptyCompo(): void
    {
        for ($i = 0; $i < $this->getIdEquipe()->getIdDivision()->getNbJoueurs(); $i++) {
            $this->setIdJoueurN($i, null);
        }
    }

    /**
     * @param int $n
     * @param Competiteur|null $competiteur
     * @return Rencontre
     */
    public function setIdJoueurN(int $n, ?Competiteur $competiteur): self
    {
        if ($n == 0) return $this->setIdJoueur0($competiteur);
        else if ($n == 1) return $this->setIdJoueur1($competiteur);
        else if ($n == 2) return $this->setIdJoueur2($competiteur);
        else if ($n == 3) return $this->setIdJoueur3($competiteur);
        else if ($n == 4) return $this->setIdJoueur4($competiteur);
        else if ($n == 5) return $this->setIdJoueur5($competiteur);
        else if ($n == 6) return $this->setIdJoueur6($competiteur);
        else if ($n == 7) return $this->setIdJoueur7($competiteur);
        else if ($n == 8) return $this->setIdJoueur8($competiteur);
        else return $this;
    }

    /**
     * @return string|null
     */
    public function getHrefMapsAdresse(): ?string
    {
        return UtilController::MAPS_URI . urlencode($this->getAdresse());
    }

    /**
     * @return string|null
     */
    public function getAdresse(): ?string
    {
        return trim($this->adresse);
    }

    /**
     * @param string|null $adresse
     * @return Rencontre
     */
    public function setAdresse(?string $adresse): self
    {
        $this->adresse = strlen(trim($adresse)) ? trim(mb_convert_case($adresse, MB_CASE_TITLE, "UTF-8")) : null;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHrefWazeAdresse(): ?string
    {
        return UtilController::WAZE_URI . urlencode($this->getAdresse());
    }

    /**
     * @return string|null
     */
    public function getTelephone(): ?string
    {
        return str_replace(' ', '', trim($this->telephone));
    }

    /**
     * @param string|null $telephone
     * @return Rencontre
     */
    public function setTelephone(?string $telephone): self
    {
        $this->telephone = strlen(trim($telephone)) ? str_replace(' ', '', trim($telephone)) : null;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSite(): ?string
    {
        return trim($this->site);
    }

    /**
     * @param string|null $site
     * @return Rencontre
     */
    public function setSite(?string $site): self
    {
        $this->site = strlen(trim($site)) ? trim($site) : null;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComplementAdresse(): ?string
    {
        return trim($this->complementAdresse);
    }

    /**
     * @param string|null $complementAdresse
     * @return Rencontre
     */
    public function setComplementAdresse(?string $complementAdresse): self
    {
        $this->complementAdresse = strlen(trim($complementAdresse)) ? trim($complementAdresse) : null;
        return $this;
    }

    /**
     * Détermine si une rencontre est passée ou non
     * @return bool
     */
    public function isOver(): bool
    {
        $dateDepassee = intval((new DateTime())->diff($this->getIdJournee()->getDateJournee())->format('%R%a')) >= 0;
        $dateReporteeDepassee = intval((new DateTime())->diff($this->getDateReport())->format('%R%a')) >= 0;
        return !(($dateDepassee && !$this->isReporte()) || ($dateReporteeDepassee && $this->isReporte()) || $this->getIdJournee()->getUndefined());
    }

    /**
     * @return void
     */
    public function toggleCompValidation(): void
    {
        $this->setValidationCompo(!$this->isValidationCompo());
    }

    /**
     * @return bool
     */
    public function isValidationCompo(): bool
    {
        return $this->validationCompo;
    }

    /**
     * @param bool $validationCompo
     * @return Rencontre
     */
    public function setValidationCompo(bool $validationCompo): Rencontre
    {
        $this->validationCompo = $validationCompo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastUpdate(): ?string
    {
        return $this->lastUpdate;
    }

    /**
     * @param string|null $lastUpdate
     * @return Rencontre
     */
    public function setLastUpdate(?string $lastUpdate): self
    {
        $this->lastUpdate = $lastUpdate;
        return $this;
    }
}