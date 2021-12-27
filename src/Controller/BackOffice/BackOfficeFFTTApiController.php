<?php

namespace App\Controller\BackOffice;

use App\Entity\Championnat;
use App\Entity\Division;
use App\Entity\Journee;
use App\Entity\Poule;
use App\Entity\Rencontre;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DivisionRepository;
use App\Repository\PouleRepository;
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
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeFFTTApiController extends AbstractController
{

    private $competiteurRepository;
    private $championnatRepository;
    private $em;
    private $divisionRepository;
    private $pouleRepository;

    /** Position des données dans les chaînes de caractères reçues de l'API */
    const JOURNEE_LABEL= 4;
    const JOURNEE_NUMBER = 1;
    const ORGANISME_PERE_LABEL = 1;
    const ORGANISME_PERE_NUMBER = 2;
    const DIVISION_PARTIE_UN = 0;
    const DIVISION_PARTIE_DEUX = 1;
    const POULE = 1;

    /**
     * ContactController constructor.
     */
    public function __construct(CompetiteurRepository $competiteurRepository,
                                ChampionnatRepository $championnatRepository,
                                DivisionRepository $divisionRepository,
                                PouleRepository $pouleRepository,
                                EntityManagerInterface $em)
    {
        $this->competiteurRepository = $competiteurRepository;
        $this->championnatRepository = $championnatRepository;
        $this->em = $em;
        $this->divisionRepository = $divisionRepository;
        $this->pouleRepository = $pouleRepository;
    }

    /**
     * @Route("/backoffice/new_phase", name="backoffice.reset.phase")
     * @throws Exception
     */
    public function index(Request $request): Response
    {
        $allChampionnatsReset = []; /** Tableau où sera stockée toute la data à update par championnat */
        $joueursIssued['competition'] = []; /** Tableau où seront stockés tous les joueurs compétiteurs devant être mis à jour */
        $joueursIssued['issuedLicences']['to_archive'] = []; /** Tableau où seront stockés les joueurs non-licenciés devant être archivés */
        $joueursIssued['issuedLicences']['to_update'] = []; /** Tableau où seront stockés les joueurs non-licenciés devant être mis à jour */
        $errorMajJoueurs = false;
        $errorMajRencontresEquipes = false;

        /** Objet API */
        $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
        try {
            /** Gestion des joueurs */
            $joueursKompo = $this->competiteurRepository->findBy(['isCompetiteur' => 1], ['nom' => 'ASC', 'prenom' => 'ASC']);
            $joueursFFTT = $api->getJoueursByClub($this->getParameter('club_id'));

            /** Gestion des joueurs non répertoriés dans l'API FFTT */
            $allLicensesFFTT = array_map(function ($joueur) { return intval($joueur->getLicence()); }, $joueursFFTT);
            $unarchivePlayers = array_filter($this->competiteurRepository->findBy(['isArchive' => 0], ['nom' => 'ASC', 'prenom' => 'ASC']), function($joueurIssuedToArchive) use ($allLicensesFFTT) {
                return !in_array($joueurIssuedToArchive->getLicence(), $allLicensesFFTT);
            });

            foreach ($unarchivePlayers as $joueurIssuedLicence){
                $joueurPotentiel = array_filter($joueursFFTT, function ($joueur) use ($joueurIssuedLicence) {
                    return str_contains($joueur->getLicence(), strval($joueurIssuedLicence->getLicence()))
                        && (new Slugify())->slugify($joueurIssuedLicence->getNom().$joueurIssuedLicence->getPrenom()) == (new Slugify())->slugify($joueur->getNom().$joueur->getPrenom());
                });

                if (!$joueurPotentiel) $joueursIssued['issuedLicences']['to_archive'][] = $joueurIssuedLicence;
                else $joueursIssued['issuedLicences']['to_update'][array_values($joueurPotentiel)[0]->getLicence()] = $joueurIssuedLicence;
            }

            foreach ($joueursKompo as $competiteur){
                $joueurFFTT = array_filter($joueursFFTT,
                    function($joueurFFTT) use ($competiteur) {
                        return $competiteur->getLicence() == $joueurFFTT->getLicence();
                    });

                if (count($joueurFFTT)){ /** Si la licence correspond bien */
                    $joueur = array_values($joueurFFTT)[0];
                    $sameName = (new Slugify())->slugify($competiteur->getNom().$competiteur->getPrenom()) == (new Slugify())->slugify($joueur->getNom().$joueur->getPrenom());
                    if (($joueur->getPoints() != $competiteur->getClassementOfficiel() || !$sameName) && intval($joueur->getPoints()) > 0){ /** Si les classements ne concordent pas */
                        $joueursIssued['competition'][$competiteur->getIdCompetiteur()]['joueur'] = $competiteur;
                        $joueursIssued['competition'][$competiteur->getIdCompetiteur()]['pointsFFTT'] = intval($joueur->getPoints());
                        $joueursIssued['competition'][$competiteur->getIdCompetiteur()]['nomFFTT'] = $joueur->getNom();
                        $joueursIssued['competition'][$competiteur->getIdCompetiteur()]['prenomFFTT'] = $joueur->getPrenom();
                        $joueursIssued['competition'][$competiteur->getIdCompetiteur()]['sameName'] = $sameName;
                    }
                }
            }
        } catch(Exception $exception) {
            $this->addFlash('fail', 'Mise à jour des joueurs compétiteurs impossible : API de la FFTT indisponible pour le moment');
            $errorMajJoueurs = true;
        }
        try {
            $allChampionnats = $this->championnatRepository->findAll();

            foreach($allChampionnats as $championnatActif){
                $allChampionnatsReset[$championnatActif->getNom()] = [];
                $journeesKompo = $championnatActif->getJournees()->toArray();

                /** Gestion des équipes */
                $equipesKompo = $championnatActif->getEquipes()->toArray();
                $equipesFFTT = array_values(array_filter($api->getEquipesByClub($this->getParameter('club_id'), 'M'), function (Equipe $eq) use ($championnatActif) {
                    $organisme_pere = explode('=', explode('&', $eq->getLienDivision())[self::ORGANISME_PERE_NUMBER])[self::ORGANISME_PERE_LABEL];
                    return $organisme_pere == $championnatActif->getLienFfttApi();
                }));

                /** On vérifie que le championnat est enregistré du côté de la FFTT en comptant les équipes */
                $allChampionnatsReset[$championnatActif->getNom()]["recorded"] = count($equipesFFTT) > 0;

                if ($allChampionnatsReset[$championnatActif->getNom()]["recorded"]){
                    $allChampionnatsReset[$championnatActif->getNom()]["idChampionnat"] = $championnatActif->getIdChampionnat();

                    $equipesIssued = [];

                    $equipesIDsFFTT = array_combine(range(0,count($equipesFFTT)-1),range(1,count($equipesFFTT)));
                    $equipesIDsKompo = array_map(function ($equipe) {
                        return $equipe->getNumero();
                    }, $equipesKompo);

                    $equipesToCreateIDs = array_diff($equipesIDsFFTT, $equipesIDsKompo);
                    $equipesToCreate = array_filter($equipesFFTT, function ($indexEquipeFFTT) use ($equipesToCreateIDs) {
                        return in_array(($indexEquipeFFTT + 1), $equipesToCreateIDs);
                    }, ARRAY_FILTER_USE_KEY);
                    // On incrémente chaque key pour avoir le numéro juste de chaque équipe à créer
                    $equipesToCreate = array_combine(array_map(function ($key) { return ++$key; }, array_keys($equipesToCreate)), $equipesToCreate);

                    $equipesToDeleteIDs = array_diff($equipesIDsKompo, $equipesIDsFFTT);
                    $equipesToDelete = array_filter($equipesKompo, function ($equipeKompo) use ($equipesToDeleteIDs) {
                        return in_array($equipeKompo->getNumero(), $equipesToDeleteIDs);
                    });

                    $equipesIDsCommon = array_intersect($equipesIDsFFTT, $equipesIDsKompo);

                    foreach ($equipesFFTT as $index => $equipe) {
                        $idEquipeFFTT = $index + 1;
                        $libelleDivisionEquipeFFTT = explode(' ', $equipe->getDivision());
                        $pouleEquipeFFTT = $libelleDivisionEquipeFFTT[count($libelleDivisionEquipeFFTT)-1];
                        $divisionEquipeFFTTLongName = mb_convert_case($libelleDivisionEquipeFFTT[self::DIVISION_PARTIE_UN] . ' ' . $libelleDivisionEquipeFFTT[self::DIVISION_PARTIE_DEUX], MB_CASE_TITLE, "UTF-8");
                        $divisionEquipeFFTTShortName = $libelleDivisionEquipeFFTT[self::DIVISION_PARTIE_UN][0] . $libelleDivisionEquipeFFTT[self::DIVISION_PARTIE_DEUX][0];
                        $equipeIssued = [];

                        /** L'équipe est recensée des 2 côtés */
                        if (!in_array($idEquipeFFTT, array_merge($equipesToCreateIDs, $equipesToDeleteIDs))){
                            $equipeKompo = array_values(array_filter($equipesKompo, function ($equipe) use ($idEquipeFFTT) {
                                return $equipe->getNumero() == $idEquipeFFTT;
                            }))[0];

                            $sameDivision = $equipeKompo->getIdDivision() && $equipeKompo->getIdDivision()->getShortName() == $divisionEquipeFFTTShortName;
                            $samePoule = $equipeKompo->getIdPoule() && $equipeKompo->getIdPoule()->getPoule() == mb_strtoupper($pouleEquipeFFTT);
                            $sameLienDivision = $equipeKompo->getLienDivision() && $equipeKompo->getLienDivision() == $equipe->getLienDivision();
                            if (!$sameDivision || !$samePoule || !$sameLienDivision){
                                $equipeIssued['equipe'] = $equipeKompo;
                                $equipeIssued['divisionFFTTLongName'] = $divisionEquipeFFTTLongName;
                                $equipeIssued['divisionFFTTShortName'] = $divisionEquipeFFTTShortName;
                                $equipeIssued['pouleFFTT'] = $pouleEquipeFFTT;
                                $equipeIssued['sameDivision'] = $sameDivision;
                                $equipeIssued['lienDivision'] = $equipe->getLienDivision();
                                $equipeIssued['samePoule'] = $samePoule;
                                $equipeIssued['sameLienDivision'] = $sameLienDivision;
                                $equipesIssued[] = $equipeIssued;
                            }
                        } else if (in_array($idEquipeFFTT, $equipesToCreateIDs)){
                            unset($equipesToCreate[$idEquipeFFTT]);
                            $equipesToCreate[$idEquipeFFTT]["poule"] = $pouleEquipeFFTT;
                            $equipesToCreate[$idEquipeFFTT]["lienDivision"] = $equipe->getLienDivision();
                            $equipesToCreate[$idEquipeFFTT]["divisionShortName"] = $divisionEquipeFFTTShortName;
                            $equipesToCreate[$idEquipeFFTT]["divisionLongName"] = $divisionEquipeFFTTLongName;
                        }
                    }
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["issued"] = $equipesIssued;
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["toDelete"] = $equipesToDelete;
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["toDeleteIDs"] = $equipesToDeleteIDs;
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["toCreate"] = $equipesToCreate;
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["toCreateIDs"] = $equipesToCreateIDs;
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["kompo"] = $equipesKompo;
                    $allChampionnatsReset[$championnatActif->getNom()]["teams"]["toUpdate"] = array_map(function($equipe) {
                        return $equipe["equipe"]->getNumero();
                    }, $equipesIssued);

                    /** Gestion des rencontres */
                    $rencontresKompo = $championnatActif->getRencontres()->toArray();
                    $rencontresParEquipes = [];
                    $journeesFFTT = [];
                    $rencontresEquipeKompo = null;

                    foreach ($equipesFFTT as $index => $equipe){
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
                                } );
                            }

                            if (!$journeesFFTT) {
                                $journeesFFTT = array_values(array_unique(array_map(function ($renc) {
                                    return strtotime($renc->getDatePrevue()->format('d-m-Y'));
                                }, $rencontresFFTT)));

                                $allChampionnatsReset[$championnatActif->getNom()]["dates"]["realNbDates"] = count($journeesFFTT);
                            }

                            foreach ($rencontresFFTT as $i => $rencontre) {
                                $isExempt = ($rencontre->getNomEquipeA() == 'Exempt' || $rencontre->getNomEquipeB() == 'Exempt');
                                $domicile = str_contains($rencontre->getNomEquipeA(), $this->getParameter('club_name'));
                                $adversaire = !$isExempt ? mb_convert_case($domicile ? $rencontre->getNomEquipeB() : $rencontre->getNomEquipeA(), MB_CASE_TITLE, "UTF-8") : null;

                                $rencontreTemp = [];
                                $rencontreTemp['equipeESFTT'] = $domicile ? $rencontre->getNomEquipeA() : $rencontre->getNomEquipeB();
                                $rencontreTemp['nbEquipe'] = $nbEquipe;
                                $rencontreTemp['journee'] = explode('?', explode(' ', $rencontre->getLibelle())[self::JOURNEE_LABEL])[self::JOURNEE_NUMBER];
                                $rencontreTemp['adversaireFFTT'] = $adversaire;
                                $rencontreTemp['exempt'] = $isExempt;
                                $rencontreTemp['domicileFFTT'] = $domicile;
                                $rencontreTemp['dateReelle'] = $rencontre->getDatePrevue();

                                if ($i <= $championnatActif->getNbJournees() - 1) {
                                    if (in_array($nbEquipe, $equipesIDsCommon)) {
                                        /** On fix les rencontres existantes des équipes existantes */
                                        if (($isExempt != $rencontresEquipeKompo[$i]->isExempt()) || (!$isExempt &&
                                            ($rencontresEquipeKompo[$i]->getAdversaire() != $rencontreTemp['adversaireFFTT']) ||
                                            ($domicile != $rencontresEquipeKompo[$i]->getDomicile()))) {

                                            $rencontreTemp['rencontre'] = $rencontresEquipeKompo[$i];
                                            $rencontreTemp['recorded'] = true;
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
                                            ->setDateReport($journeesKompo[$i]->getDateJournee())
                                            ->setReporte(false)
                                            ->setAdversaire($adversaire)
                                            ->setExempt($isExempt);

                                        $rencontreTemp['rencontre'] = $rencontreToCreate;
                                        $rencontreTemp['recorded'] = false;
                                        $rencontresParEquipes[] = $rencontreTemp;
                                    }
                                } else {
                                    /** On créé les rencontres inexistantes (si champ->getNbJournees() < nb journées réèlles) */
                                    /** L'équipe sera associée à la création */
                                    $rencontreKompo = new Rencontre($championnatActif);
                                    $rencontreKompo
                                        ->setDomicile($domicile)
                                        ->setVilleHost(false)
                                        ->setReporte(false)
                                        ->setAdversaire($adversaire)
                                        ->setExempt($isExempt);

                                    $rencontreTemp = [];
                                    $rencontreTemp['rencontre'] = $rencontreKompo;
                                    $rencontreTemp['recorded'] = false;
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

                    /** On supprime les journées en surplus */
                    $allChampionnatsReset[$championnatActif->getNom()]["dates"]["surplus"] = count($journeesKompo) > count($journeesFFTT) ? array_slice($journeesKompo, count($journeesFFTT), count($journeesKompo) - count($journeesFFTT)) : [];
                    $allChampionnatsReset[$championnatActif->getNom()]["dates"]["missing"] = $datesMissing;
                    $allChampionnatsReset[$championnatActif->getNom()]["dates"]["issued"] = $datesIssued;

                    /** Mode pré-rentrée où toutes les données des matches sont réinitialisées */
                    $allChampionnatsReset[$championnatActif->getNom()]["preRentree"]["finished"] = $this->isLaunchable($championnatActif); /** On vérifie que la phase est terminée pour être reset **/
                    $preRentreeRencontres = $championnatActif->getRencontres()->toArray();
                    $allChampionnatsReset[$championnatActif->getNom()]["preRentree"]["compositions"] = array_filter($preRentreeRencontres, function($compoPreRentree) {
                        return !$compoPreRentree->getIsEmpty();
                    });
                    $allChampionnatsReset[$championnatActif->getNom()]["preRentree"]["rencontres"] = array_filter($preRentreeRencontres, function($rencontrePreRentree) {
                        return $rencontrePreRentree->getAdversaire() != null ||
                               $rencontrePreRentree->isExempt() ||
                               $rencontrePreRentree->getDomicile() != null ||
                               $rencontrePreRentree->getVilleHost() != null;
                    });
                    $allChampionnatsReset[$championnatActif->getNom()]["preRentree"]["journees"] = array_filter($championnatActif->getJournees()->toArray(), function($journeePreRentree) {
                        return !$journeePreRentree->getUndefined();
                    });
                    $allChampionnatsReset[$championnatActif->getNom()]["preRentree"]["teams"] = array_filter($championnatActif->getEquipes()->toArray(), function($equipePreRentree) {
                        return $equipePreRentree->getIdDivision() || $equipePreRentree->getIdPoule();
                    });
                    $allChampionnatsReset[$championnatActif->getNom()]["preRentree"]["countDispos"] = count($championnatActif->getDispos()->toArray());
                } else {
                    $allChampionnatsReset[$championnatActif->getNom()]["messageChampionnat"] = "Le club n'est pas affilié à ce championnat";
                }
            }
            /** Si un des deux boutons de mise à jour est cliqué */
            /** Mise à jour des compétiteurs */
            if ($request->request->get('resetPlayers')) {
                /** On met à jour les compétiteurs **/
                try {
                    foreach ($joueursIssued['competition'] as $joueurIssued) {
                        if (!$joueurIssued['sameName'])
                            $joueurIssued['joueur']
                                ->setNom($joueurIssued['nomFFTT'])
                                ->setPrenom($joueurIssued['prenomFFTT']);
                        $joueurIssued['joueur']
                            ->setClassementOfficiel($joueurIssued['pointsFFTT']);
                    }
                    $this->em->flush();
                } catch (Exception $exception) {
                    $this->addFlash('fail', 'Compétiteurs non mis à jour');
                }

                /** On archive les joueurs non-licenciés **/
                try {
                    foreach ($joueursIssued['issuedLicences']['to_archive'] as $joueurToArchive) {
                        $joueurToArchive->setIsTotallyArchive();
                    }
                    $this->em->flush();
                } catch (Exception $exception) {
                    $this->addFlash('fail', 'Joueurs non répertoriés non archivés');
                }

                /** On met à jour les licences non répertoriées **/
                try {
                    foreach ($joueursIssued['issuedLicences']['to_update'] as $newLicence => $joueurToUpdate) {
                        $joueurToUpdate->setLicence($newLicence);
                    }
                    $this->em->flush();
                } catch (Exception $exception) {
                    $this->addFlash('fail', 'Licences non répertoriées non mises à jour');
                }

                $this->addFlash('success', 'Joueurs mis à jour');
                return $this->redirectToRoute('backoffice.reset.phase');
            }
            else if ($request->request->get('idChampionnat')) { /** Mise à jour d'un championnat (pré-rentrée ou phase) */
                $idChampionnat = intval($request->request->get('idChampionnat'));
                $championnatSearch = array_filter($allChampionnats, function ($champ) use ($idChampionnat) {
                    return $champ->getIdChampionnat() == $idChampionnat;
                });

                if (count($championnatSearch) == 1) {
                    $championnat = array_values($championnatSearch)[0];

                    /** Mode pré-rentrée lancé */
                    if ($request->request->get('preRentreeResetChampionnats')) {
                        /** On supprime toutes les dispos du championnat sélectionné **/
                        $this->championnatRepository->deleteData('Disponibilite', $idChampionnat);

                        /** On set les Journées comme étant indéfinies avec les dates maximum de la prochaine phase */
                        $maxDatesNextPhase = $this->maxDatesNextPhase(max($this->getLastDates($championnat)), count($allChampionnatsReset[$championnat->getNom()]["preRentree"]["journees"]));
                        foreach ($allChampionnatsReset[$championnat->getNom()]["preRentree"]["journees"] as $index => $dateKompo) {
                            $dateKompo
                                ->setUndefined(true)
                                ->setDateJournee($maxDatesNextPhase[$index]);
                        }
                        $this->em->flush();

                        /** On vide les compositions d'équipe */
                        foreach ($allChampionnatsReset[$championnat->getNom()]["preRentree"]["compositions"] as $compositionKompo) {
                            $nbJoueursDiv = $compositionKompo->getIdEquipe()->getIdDivision() ? $compositionKompo->getIdEquipe()->getIdDivision()->getNbJoueurs() : $this->getParameter('nb_joueurs_default_division'); /** Nombre de joueurs par défaut dans une division */
                            for ($i = 0; $i < $nbJoueursDiv; $i++){
                                $compositionKompo->setIdJoueurN($i, null);
                            }
                        }
                        $this->em->flush();

                        /** On reset les informations des rencontres */
                        foreach ($allChampionnatsReset[$championnat->getNom()]["preRentree"]["rencontres"] as $rencontreKompo) {
                            $rencontreKompo
                                ->setExempt(false)
                                ->setDomicile(null)
                                ->setVilleHost(null)
                                ->setDateReport($rencontreKompo->getIdJournee()->getDateJournee())
                                ->setAdversaire(null);
                        }
                        $this->em->flush();

                        /** On reset les lienDivision, divisions et poules des équipes */
                        foreach ($allChampionnatsReset[$championnat->getNom()]["preRentree"]["teams"] as $equipeKompo) {
                            $equipeKompo
                                ->setLienDivision(null)
                                ->setIdPoule(null);
                        }
                        $this->em->flush();
                        $this->addFlash('success', 'Championnat ' . $championnat->getNom() . ' réinitialisé');
                    }
                    /** Mode lancement de la phase */
                    else if ($request->request->get('resetChampionnats')) {
                        /** On fix les dates des journées */
                        foreach ($allChampionnatsReset[$championnat->getNom()]["dates"]["issued"] as $dateIssuedToFix) {
                            $dateIssuedToFix['journee']
                                ->setDateJournee($dateIssuedToFix['dateFFTT'])
                                ->setUndefined(false);
                        }
                        $this->em->flush();
                        $this->em->refresh($championnat);

                        /** On fix le nombre de journées du championnat */
                        if ($allChampionnatsReset[$championnat->getNom()]["dates"]["realNbDates"] != $championnat->getNbJournees()){
                            $championnat->setNbJournees($allChampionnatsReset[$championnat->getNom()]["dates"]["realNbDates"]);

                            /** On créé les journées inexistantes */
                            foreach ($allChampionnatsReset[$championnat->getNom()]["dates"]["missing"] as $dateMissingToCreate) {
                                $this->em->persist($dateMissingToCreate);
                            }

                            /** On supprime les dates en surplus */
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
                            $arrayDivisionPoule = $this->getDivisionPoule($equipeIssued['divisionFFTTLongName'], $equipeIssued['divisionFFTTShortName'], $equipeIssued['pouleFFTT'], $championnat);
                            $equipeIssued['equipe']
                                ->setLienDivision($equipeIssued['lienDivision'])
                                ->setIdDivision($arrayDivisionPoule[0])
                                ->setIdPoule($arrayDivisionPoule[self::POULE]);
                        }
                        $this->em->refresh($championnat);

                        /** On supprime les équipes superflux */
                        foreach ($allChampionnatsReset[$championnat->getNom()]["teams"]["toDelete"] as $equipeToDelete) {
                            $this->em->remove($equipeToDelete);
                            $this->em->flush();
                        }

                        /** On créé les équipes inexistantes */
                        foreach ($allChampionnatsReset[$championnat->getNom()]["teams"]["toCreate"] as $numero => $equipeToCreate) {
                            $arrayDivisionPoule = $this->getDivisionPoule($equipeToCreate["divisionLongName"], $equipeToCreate["divisionShortName"], $equipeToCreate["poule"], $championnat);

                            $newEquipe = new \App\Entity\Equipe();
                            $newEquipe->setIdPoule($arrayDivisionPoule[self::POULE]);
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
                            if ($rencontresParEquipe['recorded']) {
                                /** On modifie les rencontres existantes ... */
                                $rencontresParEquipe['rencontre']
                                    ->setAdversaire($rencontresParEquipe['adversaireFFTT'])
                                    ->setDomicile($rencontresParEquipe['domicileFFTT'])
                                    ->setVilleHost(false)
                                    ->setExempt($rencontresParEquipe['exempt'])
                                    ->setReporte(false)
                                    ->setDateReport($rencontresParEquipe['dateReelle']);
                            } else {
                                /** ... sinon on créé les rencontres inexistantes */
                                $nbEquipe = $rencontresParEquipe['nbEquipe'];
                                $equipeToSet = array_values(array_filter($championnat->getEquipes()->toArray(), function($eq) use ($nbEquipe) {
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
                    return $this->redirectToRoute('backoffice.reset.phase');
                } else $this->addFlash('fail', 'Championnat inconnu !');
            }
        } catch (Exception $exception) {
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
     * Retourne un message selon que le championnat est terminé ou pas pour autoriser la pré-rentrée
     * @param Championnat $championnat
     * @return array
     */
    function isLaunchable(Championnat $championnat): array {
        $latestDate = $this->getLastDates($championnat);
        if (!count($latestDate)) return ['launchable' => false, 'message' => 'Ce championnat n\'a pas d\'équipes enregistrées'];
        else if (max($latestDate) < new DateTime()) return ['launchable' => true, 'message' => 'La phase est terminée et la pré-rentrée prête à être lancée'];
        else return ['launchable' => false, 'message' => 'La phase n\'est pas terminée pour lancer la pré-rentrée'];
    }

    /**
     * Retourne les dates au plus tard de toutes les recontres du championnat sélectionné
     * @param Championnat $championnat
     * @return array
     */
    function getLastDates(Championnat $championnat): array
    {
        return array_unique(array_map(function(Rencontre $renc) {
            return max([$renc->isReporte() ? $renc->getDateReport() : null, $renc->getIdJournee()->getDateJournee()]);
        }, $championnat->getRencontres()->toArray()), SORT_REGULAR);
    }

    /**
     * @param string $divisionLongName
     * @param string $divisionShortName
     * @param string $pouleName
     * @param Championnat $championnat
     * @return array
     */
    function getDivisionPoule(string $divisionLongName, string $divisionShortName, string $pouleName, Championnat $championnat): array
    {
        /** Si la division n'existe pas, on la créé **/
        $divisionsSearch = $this->divisionRepository->findBy(['shortName' => $divisionShortName, 'idChampionnat' => $championnat->getIdChampionnat()]);
        $division = null;
        if (count($divisionsSearch) == 0){
            $division = new Division();
            $division->setLongName($divisionLongName);
            $division->setShortName($divisionShortName);
            $division->setIdChampionnat($championnat);
            $division->setNbJoueurs($this->getParameter('nb_joueurs_default_division'));
            $this->em->persist($division);
        }
        else if (count($divisionsSearch) == 1) $division = $divisionsSearch[0];

        /** Si la poule n'existe pas, on la créé **/
        $poulesSearch = $this->pouleRepository->findBy(['poule' => $pouleName]);
        $poule = null;
        if (count($poulesSearch) == 0){
            $poule = new Poule();
            $poule->setPoule($pouleName);
            $this->em->persist($poule);
        }
        else if (count($poulesSearch) == 1) $poule = $poulesSearch[0];

        return [$division, $poule];
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
    function maxDatesNextPhase(Datetime $date, int $nbJournees): array
    {
        if (new Datetime($date->format('Y') . '-' . '07-01') < $date && $date < new Datetime($date->format('Y') . '-' . '12-31')) {
            $lastDay = date_modify(new Datetime($date->format('Y') . '-' . '06-30'),'+1 day +1 year');
        } else $lastDay = date_modify(new Datetime($date->format('Y') . '-' . '12-31'),'+1 day');

        $firstDay = clone $lastDay;
        $firstDay->sub(new DateInterval('P' . $nbJournees . 'D'));
        $start = $firstDay;
        $end = $lastDay;
        $interval = new DateInterval('P1D');
        return iterator_to_array(new DatePeriod($start, $interval, $end));
    }
}
