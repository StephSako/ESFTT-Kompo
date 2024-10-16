<?php

namespace App\Controller\BackOffice;

use App\Controller\ContactController;
use App\Controller\UtilController;
use App\Entity\Championnat;
use App\Entity\Division;
use App\Entity\Journee;
use App\Entity\Poule;
use App\Entity\Rencontre;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DivisionRepository;
use App\Repository\PouleRepository;
use App\Repository\SettingsRepository;
use Cocur\Slugify\Slugify;
use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FFTTApi\FFTTApi;
use FFTTApi\Model\Equipe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeFFTTApiController extends AbstractController
{

    /** Position des données dans les chaînes de caractères reçues de l'API */
    const REGEX_JOURNEE_NUMBER = '/^Poule [0-9]+ - tour n°([0-9]+) du \d{2}\/\d{2}\/\d{4}$/';
    const REGEX_ORGANISME_PERE = '/^cx_poule=[0-9]*&D1=[0-9]+&organisme_pere=([0-9]+)$/';
    const DIVISION_PARTIE_UN = 0;
    const DIVISION_PARTIE_DEUX = 1;
    const REGEX_NUMERO_EQUIPE = '/^[A-Z\s]+ (\(?[0-9]+\)?) - Phase ([1|2])$/';
    const STRING_LIBELLE_POULE = 'Poule';
    const CONVERTIONS = [
        "L08_Honneur Poule" => [
            "shortName" => "H",
            "longName" => "Honneur"
        ],
        "L08_R3 Messieurs" => [
            "shortName" => "R3",
            "longName" => "Régionale 3"
        ],
        "L08_2eme Division" => [
            "shortName" => "D2",
            "longName" => "Division 2"
        ],
        "championnat veteran" => [
            "indexShortName" => 2,
            "indexLongName" => 2,
        ]
    ];
    private $competiteurRepository;
    private $championnatRepository;
    private $em;
    private $divisionRepository;
    private $pouleRepository;
    private $settingsRepository;

    /**
     * @param CompetiteurRepository $competiteurRepository
     * @param ChampionnatRepository $championnatRepository
     * @param DivisionRepository $divisionRepository
     * @param SettingsRepository $settingsRepository
     * @param PouleRepository $pouleRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(CompetiteurRepository  $competiteurRepository,
                                ChampionnatRepository  $championnatRepository,
                                DivisionRepository     $divisionRepository,
                                SettingsRepository     $settingsRepository,
                                PouleRepository        $pouleRepository,
                                EntityManagerInterface $em)
    {
        $this->competiteurRepository = $competiteurRepository;
        $this->championnatRepository = $championnatRepository;
        $this->em = $em;
        $this->divisionRepository = $divisionRepository;
        $this->pouleRepository = $pouleRepository;
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * @param Request $request
     * @param ContactController $contactController
     * @param UtilController $utilController
     * @return Response
     * @Route("/backoffice/settings/update", name="backoffice.settings.update")
     */
    public function index(Request $request, ContactController $contactController, UtilController $utilController): Response
    {
        $allChampionnatsReset = [];
        /** Tableau où sera stockée toute la data à update par championnat */
        $joueursIssued['competition'] = [];
        /** Tableau où seront stockés tous les joueurs compétiteurs devant être mis à jour */
        $errorMajJoueurs = false;
        $errorMajRencontresEquipes = false;

        /** Objet API */
        $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
        try {
            /** Gestion des joueurs */
            $joueursKompo = $this->competiteurRepository->findBy(['isArchive' => 0], ['nom' => 'ASC', 'prenom' => 'ASC']);
            $joueursIssued['nbJoueursCritFed'] = count(array_filter($joueursKompo, function ($joueur) {
                return $joueur->isCritFed();
            }));
            $joueursFFTT = $api->getJoueursByClub($this->getParameter('club_id'));

            foreach ($joueursKompo as $competiteur) {
                $joueurFFTT = array_filter($joueursFFTT,
                    function ($joueurFFTT) use ($competiteur) {
                        return $competiteur->getLicence() == $joueurFFTT->getLicence();
                    });

                if (count($joueurFFTT)) {
                    /** Si la licence correspond bien */
                    $joueur = array_values($joueurFFTT)[0];
                    $isCritFed = $competiteur->isCritFed();
                    /** Si le joueur est inscrit au critérium fédéral */
                    $sameName = (new Slugify())->slugify($competiteur->getNom() . $competiteur->getPrenom()) == (new Slugify())->slugify($joueur->getNom() . $joueur->getPrenom());
                    /** Si les noms/prénoms ne concordent pas */
                    $sameClassement = $joueur->getPoints() == $competiteur->getClassementOfficiel();
                    /** Si les classements ne concordent pas */
                    if ((!$sameClassement || !$sameName || $isCritFed) && intval($joueur->getPoints()) > 0) {
                        $joueursIssued['competition'][$competiteur->getIdCompetiteur()]['joueur'] = $competiteur;
                        $joueursIssued['competition'][$competiteur->getIdCompetiteur()]['pointsFFTT'] = intval($joueur->getPoints());
                        $joueursIssued['competition'][$competiteur->getIdCompetiteur()]['nomFFTT'] = $joueur->getNom();
                        $joueursIssued['competition'][$competiteur->getIdCompetiteur()]['prenomFFTT'] = $joueur->getPrenom();
                        $joueursIssued['competition'][$competiteur->getIdCompetiteur()]['sameName'] = $sameName;
                        $joueursIssued['competition'][$competiteur->getIdCompetiteur()]['sameClassement'] = $sameClassement;
                        $joueursIssued['competition'][$competiteur->getIdCompetiteur()]['isCritFed'] = $isCritFed;
                    }
                }
            }
            $joueursIssued['nbJoueursCritFedOnly'] = count(array_filter($joueursIssued['competition'], function ($joueur) {
                return $joueur['isCritFed'] && $joueur['sameName'] && $joueur['sameClassement'];
            }));

        } catch (Exception $e) {
            $this->addFlash('fail', 'Mise à jour des membres impossible : API de la FFTT indisponible pour le moment');
            $errorMajJoueurs = true;
        }

        try {
            $allChampionnats = $this->championnatRepository->getChampionnatsUpdatableByFFTTApi();

            foreach ($allChampionnats as $championnatActif) {
                $allChampionnatsReset[$championnatActif->getNom()] = [];
                $journeesKompo = $championnatActif->getJournees()->toArray();

                /** Gestion des équipes */
                $equipesKompo = $championnatActif->getEquipes()->toArray();
                $equipesFFTT = array_values(array_filter($api->getEquipesByClub($this->getParameter('club_id'), 'M'), function (Equipe $eq) use ($championnatActif) {
                    $typeEpreuve = $eq->getEpreuve();
                    $phase = $this->getValueFromRegex(self::REGEX_NUMERO_EQUIPE, $eq->getLibelle(), 2);
                    return $typeEpreuve == $championnatActif->getTypeEpreuve() && (!$championnatActif->isPeriodicite() || $phase == $this->getDatePhase());
                }));

                /** On ordonne les objets des Equipes selon leurs numéros */
                usort($equipesFFTT, function ($equi1, $equi2) {
                    return $this->getEquipeNumero($equi1->getLibelle()) - $this->getEquipeNumero($equi2->getLibelle());
                });

                /** On vérifie que le championnat est enregistré du côté de la FFTT en comptant les équipes */
                $allChampionnatsReset[$championnatActif->getNom()]["enAttenteNouvellePhase"] = !(count($equipesFFTT) == 0 && count(array_filter($equipesKompo, function (\App\Entity\Equipe $equipeKompo) {
                        return $equipeKompo->getIdPoule() == null;
                    })) == count($equipesKompo));

                if ($allChampionnatsReset[$championnatActif->getNom()]["enAttenteNouvellePhase"]) {
                    $allChampionnatsReset[$championnatActif->getNom()]["idChampionnat"] = $championnatActif->getIdChampionnat();

                    $equipesIssued = [];

                    $equipesIDsFFTT = array_map(function ($equipeFFTT) {
                        return $this->getEquipeNumero($equipeFFTT->getLibelle());
                    }, $equipesFFTT);
                    $equipesIDsKompo = array_map(function ($equipe) {
                        return $equipe->getNumero();
                    }, $equipesKompo);

                    $equipesToCreateIDs = array_diff($equipesIDsFFTT, $equipesIDsKompo);
                    $equipesToCreate = array_filter($equipesFFTT, function ($indexEquipeFFTT) use ($equipesToCreateIDs) {
                        return in_array($this->getEquipeNumero($indexEquipeFFTT->getLibelle()), $equipesToCreateIDs);
                    });

                    $equipesToCreate = array_combine(array_map(function ($equipeToEditIndex) {
                        return preg_replace('/\D/', '', $this->getEquipeNumero($equipeToEditIndex->getLibelle()));
                    }, $equipesToCreate), array_values($equipesToCreate));

                    $equipesNonEnregistreesIDs = array_diff($equipesIDsKompo, $equipesIDsFFTT);
                    $equipesNonEnregistrees = array_filter($equipesKompo, function ($equipeKompo) use ($equipesNonEnregistreesIDs) {
                        return in_array($equipeKompo->getNumero(), $equipesNonEnregistreesIDs);
                    });

                    $equipesIDsCommon = array_intersect($equipesIDsFFTT, $equipesIDsKompo);

                    foreach ($equipesFFTT as $equipe) {
                        $numeroEquipeFFTT = $this->getEquipeNumero($equipe->getLibelle());
                        $libelleDivisionEquipeFFTT = explode(' ', $equipe->getDivision());

                        if ($libelleDivisionEquipeFFTT[count($libelleDivisionEquipeFFTT) - 2] == self::STRING_LIBELLE_POULE) {
                            $pouleEquipeFFTT = $libelleDivisionEquipeFFTT[count($libelleDivisionEquipeFFTT) - 1];
                        } else {
                            $pouleEquipeFFTT = null;
                        }

                        $divisionEquipeFFTT = $this->labelDivisionFormatter($libelleDivisionEquipeFFTT);
                        $equipeIssued = [];

                        /** L'équipe est recensée des 2 côtés */
                        if (!in_array($numeroEquipeFFTT, array_merge($equipesToCreateIDs, $equipesNonEnregistreesIDs))) {
                            $equipeKompo = array_values(array_filter($equipesKompo, function ($equipe) use ($numeroEquipeFFTT) {
                                return $equipe->getNumero() == $numeroEquipeFFTT;
                            }))[0];

                            $sameDivision = $equipeKompo->getIdDivision() && $equipeKompo->getIdDivision()->getShortName() == $divisionEquipeFFTT["shortName"];
                            $samePoule = ($equipeKompo->getIdPoule() && $equipeKompo->getIdPoule()->getPoule() == mb_strtoupper($pouleEquipeFFTT)) || ($equipeKompo->getIdPoule() == null && mb_strtoupper($pouleEquipeFFTT) == "");
                            $sameLienDivision = $equipeKompo->getLienDivision() && $equipeKompo->getLienDivision() == $equipe->getLienDivision();
                            if (!$sameDivision || !$samePoule || !$sameLienDivision) {
                                $equipeIssued['equipe'] = $equipeKompo;
                                $equipeIssued['divisionFFTTLongName'] = $divisionEquipeFFTT["longName"];
                                $equipeIssued['divisionFFTTShortName'] = $divisionEquipeFFTT["shortName"];
                                $equipeIssued['pouleFFTT'] = $pouleEquipeFFTT;
                                $equipeIssued['sameDivision'] = $sameDivision;
                                $equipeIssued['lienDivision'] = $equipe->getLienDivision();
                                $equipeIssued['samePoule'] = $samePoule;
                                $equipeIssued['sameLienDivision'] = $sameLienDivision;
                                $equipesIssued[] = $equipeIssued;
                            }
                        } else if (in_array($numeroEquipeFFTT, $equipesToCreateIDs)) {
                            unset($equipesToCreate[$numeroEquipeFFTT]);
                            $equipesToCreate[$numeroEquipeFFTT]["poule"] = $pouleEquipeFFTT;
                            $equipesToCreate[$numeroEquipeFFTT]["lienDivision"] = $equipe->getLienDivision();
                            $equipesToCreate[$numeroEquipeFFTT]["divisionShortName"] = $divisionEquipeFFTT["shortName"];
                            $equipesToCreate[$numeroEquipeFFTT]["divisionLongName"] = $divisionEquipeFFTT["longName"];
                        }
                    }
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["issued"] = $equipesIssued;
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["notRegistered"] = $equipesNonEnregistrees;
                    sort($equipesNonEnregistreesIDs);
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["notRegisteredIDs"] = $equipesNonEnregistreesIDs;
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["toCreate"] = $equipesToCreate;
                    sort($equipesToCreateIDs);
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["toCreateIDs"] = $equipesToCreateIDs;
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["kompo"] = $equipesKompo;
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["toUpdate"] = array_map(function ($equipe) {
                        return $equipe["equipe"]->getNumero();
                    }, $equipesIssued);

                    /** Gestion des rencontres */
                    $rencontresKompo = $championnatActif->getRencontres()->toArray();
                    $rencontresParEquipes = [];
                    $journeesFFTT = [];
                    $rencontresEquipeKompo = null;
                    $adressesClubs = [];

                    foreach ($equipesFFTT as $index => $equipe) {
                        $nbEquipe = $index + 1;

                        if (in_array($nbEquipe, array_merge($equipesIDsCommon, $equipesToCreateIDs))) {

                            $rencontresFFTT = array_values(array_filter($api->getRencontrePouleByLienDivision($equipe->getLienDivision()), function ($rencontre) {
                                return str_contains($rencontre->getNomEquipeA(), $this->getParameter('club_name')) || str_contains($rencontre->getNomEquipeB(), $this->getParameter('club_name'));
                            }));

                            if (in_array($nbEquipe, $equipesIDsCommon)) {
                                $rencontresEquipeKompo = array_values(array_filter($rencontresKompo, function ($renc) use ($nbEquipe) {
                                    return $renc->getIdEquipe()->getNumero() == $nbEquipe;
                                }));
                                usort($rencontresEquipeKompo, function ($element1, $element2) {
                                    $datetime1 = ($element1->getIdjournee()->getDateJournee())->getTimeStamp();
                                    $datetime2 = ($element2->getIdjournee()->getDateJournee())->getTimeStamp();
                                    return $datetime1 - $datetime2;
                                });
                            }

                            if (!$journeesFFTT) {
                                $journeesFFTT = array_values(array_unique(array_map(function ($renc) {
                                    return strtotime($renc->getDatePrevue()->format('d-m-Y'));
                                }, $rencontresFFTT)));

                                $allChampionnatsReset[$championnatActif->getNom()]["dates"]["realNbDates"] = count($journeesFFTT);
                            }

                            foreach ($rencontresFFTT as $i => $rencontre) {
                                $domicile = str_contains($rencontre->getNomEquipeA(), $this->getParameter('club_name'));

                                parse_str($rencontre->getLien(), $output);
                                $idClubAdversaire = $output[!$domicile ? 'clubnum_1' : 'clubnum_2'];

                                $isExempt = $idClubAdversaire == null || ($rencontre->getNomEquipeA() == 'Exempt' || $rencontre->getNomEquipeB() == 'Exempt');
                                $adversaire = !$isExempt ? mb_convert_case($domicile ? $rencontre->getNomEquipeB() : $rencontre->getNomEquipeA(), MB_CASE_TITLE, "UTF-8") : null;

                                /** On request les détails du club adversaire */
                                if ($idClubAdversaire && !array_key_exists($idClubAdversaire, $adressesClubs)) {
                                    $adressesClubs[$idClubAdversaire] = null;
                                }

                                $rencontreTemp = [];
                                $rencontreTemp['equipeESFTT'] = $domicile ? $rencontre->getNomEquipeA() : $rencontre->getNomEquipeB();
                                $rencontreTemp['nbEquipe'] = $nbEquipe;
                                $rencontreTemp['journee'] = $this->getValueFromRegex(self::REGEX_JOURNEE_NUMBER, $rencontre->getLibelle());
                                $rencontreTemp['adversaireFFTT'] = $adversaire;
                                $rencontreTemp['exempt'] = $isExempt;
                                $rencontreTemp['domicileFFTT'] = $domicile;
                                $rencontreTemp['dateReelle'] = $rencontre->getDatePrevue();

                                /** Contrôle de l'affichage des pictogrammes dans le formulaire à OK par défaut */
                                $rencontreTemp['infosContact']['adresse'] = 0;
                                $rencontreTemp['infosContact']['complementAdresse'] = 0;
                                $rencontreTemp['infosContact']['site'] = 0;
                                $rencontreTemp['infosContact']['telephone'] = 0;

                                if ($i <= $championnatActif->getNbJournees() - 1) {
                                    if (in_array($nbEquipe, $equipesIDsCommon)) {
                                        /** On fix les rencontres existantes des équipes existantes */

                                        $noCoordonneesRencontreKompo = !$rencontresEquipeKompo[$i]->getSite() && !$rencontresEquipeKompo[$i]->getAdresse() && !$rencontresEquipeKompo[$i]->getTelephone() && !$rencontresEquipeKompo[$i]->getComplementAdresse();
                                        if (($isExempt != $rencontresEquipeKompo[$i]->isExempt()) ||
                                            (!$isExempt &&
                                                ($rencontresEquipeKompo[$i]->getAdversaire() != $rencontreTemp['adversaireFFTT'] ||
                                                    $domicile != $rencontresEquipeKompo[$i]->getDomicile() ||
                                                    (($rencontresEquipeKompo[$i]->getAdversaire() != $rencontreTemp['adversaireFFTT']) || $noCoordonneesRencontreKompo)))) {

                                            /** Si aucune information de contact n'est renseignée dans la rencontre Kompo ou si le nom de l'adversaire a changé, on les renseigne/met à jour */
                                            if ($idClubAdversaire && !$adressesClubs[$idClubAdversaire]) {
                                                $adressesClubs[$idClubAdversaire] = $api->getClubDetails($idClubAdversaire);
                                            }

                                            /** On indique l'adresse, complément d'adresse et site web du club adverse */
                                            $adresseAdversaire = $idClubAdversaire ? UtilController::formatAddress($adressesClubs[$idClubAdversaire]->getAdresseSalle1(), $adressesClubs[$idClubAdversaire]->getCodePostaleSalle(), $adressesClubs[$idClubAdversaire]->getVilleSalle()) : '';
                                            $complementAdresseAdversaire = $idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getNomSalle() . ' ' .
                                                $adressesClubs[$idClubAdversaire]->getAdresseSalle2() . ' ' .
                                                $adressesClubs[$idClubAdversaire]->getAdresseSalle3() : '';
                                            $rencontreTemp['adresse'] = $adresseAdversaire;
                                            $rencontreTemp['site'] = $idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getSiteWeb() : '';
                                            $rencontreTemp['telephone'] = $idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getTelCoordo() : '';
                                            $rencontreTemp['complementAdresse'] = $complementAdresseAdversaire;

                                            if ($rencontresEquipeKompo[$i]->getAdversaire() != $rencontreTemp['adversaireFFTT']) {
                                                $rencontreTemp['forceUpdateInfoAddresse'] = true;
                                            }

                                            /** Contrôle de l'affichage des pictogrammes dans le formulaire */
                                            $rencontreTemp['infosContact']['adresse'] = ($rencontresEquipeKompo[$i]->getAdversaire() != $rencontreTemp['adversaireFFTT']) || ($noCoordonneesRencontreKompo && strlen(trim($adresseAdversaire)) > 0 && !strlen($rencontresEquipeKompo[$i]->getAdresse()));
                                            $rencontreTemp['infosContact']['complementAdresse'] = ($rencontresEquipeKompo[$i]->getAdversaire() != $rencontreTemp['adversaireFFTT']) || ($noCoordonneesRencontreKompo && strlen(trim($complementAdresseAdversaire)) > 0 && !strlen($rencontresEquipeKompo[$i]->getComplementAdresse()));
                                            $rencontreTemp['infosContact']['site'] = ($rencontresEquipeKompo[$i]->getAdversaire() != $rencontreTemp['adversaireFFTT']) || ($noCoordonneesRencontreKompo && strlen(trim($idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getSiteWeb() : '')) > 0 && !strlen($rencontresEquipeKompo[$i]->getSite()));
                                            $rencontreTemp['infosContact']['telephone'] = ($rencontresEquipeKompo[$i]->getAdversaire() != $rencontreTemp['adversaireFFTT']) || ($noCoordonneesRencontreKompo && strlen(trim($idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getTelCoordo() : '') > 0 && !strlen($rencontresEquipeKompo[$i]->getTelephone())));

                                            $rencontreTemp['rencontre'] = $rencontresEquipeKompo[$i];
                                            $rencontreTemp['enAttenteNouvellePhase'] = true;
                                            $rencontresParEquipes[] = $rencontreTemp;
                                        }
                                    } else if (in_array($nbEquipe, $equipesToCreateIDs)) {
                                        /** On créé les nouvelles rencontres des nouvelles équipes */
                                        /** L'équipe sera associée à ses rencontres à la création */
                                        $rencontreToCreate = new Rencontre($championnatActif);
                                        $rencontreToCreate
                                            ->setIdJournee($journeesKompo[$i])
                                            ->setDomicile($domicile)
                                            ->setVilleHost(false)
                                            ->setConsigne(null)
                                            ->setDateReport($journeesKompo[$i]->getDateJournee())
                                            ->setReporte(false)
                                            ->setAdversaire($adversaire)
                                            ->setValidationCompo(false)
                                            ->setExempt($isExempt);

                                        /** On indique l'adresse, complément d'adresse et site web du club adverse */
                                        if ($idClubAdversaire && !$adressesClubs[$idClubAdversaire]) {
                                            $adressesClubs[$idClubAdversaire] = $api->getClubDetails($idClubAdversaire);
                                        }

                                        $adresseAdversaire = $idClubAdversaire ? UtilController::formatAddress($adressesClubs[$idClubAdversaire]->getAdresseSalle1(), $adressesClubs[$idClubAdversaire]->getCodePostaleSalle(), $adressesClubs[$idClubAdversaire]->getVilleSalle()) : '';
                                        $complementAdresseAdversaire = $idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getNomSalle() . ' ' .
                                            $adressesClubs[$idClubAdversaire]->getAdresseSalle2() . ' ' .
                                            $adressesClubs[$idClubAdversaire]->getAdresseSalle3() : '';
                                        $rencontreToCreate
                                            ->setAdresse($adresseAdversaire)
                                            ->setSite($idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getSiteWeb() : '')
                                            ->setTelephone($idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getTelCoordo() : '')
                                            ->setComplementAdresse($complementAdresseAdversaire);

                                        /** Contrôle de l'affichage des pictogrammes dans le formulaire */
                                        $rencontreTemp['infosContact']['adresse'] = strlen(trim($adresseAdversaire));
                                        $rencontreTemp['infosContact']['complementAdresse'] = strlen(trim($complementAdresseAdversaire));
                                        $rencontreTemp['infosContact']['site'] = strlen(trim($idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getSiteWeb() : ''));
                                        $rencontreTemp['infosContact']['telephone'] = strlen(trim($idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getTelCoordo() : ''));

                                        $rencontreTemp['rencontre'] = $rencontreToCreate;
                                        $rencontreTemp['enAttenteNouvellePhase'] = false;
                                        $rencontresParEquipes[] = $rencontreTemp;
                                    }
                                } else {
                                    /** On créé les rencontres inexistantes (si champ->getNbJournees() < nb journées réèlles) */
                                    /** L'équipe sera associée à la création */
                                    $rencontreKompo = new Rencontre($championnatActif);
                                    $rencontreKompo
                                        ->setDomicile($domicile)
                                        ->setVilleHost(false)
                                        ->setConsigne(null)
                                        ->setReporte(false)
                                        ->setAdversaire($adversaire)
                                        ->setValidationCompo(false)
                                        ->setExempt($isExempt);

                                    /** On indique l'adresse, complément d'adresse et site web du club adverse */
                                    if ($idClubAdversaire && !$adressesClubs[$idClubAdversaire]) {
                                        $adressesClubs[$idClubAdversaire] = $api->getClubDetails($idClubAdversaire);
                                    }

                                    $adresseAdversaire = $idClubAdversaire ? UtilController::formatAddress($adressesClubs[$idClubAdversaire]->getAdresseSalle1(), $adressesClubs[$idClubAdversaire]->getCodePostaleSalle(), $adressesClubs[$idClubAdversaire]->getVilleSalle()) : '';
                                    $complementAdresseAdversaire = $idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getNomSalle() . ' ' .
                                        $adressesClubs[$idClubAdversaire]->getAdresseSalle2() . ' ' .
                                        $adressesClubs[$idClubAdversaire]->getAdresseSalle3() : '';
                                    $rencontreKompo
                                        ->setAdresse($adresseAdversaire)
                                        ->setSite($idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getSiteWeb() : '')
                                        ->setTelephone($idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getTelCoordo() : '')
                                        ->setComplementAdresse($complementAdresseAdversaire);

                                    /** Contrôle de l'affichage des pictogrammes dans le formulaire */
                                    $rencontreTemp['infosContact']['adresse'] = strlen(trim($adresseAdversaire));
                                    $rencontreTemp['infosContact']['complementAdresse'] = strlen(trim($complementAdresseAdversaire));
                                    $rencontreTemp['infosContact']['site'] = strlen(trim($idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getSiteWeb() : ''));
                                    $rencontreTemp['infosContact']['telephone'] = strlen(trim($idClubAdversaire ? $adressesClubs[$idClubAdversaire]->getTelCoordo() : ''));

                                    $rencontreTemp['rencontre'] = $rencontreKompo;
                                    $rencontreTemp['enAttenteNouvellePhase'] = false;
                                    $rencontresParEquipes[] = $rencontreTemp;
                                }
                            }
                        }
                    }

                    $rencontresParEquipesSorted = [];
                    foreach ($rencontresParEquipes as $key => $item) {
                        $rencontresParEquipesSorted[mb_convert_case($item['equipeESFTT'], MB_CASE_TITLE, "UTF-8")][$key] = $item;
                    }
                    $allChampionnatsReset[$championnatActif->getNom()]["matches"]["issuedSorted"] = $rencontresParEquipesSorted;
                    $allChampionnatsReset[$championnatActif->getNom()]["matches"]["issued"] = $rencontresParEquipes;
                    $allChampionnatsReset[$championnatActif->getNom()]["matches"]["kompo"] = $rencontresKompo;

                    /** On vérifie les dates **/
                    $datesIssued = [];
                    $datesMissing = [];

                    $nbJourneesChamp = $championnatActif->getNbJournees();
                    foreach ($journeesFFTT as $index => $dateFFTT) {
                        $dateJournee = (new DateTime())->setTimestamp($dateFFTT);
                        if ($index <= $nbJourneesChamp - 1) {
                            if ($journeesKompo[$index]->getDateJournee()->getTimestamp() != $dateFFTT || $journeesKompo[$index]->getUndefined()) {
                                $dateIssued = [];
                                $dateIssued['undefined'] = $journeesKompo[$index]->getUndefined();
                                $dateIssued['journee'] = $journeesKompo[$index];
                                $dateIssued['nJournee'] = $index + 1;
                                $dateIssued['dateFFTT'] = $dateJournee;
                                $datesIssued[] = $dateIssued;
                            }
                        } else {
                            $dateMissing = new Journee();
                            $dateMissing
                                ->setIdChampionnat($championnatActif)
                                ->setDateJournee($dateJournee)
                                ->setUndefined(false);

                            $datesMissing[$index + 1] = $dateMissing;
                        }
                    }

                    /** On supprime, ajoute et corrige les journées en surplus */
                    $allChampionnatsReset[$championnatActif->getNom()]["dates"]["surplus"] = count($journeesKompo) > count($journeesFFTT) && count($journeesFFTT) > 0 ? array_slice($journeesKompo, count($journeesFFTT), count($journeesKompo) - count($journeesFFTT)) : [];
                    $allChampionnatsReset[$championnatActif->getNom()]["dates"]["missing"] = $datesMissing;
                    $allChampionnatsReset[$championnatActif->getNom()]["dates"]["issued"] = $datesIssued;

                    /** Mode pré-phase où toutes les données des matches sont réinitialisées */
                    $allChampionnatsReset[$championnatActif->getNom()]["preRentree"]["finished"] = $utilController->isPreRentreeLaunchable($championnatActif);
                    /** On vérifie que la phase est terminée pour être reset **/
                    $preRentreeRencontres = $championnatActif->getRencontres()->toArray();
                    $allChampionnatsReset[$championnatActif->getNom()]["preRentree"]["compositions"] = array_filter($preRentreeRencontres, function ($compoPreRentree) {
                        return !$compoPreRentree->getIsEmpty();
                    });
                    $allChampionnatsReset[$championnatActif->getNom()]["preRentree"]["rencontres"] = array_filter($preRentreeRencontres, function ($rencontrePreRentree) {
                        return $rencontrePreRentree->getAdversaire() != null ||
                            $rencontrePreRentree->isExempt() ||
                            $rencontrePreRentree->getDomicile() != null ||
                            $rencontrePreRentree->getVilleHost() != null;
                    });
                    $allChampionnatsReset[$championnatActif->getNom()]["preRentree"]["journees"] = array_filter($championnatActif->getJournees()->toArray(), function ($journeePreRentree) {
                        return !$journeePreRentree->getUndefined();
                    });
                    $allChampionnatsReset[$championnatActif->getNom()]["preRentree"]["teams"] = array_filter($championnatActif->getEquipes()->toArray(), function ($equipePreRentree) {
                        return $equipePreRentree->getIdDivision() || $equipePreRentree->getIdPoule();
                    });
                    $allChampionnatsReset[$championnatActif->getNom()]["preRentree"]["countDispos"] = count($championnatActif->getDispos()->toArray());
                } else {
                    $allChampionnatsReset[$championnatActif->getNom()]["messageChampionnat"] = "La phase n'est pas encore lancée par la FFTT pour ce championnat";
                }
            }

            /** Si un des deux boutons de mise à jour est cliqué */
            /** Mise à jour des compétiteurs */
            if ($request->request->get('resetPlayers')) {
                /** On met à jour les compétiteurs **/
                try {
                    foreach ($joueursIssued['competition'] as $joueurIssued) {
                        if (!$joueurIssued['sameName'] || !$joueurIssued['sameClassement'])
                            $joueurIssued['joueur']
                                ->setNom($joueurIssued['nomFFTT'])
                                ->setPrenom($joueurIssued['prenomFFTT']);
                        /** On désinscris tous les joueurs du critérium fédéral si la case est cochée */
                        $joueurIssued['joueur']->setIsCritFed($request->request->get('unsubCritFedplayers') == 'on' ? false : $joueurIssued['joueur']->isCritFed());
                        $joueurIssued['joueur']->setClassementOfficiel($joueurIssued['pointsFFTT']);
                    }
                    $this->em->flush();

                    /** On retrie les compositions d'équipes */
                    foreach (array_filter($allChampionnats, function ($champ) {
                        return $champ->isCompoSorted();
                    }) as $championnatToSort) {
                        foreach ($championnatToSort->getRencontres()->toArray() as $rencontre) {
                            $rencontre->sortComposition();
                        }
                    }
                    $this->em->flush();

                } catch (Exception $exception) {
                    $this->addFlash('fail', 'Compétiteurs non mis à jour');
                }

                $this->addFlash('success', 'Joueurs mis à jour');
                return $this->redirectToRoute('backoffice.settings.update');
            } else if ($request->request->get('idChampionnat')) {
                /** Mise à jour d'un championnat (pré-phase ou phase) */
                $idChampionnat = intval($request->request->get('idChampionnat'));
                $championnatSearch = array_filter($allChampionnats, function ($champ) use ($idChampionnat) {
                    return $champ->getIdChampionnat() == $idChampionnat;
                });

                if (count($championnatSearch) == 1) {
                    $championnat = array_values($championnatSearch)[0];

                    /** Mode pré-phase lancé */
                    if ($request->request->get('preRentreeResetChampionnats')) {
                        /** On supprime toutes les dispos du championnat sélectionné **/
                        $this->championnatRepository->deleteData('Disponibilite', $idChampionnat);

                        /** On set les Journées comme étant indéfinies avec les dates maximum de la prochaine phase */
                        $maxDatesNextPhase = $this->maxDatesNextPhase(max($utilController->getLastDates($championnat)), count($allChampionnatsReset[$championnat->getNom()]["preRentree"]["journees"]));
                        foreach ($allChampionnatsReset[$championnat->getNom()]["preRentree"]["journees"] as $index => $dateKompo) {
                            $dateKompo
                                ->setLastUpdate(null)
                                ->setUndefined(true)
                                ->setDateJournee($maxDatesNextPhase[$index]);
                        }
                        $this->em->flush();

                        /** On vide les compositions d'équipe */
                        foreach ($allChampionnatsReset[$championnat->getNom()]["preRentree"]["compositions"] as $compositionKompo) {
                            $nbJoueursDiv = $compositionKompo->getIdEquipe()->getIdDivision() ? $compositionKompo->getIdEquipe()->getIdDivision()->getNbJoueurs() : $this->getParameter('nb_joueurs_default_division');
                            /** Nombre de joueurs par défaut dans une division */
                            for ($i = 0; $i < $nbJoueursDiv; $i++) {
                                $compositionKompo->setIdJoueurN($i, null);
                            }
                        }
                        $this->em->flush();

                        /** On reset les informations des rencontres */
                        foreach ($allChampionnatsReset[$championnat->getNom()]["preRentree"]["rencontres"] as $rencontreKompo) {
                            $rencontreKompo
                                ->setLastUpdate(null)
                                ->setExempt(false)
                                ->setDomicile(null)
                                ->setVilleHost(null)
                                ->setConsigne(null)
                                ->setTelephone(null)
                                ->setSite(null)
                                ->setAdresse(null)
                                ->setValidationCompo(false)
                                ->setComplementAdresse(null)
                                ->setReporte(false)
                                ->setDateReport($rencontreKompo->getIdJournee()->getDateJournee())
                                ->setAdversaire(null);
                        }
                        $this->em->flush();

                        /** On reset les lienDivision, divisions et poules des équipes */
                        foreach ($allChampionnatsReset[$championnat->getNom()]["preRentree"]["teams"] as $equipeKompo) {
                            $equipeKompo
                                ->setLastUpdate(null)
                                ->setLienDivision(null)
                                ->setIdPoule(null);
                        }
                        $this->em->flush();
                        $this->addFlash('success', 'Championnat ' . $championnat->getNom() . ' réinitialisé');
                        $joueursToContact = array_filter($this->competiteurRepository->findJoueursByRole('Competiteur', null), function ($j) use ($championnat) {
                            return in_array($championnat->getIdChampionnat(), array_map(function ($t) {
                                return $t->getIdChampionnat()->getIdChampionnat();
                            }, $j->getTitularisations()->toArray()));
                        });
                        $mails = array_map(function ($joueur) {
                            return new Address($joueur->getFirstContactableMail(), $joueur->getPrenom() . ' ' . $joueur->getNom());
                        }, $contactController->returnPlayersContactByMedia($joueursToContact)['mail']['contactables']);

                        try {
                            $str_replacers = [
                                'old' => ["[#nom_phase#]", "[#lien_division#]"],
                                'new' => [
                                    $championnat->getNom(),
                                    " <a href=\"" . $this->getParameter('url_prod') . '/journee/' . $championnat->getIdChampionnat() . "\">ici</a>"
                                ]
                            ];

                            $contactController->sendMail(
                                $mails,
                                true,
                                'Kompo - Phase terminée',
                                $this->settingsRepository->find('mail-pre-phase')->getContent(),
                                $str_replacers,
                                true);
                            $this->addFlash('success', "L'alerte de pré-phase a été envoyée");
                        } catch (Exception $e) {
                            $this->addFlash('fail', "L'alerte n'a pas pu être envoyée");
                        }
                    } /** Mode lancement de la phase */
                    else if ($request->request->get('resetChampionnats')) {
                        /** On fix les dates des journées */
                        foreach ($allChampionnatsReset[$championnat->getNom()]["dates"]["issued"] as $dateIssuedToFix) {
                            $dateIssuedToFix['journee']
                                ->setLastUpdate(null)
                                ->setDateJournee($dateIssuedToFix['dateFFTT'])
                                ->setUndefined(false);
                        }
                        $this->em->flush();
                        $this->em->refresh($championnat);

                        /** On fix le nombre de journées du championnat */
                        if ($allChampionnatsReset[$championnat->getNom()]["dates"]["realNbDates"] > 0 && $allChampionnatsReset[$championnat->getNom()]["dates"]["realNbDates"] != $championnat->getNbJournees()) {
                            $championnat
                                ->setLastUpdate(null)
                                ->setNbJournees($allChampionnatsReset[$championnat->getNom()]["dates"]["realNbDates"]);

                            /** On créé les journées inexistantes */
                            foreach ($allChampionnatsReset[$championnat->getNom()]["dates"]["missing"] as $dateMissingToCreate) {
                                $this->em->persist($dateMissingToCreate);
                            }

                            /** On supprime les journées en surplus */
                            foreach ($allChampionnatsReset[$championnat->getNom()]["dates"]["surplus"] as $dateSurplus) {
                                $this->em->remove($dateSurplus);
                                $this->em->flush();
                            }
                            $this->em->flush();
                            $this->em->refresh($championnat);
                        }

                        /** On fix les équipes */
                        foreach ($allChampionnatsReset[$championnat->getNom()]["teams"]["issued"] as $equipeIssued) {
                            /** On set la division et la poule à l'équipe */
                            $arrayDivisionPoule = $this->getDivisionPoule($equipeIssued['divisionFFTTLongName'], $equipeIssued['divisionFFTTShortName'], $equipeIssued['pouleFFTT'], $championnat, $equipeIssued['lienDivision']);
                            $equipeIssued['equipe']
                                ->setLastUpdate(null)
                                ->setLienDivision($equipeIssued['lienDivision'])
                                ->setIdDivision($arrayDivisionPoule[0])
                                ->setIdPoule($arrayDivisionPoule[1]);
                        }
                        $this->em->refresh($championnat);

                        /** On créé les équipes inexistantes */
                        foreach ($allChampionnatsReset[$championnat->getNom()]["teams"]["toCreate"] as $numero => $equipeToCreate) {
                            $arrayDivisionPoule = $this->getDivisionPoule($equipeToCreate["divisionLongName"], $equipeToCreate["divisionShortName"], $equipeToCreate["poule"], $championnat, $equipeToCreate['lienDivision']);
                            $newEquipe = new \App\Entity\Equipe();
                            $newEquipe->setIdPoule($arrayDivisionPoule[1]);
                            $newEquipe->setNumero($numero);
                            $newEquipe->setIdChampionnat($championnat);
                            $newEquipe->setIdDivision($arrayDivisionPoule[0]);
                            $newEquipe->setLienDivision($equipeToCreate["lienDivision"]);
                            $this->em->persist($newEquipe);
                            $this->em->flush();
                        }

                        $this->em->refresh($championnat);

                        /** On fix/créé les rencontres **/
                        foreach ($allChampionnatsReset[$championnat->getNom()]["matches"]["issued"] as $rencontresParEquipe) {
                            if ($rencontresParEquipe['enAttenteNouvellePhase']) {
                                /** On modifie les rencontres existantes ... */
                                $rencontresParEquipe['rencontre']
                                    ->setAdversaire($rencontresParEquipe['adversaireFFTT'])
                                    ->setDomicile($rencontresParEquipe['domicileFFTT'])
                                    ->setVilleHost(false)
                                    ->setConsigne(null)
                                    ->setExempt($rencontresParEquipe['exempt'])
                                    ->setReporte(false)
                                    ->setLastUpdate(null)
                                    ->setDateReport($rencontresParEquipe['dateReelle']);

                                /** On renseigne les informations de contacts de l'adversaire si aucun champ n'est renseigné, ou si le nom de l'adversaire est modifié */
                                if ($rencontresParEquipe['forceUpdateInfoAddresse'] || (!$rencontresParEquipe['rencontre']->getSite() && !$rencontresParEquipe['rencontre']->getAdresse() && !$rencontresParEquipe['rencontre']->getComplementAdresse() && !$rencontresParEquipe['rencontre']->gettelephone())) {
                                    $rencontresParEquipe['rencontre']
                                        ->setTelephone($rencontresParEquipe['telephone'])
                                        ->setSite($rencontresParEquipe['site'])
                                        ->setAdresse($rencontresParEquipe['adresse'])
                                        ->setComplementAdresse($rencontresParEquipe['complementAdresse']);
                                }
                            } else {
                                /** ... sinon on créé les rencontres inexistantes */
                                $nbEquipe = $rencontresParEquipe['nbEquipe'];
                                $equipeToSet = array_values(array_filter($championnat->getEquipes()->toArray(), function ($eq) use ($nbEquipe) {
                                    return $eq->getNumero() == $nbEquipe;
                                }))[0];
                                $journeeToSet = ($championnat->getJournees()->toArray())[$rencontresParEquipe['journee'] - 1];

                                $rencontresParEquipe['rencontre']
                                    ->setIdEquipe($equipeToSet)
                                    ->setIdJournee($journeeToSet)
                                    ->setDateReport($journeeToSet->getDateJournee());
                                $this->em->persist($rencontresParEquipe['rencontre']);
                            }
                            $this->em->flush();
                        }

                        $this->em->flush();
                        $this->addFlash('success', 'Championnat mis à jour');
                    }

                    /** On reset tous les last_update des entités liées au championnat */
                    $this->championnatRepository->resetLastUpdateForChampEntities($idChampionnat);

                    return $this->redirectToRoute('backoffice.settings.update');
                } else $this->addFlash('fail', 'Championnat inconnu !');
            }
        } catch (Exception $e) {
            $this->addFlash('fail', 'Mise à jour des rencontres et équipes impossible : API de la FFTT indisponible pour le moment');
            $errorMajRencontresEquipes = true;
        }

        return $this->render('backoffice/reset.html.twig', [
            'allChampionnatsReset' => $allChampionnatsReset,
            'joueursIssued' => $joueursIssued,
            'errorMajJoueurs' => $errorMajJoueurs,
            'errorMajRencontresEquipes' => $errorMajRencontresEquipes
        ]);
    }

    /**
     * @param $regex
     * @param string $value
     * @param int|null $index Par défaut le premier match
     * @return string
     */
    public function getValueFromRegex($regex, string $value, ?int $index = 1): string
    {
        preg_match($regex, $value, $matches);
        return $matches[$index];
    }

    /**
     * Détermine la phase d'une date passée en paramètre
     * @return string
     */
    public function getDatePhase(): string
    {
        $monthDate = (new DateTime())->format('n');
        if ($monthDate >= 1 && $monthDate <= 6) return "2";
        return "1";
    }

    /**
     * Retourne le numéro d'une équipe de l'API FFTT en passant son libellé en paramètre
     * @param string $equipeLibelle
     * @return int
     */
    public function getEquipeNumero(string $equipeLibelle): int
    {
        return intval(preg_replace('/\D/', '', $this->getValueFromRegex(self::REGEX_NUMERO_EQUIPE, $equipeLibelle)));
    }

    public function labelDivisionFormatter(array $libelleBrutFFTT): array
    {
        $libelleLong = $libelleBrutFFTT[self::DIVISION_PARTIE_UN] . ' ' . $libelleBrutFFTT[self::DIVISION_PARTIE_DEUX];
        /** Si le libellé doit être traduit */
        if (array_key_exists($libelleLong, self::CONVERTIONS)) {
            if (array_key_exists("indexShortName", self::CONVERTIONS[$libelleLong])) {
                return [
                    "longName" => mb_convert_case(array_key_exists("indexLongName", self::CONVERTIONS[$libelleLong]) ? $libelleBrutFFTT[self::CONVERTIONS[$libelleLong]['indexLongName']] : $libelleLong, MB_CASE_TITLE, "UTF-8"),
                    "shortName" => mb_convert_case($libelleBrutFFTT[self::CONVERTIONS[$libelleLong]['indexShortName']], MB_CASE_UPPER, "UTF-8")
                ];
            } else return self::CONVERTIONS[$libelleLong];
        }
        return [
            "longName" => mb_convert_case($libelleLong, MB_CASE_TITLE, "UTF-8"),
            "shortName" => mb_convert_case($libelleBrutFFTT[self::DIVISION_PARTIE_UN][0] . $libelleBrutFFTT[self::DIVISION_PARTIE_DEUX][0], MB_CASE_UPPER, "UTF-8")
        ];
    }

    /**
     * Retourne les dates au plus tard de la prochaine phase :
     *  - phase 1 (du 1er Juillet au 31 Décembre)
     *  - phase 2 (du 1 Janvier au 30 Juin)
     * @param DateTime $date
     * @param int $nbJournees
     * @return array
     * @throws Exception
     */
    public function maxDatesNextPhase(Datetime $date, int $nbJournees): array
    {
        if (new Datetime($date->format('Y') . '-' . '07-01') < $date && $date < new Datetime($date->format('Y') . '-' . '12-31')) {
            $lastDay = date_modify(new Datetime($date->format('Y') . '-' . '06-30'), '+1 day +1 year');
        } else $lastDay = date_modify(new Datetime($date->format('Y') . '-' . '12-31'), '+1 day');

        $firstDay = clone $lastDay;
        $firstDay->sub(new DateInterval('P' . $nbJournees . 'D'));
        $start = $firstDay;
        $end = $lastDay;
        $interval = new DateInterval('P1D');
        return iterator_to_array(new DatePeriod($start, $interval, $end));
    }

    /**
     * @param string $divisionLongName
     * @param string $divisionShortName
     * @param string|null $pouleName
     * @param Championnat $championnat
     * @param string $lienDivision
     * @return array
     */
    public function getDivisionPoule(string $divisionLongName, string $divisionShortName, ?string $pouleName, Championnat $championnat, string $lienDivision): array
    {
        /** Si la division n'existe pas, on la créé **/
        $divisionsSearch = $this->divisionRepository->findBy(['shortName' => $divisionShortName, 'idChampionnat' => $championnat->getIdChampionnat()]);
        $division = null;
        if (count($divisionsSearch) == 0) {
            $division = new Division();
            $division->setLongName($divisionLongName);
            $division->setShortName($divisionShortName);
            $division->setIdChampionnat($championnat);
            $division->setOrganismePere($this->getValueFromRegex(self::REGEX_ORGANISME_PERE, $lienDivision));
            $division->setNbJoueurs($this->getParameter('nb_joueurs_default_division'));
            $this->em->persist($division);
        } else if (count($divisionsSearch) == 1) $division = $divisionsSearch[0];

        /** Si la poule n'existe pas, on la créé **/
        $poule = null;
        if ($pouleName) {
            $poulesSearch = $this->pouleRepository->findBy(['poule' => $pouleName]);
            if (count($poulesSearch) == 0) {
                $poule = new Poule();
                $poule->setPoule($pouleName);
                $this->em->persist($poule);
            } else if (count($poulesSearch) == 1) $poule = $poulesSearch[0];
        }

        return [$division, $poule];
    }
}
