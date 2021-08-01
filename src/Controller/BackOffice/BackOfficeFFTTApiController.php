<?php

namespace App\Controller\BackOffice;

use App\Entity\Championnat;
use App\Entity\Division;
use App\Entity\Poule;
use App\Entity\Rencontre;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DivisionRepository;
use App\Repository\PouleRepository;
use App\Repository\RencontreRepository;
use Cocur\Slugify\Slugify;
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
    private $rencontreRepository;
    private $em;
    private $divisionRepository;
    private $pouleRepository;

    /**
     * ContactController constructor.
     */
    public function __construct(CompetiteurRepository $competiteurRepository,
                                ChampionnatRepository $championnatRepository,
                                RencontreRepository $rencontreRepository,
                                DivisionRepository $divisionRepository,
                                PouleRepository $pouleRepository,
                                EntityManagerInterface $em)
    {
        $this->competiteurRepository = $competiteurRepository;
        $this->championnatRepository = $championnatRepository;
        $this->rencontreRepository = $rencontreRepository;
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
        $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));

        /** idPere par organisme */
        // Départementale Val d'Oise : 95
        // Régionale Val d'Oise : 1008
        // Nationale France : 100001

        /** Gestion des joueurs */
        $joueursKompo = $this->competiteurRepository->findBy(['visitor' => 0]);
        $joueursFFTT = $api->getJoueursByClub($this->getParameter('club_id'));
        $joueursIssued = [];

        foreach ($joueursKompo as $competiteur){
            $joueurFFTT = array_filter($joueursFFTT,
                function($joueurFFTT) use ($competiteur) {
                    return $competiteur->getLicence() == $joueurFFTT->getLicence();
                });

            if (count($joueurFFTT)){ /** Si la licence correspond bien */
                $joueur = array_values($joueurFFTT)[0];
                $sameName = (new Slugify())->slugify($competiteur->getNom().$competiteur->getPrenom()) == (new Slugify())->slugify($joueur->getNom().$joueur->getPrenom());
                if ($joueur->getPoints() != $competiteur->getClassementOfficiel() || !$sameName){ /** Si les classements ne concordent pas */
                    $joueursIssued[$competiteur->getIdCompetiteur()]['joueur'] = $competiteur;
                    $joueursIssued[$competiteur->getIdCompetiteur()]['pointsFFTT'] = intval($joueur->getPoints());
                    $joueursIssued[$competiteur->getIdCompetiteur()]['nomFFTT'] = $joueur->getNom();
                    $joueursIssued[$competiteur->getIdCompetiteur()]['prenomFFTT'] = $joueur->getPrenom();
                    $joueursIssued[$competiteur->getIdCompetiteur()]['sameName'] = $sameName;
                }
            }
        }
        $messageJoueurs = (!count($joueursIssued) ? 'Tous les joueurs sont à jour' : count($joueursIssued) . ' joueurs seront mis à jour');

        $allChampionnats = $this->championnatRepository->findAll();
        $allChampionnatsReset = []; /** Tableau où sera stockée toute la data à update par championnat */

        foreach($allChampionnats as $championnatActif){
            $allChampionnatsReset[$championnatActif->getNom()] = [];
            $datesKompo = $championnatActif->getJournees()->toArray();

            /** Gestion des équipes */
            $equipesKompo = $championnatActif->getEquipes()->toArray();
            $equipesFFTT = array_filter($api->getEquipesByClub($this->getParameter('club_id'), 'M'), function (Equipe $eq) use ($championnatActif) {
                $organisme_pere = explode('=', explode('&', $eq->getLienDivision())[2])[1];
                return $organisme_pere == $championnatActif->getLienFfttApi();
            });

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
                    $divisionEquipeFFTTLongName = mb_convert_case($libelleDivisionEquipeFFTT[0] . ' ' . $libelleDivisionEquipeFFTT[1], MB_CASE_TITLE, "UTF-8");
                    $divisionEquipeFFTTShortName = $libelleDivisionEquipeFFTT[0][0] . $libelleDivisionEquipeFFTT[1][0];
                    $equipeIssued = [];

                    /** L'équipe est recensée des 2 côtés */
                    if (!in_array($idEquipeFFTT, array_merge($equipesToCreateIDs, $equipesToDeleteIDs))){
                        $equipeKompo = array_values(array_filter($equipesKompo, function ($equipe) use ($idEquipeFFTT) {
                            return $equipe->getNumero() == $idEquipeFFTT;
                        }))[0];

                        $sameDivision = $equipeKompo->getIdDivision() && $equipeKompo->getIdDivision()->getShortName() == $divisionEquipeFFTTShortName;
                        $samePoule = $equipeKompo->getIdPoule() && $equipeKompo->getIdPoule()->getPoule() == mb_strtoupper($pouleEquipeFFTT);
                        if (!$sameDivision || !$samePoule){
                            $equipeIssued['equipe'] = $equipeKompo;
                            $equipeIssued['divisionFFTTLongName'] = $divisionEquipeFFTTLongName;
                            $equipeIssued['divisionFFTTShortName'] = $divisionEquipeFFTTShortName;
                            $equipeIssued['pouleFFTT'] = $pouleEquipeFFTT;
                            $equipeIssued['sameDivision'] = $sameDivision;
                            $equipeIssued['samePoule'] = $samePoule;
                            array_push($equipesIssued, $equipeIssued);
                        }
                    } else if (in_array($idEquipeFFTT, $equipesToCreateIDs)){
                        unset($equipesToCreate[$idEquipeFFTT]);
                        $equipesToCreate[$idEquipeFFTT]["team"]["poule"] = $pouleEquipeFFTT;
                        $equipesToCreate[$idEquipeFFTT]["team"]["divisionShortName"] = $divisionEquipeFFTTShortName;
                        $equipesToCreate[$idEquipeFFTT]["team"]["divisionLongName"] = $divisionEquipeFFTTLongName;
                        $equipesToCreate[$idEquipeFFTT]["rencontres"] = [];
                    }
                }
                $allChampionnatsReset[$championnatActif->getNom()]["teams"]["issued"] = $equipesIssued;
                $allChampionnatsReset[$championnatActif->getNom()]["teams"]["toDelete"] = $equipesToDelete;
                $allChampionnatsReset[$championnatActif->getNom()]["teams"]["toDeleteIDs"] = $equipesToDeleteIDs;
                $allChampionnatsReset[$championnatActif->getNom()]["teams"]["toCreate"] = $equipesToCreate;
                $allChampionnatsReset[$championnatActif->getNom()]["teams"]["toCreateIDs"] = $equipesToCreateIDs;
                $allChampionnatsReset[$championnatActif->getNom()]["teams"]["toUpdate"] = array_map(function($equipe) {
                    return $equipe["equipe"]->getNumero();
                }, $equipesIssued);

                /** Gestion des rencontres */
                $rencontresKompo = array_filter($this->rencontreRepository->findAll(), function ($renc) use ($championnatActif) {
                    return $renc->getIdChampionnat()->getIdChampionnat() == $championnatActif->getIdChampionnat();
                });
                $rencontresParEquipes = [];
                $datesFFTT = [];

                foreach ($equipesFFTT as $index => $equipe){
                    $nbEquipe = $index + 1;

                    if (in_array($nbEquipe, array_merge($equipesIDsCommon, $equipesToCreateIDs))){

                        $rencontresFFTT = array_values(array_filter($api->getRencontrePouleByLienDivision($equipe->getLienDivision()), function ($rencontre) {
                            return str_contains($rencontre->getNomEquipeA(), $this->getParameter('club_name')) || str_contains($rencontre->getNomEquipeB(), $this->getParameter('club_name'));
                        }));

                        if (in_array($nbEquipe, $equipesIDsCommon)) {
                            $rencontresEquipeKompo = array_values(array_filter($rencontresKompo, function ($renc) use ($nbEquipe) {
                                return $renc->getIdEquipe()->getNumero() == $nbEquipe;
                            }));
                        }

                        if (!$datesFFTT) {
                            $datesFFTT = array_values(array_unique(array_map(function ($renc) {
                                return strtotime($renc->getDatePrevue()->format('d-m-Y'));
                            }, $rencontresFFTT)));
                        }

                        foreach ($rencontresFFTT as $i => $rencontre) {
                            $isExempt = ($rencontre->getNomEquipeA() == 'Exempt' || $rencontre->getNomEquipeB() == 'Exempt');
                            if (in_array($nbEquipe, $equipesIDsCommon) && $rencontresEquipeKompo[$i]->getAdversaire() != null && $rencontresEquipeKompo[$i]->isExempt() == false && !$isExempt) {
                                $domicile = str_contains($rencontre->getNomEquipeA(), $this->getParameter('club_name'));
                                if (($domicile && $rencontresEquipeKompo[$i]->getAdversaire() != mb_convert_case($rencontre->getNomEquipeB(), MB_CASE_TITLE, "UTF-8")) ||
                                    (!$domicile && $rencontresEquipeKompo[$i]->getAdversaire() != mb_convert_case($rencontre->getNomEquipeA(), MB_CASE_TITLE, "UTF-8")) ||
                                    ($domicile != $rencontresEquipeKompo[$i]->getDomicile())) {
                                    $rencontreTemp = [];
                                    $rencontreTemp['rencontre'] = $rencontresEquipeKompo[$i];
                                    $rencontreTemp['equipeESFTT'] = $domicile ? $rencontre->getNomEquipeA() : $rencontre->getNomEquipeB();
                                    $rencontreTemp['journee'] = explode(' ', $rencontre->getLibelle())[5];
                                    $rencontreTemp['adversaireFFTT'] = mb_convert_case($domicile ? $rencontre->getNomEquipeB() : $rencontre->getNomEquipeA(), MB_CASE_TITLE, "UTF-8");
                                    $rencontreTemp['domicileFFTT'] = $domicile;
                                    $rencontreTemp['dateReelle'] = $rencontre->getDatePrevue();
                                    array_push($rencontresParEquipes, $rencontreTemp);
                                }
                            } else if (in_array($nbEquipe, $equipesToCreateIDs)) {
                                /** On créé les nouvelles rencontres de la nouvelle équipe */
                                $domicile = str_contains($rencontre->getNomEquipeA(), $this->getParameter('club_name'));
                                $rencontreToCreate = new Rencontre($championnatActif);
                                $rencontreToCreate
                                    ->setIdJournee($datesKompo[$i])
                                    ->setDomicile($domicile)
                                    ->setHosted(false)
                                    ->setDateReport($datesKompo[$i]->getDateJournee())
                                    ->setReporte(false)
                                    ->setAdversaire(!$isExempt ? mb_convert_case($domicile ? $rencontre->getNomEquipeB() : $rencontre->getNomEquipeA(), MB_CASE_TITLE, "UTF-8") : null)
                                    ->setExempt($isExempt);

                                array_push($allChampionnatsReset[$championnatActif->getNom()]["teams"]["toCreate"][$nbEquipe]["rencontres"], $rencontreToCreate);
                            }
                        }
                    }
                }
                $allChampionnatsReset[$championnatActif->getNom()]["matches"]["message"] = (!count($rencontresParEquipes) ? 'Toutes les rencontres sont à jour' : count($rencontresParEquipes) . ' rencontres seront mises à jour. Toutes les compositions d\'équipe seront vidées.');

                $rencontresParEquipesSorted = [];
                foreach ($rencontresParEquipes as $key => $item) {
                    $rencontresParEquipesSorted[mb_convert_case($item['equipeESFTT'], MB_CASE_TITLE, "UTF-8")][$key] = $item;
                }
                $allChampionnatsReset[$championnatActif->getNom()]["matches"]["issuedSorted"] = $rencontresParEquipesSorted;
                $allChampionnatsReset[$championnatActif->getNom()]["matches"]["issued"] = $rencontresParEquipes;
                $allChampionnatsReset[$championnatActif->getNom()]["matches"]["kompo"] = $rencontresKompo;

                /** On vérifie les dates **/
                $datesIssued = [];

                foreach ($datesFFTT as $index => $dateFFTT) {
                    //TODO Fix undefined dates
                    if ($datesKompo[$index]->getDateJournee()->getTimestamp() == $dateFFTT && !$datesKompo[$index]->getUndefined()) unset($datesFFTT[$index]);
                    else {
                        $dateIssued = [];
                        $dateIssued['undefined'] = $datesKompo[$index]->getUndefined();
                        $dateIssued['journee'] = $datesKompo[$index];
                        $dateIssued['nJournee'] = $index + 1;
                        $dateIssued['dateFFTT'] = (new DateTime())->setTimestamp($dateFFTT);
                        array_push($datesIssued, $dateIssued);
                    }
                }

                $allChampionnatsReset[$championnatActif->getNom()]["dates"]["issued"] = $datesIssued;
                $allChampionnatsReset[$championnatActif->getNom()]["dates"]["message"] = (!count($datesIssued) ? 'Toutes les dates sont à jour' : count($datesIssued) . ' dates seront mises à jour');

                /** On vérifie que la phase est terminée pour être reset **/
                $allChampionnatsReset[$championnatActif->getNom()]["finished"] = $this->getLatestDate($championnatActif) < new DateTime();

                /** On vérifie que toutes les disponiblités seront supprimées */
                $allChampionnatsReset[$championnatActif->getNom()]["disposMessage"] = count($championnatActif->getDispos()->toArray()) ? count($championnatActif->getDispos()->toArray()) . ' disponibilités de joueurs seront supprimées' : 'Toutes les disponibilités de joueurs ont été supprimées';
            } else {
                $allChampionnatsReset[$championnatActif->getNom()]["messageChampionnat"] = "Le championnat n'est pas enregistré pour le club pour l'instant";
            }
        }

        /** Si un des deux boutons de mise à jour est cliqué */
        /** Mise à jour des compétiteurs */
        if ($request->request->get('resetPlayers')) {
            /** On met à jour les compétiteurs **/
            foreach ($joueursIssued as $joueurIssued) {
                if (!$joueurIssued['sameName']) $joueurIssued['joueur']->setNom($joueurIssued['nomFFTT'])->setPrenom($joueurIssued['prenomFFTT']);
                $joueurIssued['joueur']->setClassementOfficiel($joueurIssued['pointsFFTT']);
            }

            $this->em->flush();
            $this->addFlash('success', 'Compétiteurs mis à jour');
            return $this->redirectToRoute('backoffice.reset.phase');
        }
        else if ($request->request->get('resetChampionnats') && $request->request->get('idChampionnat')) {
            $idChampionnat = intval($request->request->get('idChampionnat'));
            $championnatSearch = array_filter($allChampionnats, function ($champ) use ($idChampionnat) {
                return $champ->getIdChampionnat() == $idChampionnat;
            });
            if (count($championnatSearch) == 1){
                $championnat = $championnatSearch[0];

                /** On supprime toutes les dispos du championnat sélectionné **/
                $this->championnatRepository->deleteData('Disponibilite', $idChampionnat);

                /** On reset les rencontres **/
                foreach ($allChampionnatsReset[$championnat->getNom()]["matches"]["issued"] as $rencontresParEquipe) {
                    $rencontresParEquipe['rencontre']->setAdversaire($rencontresParEquipe['adversaireFFTT'])
                        ->setDomicile($rencontresParEquipe['domicileFFTT'])
                        ->setHosted(false)->setExempt(false)->setReporte(false)
                        ->setDateReport($rencontresParEquipe['dateReelle']);
                }

                /** On reset les joueurs des compos */
                foreach ($allChampionnatsReset[$championnat->getNom()]["matches"]["kompo"] as $rencontreKompo) {
                    $nbJoueursDiv = $rencontreKompo->getIdEquipe()->getIdDivision() ? $rencontreKompo->getIdEquipe()->getIdDivision()->getNbJoueurs() : 9; /** Nombre de joueurs par défaut dans une division */ //TODO In .env
                    for ($i = 0; $i < $nbJoueursDiv; $i++){
                        $rencontreKompo->setIdJoueurNToNull($i);
                    }
                }

                /** On reset les dates des journées */
                //TODO Update du nb de journées du championnat si besoin
                foreach ($allChampionnatsReset[$championnat->getNom()]["dates"]["issued"] as $dateIssued) {
                    $dateIssued['journee']->setDateJournee($dateIssued['dateFFTT'])->setUndefined(false);
                }

                /** On fix les équipes */
                foreach ($allChampionnatsReset[$championnat->getNom()]["teams"]["issued"] as $equipeIssued) {
                    /** On set la division et la poule à l'équipe */
                    $arrayDivisionPoule = $this->getDivisionPoule($equipeIssued['divisionFFTTLongName'], $equipeIssued['divisionFFTTShortName'], $equipeIssued['pouleFFTT'], $championnat);
                    $equipeIssued['equipe']->setIdDivision($arrayDivisionPoule[0])->setIdPoule($arrayDivisionPoule[1]);
                }

                /** On supprime les équipes superflux */
                foreach ($allChampionnatsReset[$championnat->getNom()]["teams"]["toDelete"] as $equipeToDelete) {
                    $this->em->remove($equipeToDelete);
                }

                /** On créer les équipes inexistantes */
                foreach ($allChampionnatsReset[$championnat->getNom()]["teams"]["toCreate"] as $numero => $equipeToCreate) {
                    $arrayDivisionPoule = $this->getDivisionPoule($equipeToCreate["team"]["divisionLongName"], $equipeToCreate["team"]["divisionShortName"], $equipeToCreate["team"]["poule"], $championnat);

                    $newEquipe = new \App\Entity\Equipe();
                    $newEquipe->setIdPoule($arrayDivisionPoule[1]);
                    $newEquipe->setNumero($numero);
                    $newEquipe->setIdChampionnat($championnat);
                    $newEquipe->setIdDivision($arrayDivisionPoule[0]);
                    $this->em->persist($newEquipe);

                    /** Creating new team's rencontres  */
                    foreach ($equipeToCreate["rencontres"] as $rencontre) {
                        $rencontre->setIdEquipe($newEquipe);
                        $this->em->persist($rencontre);
                    }
                }

                $this->em->flush();
                $this->addFlash('success', 'Phase du championnat \'' . $championnat->getNom() . '\' mise à jour');
                return $this->redirectToRoute('backoffice.reset.phase');
            }
            else $this->addFlash('fail', 'Championnat inconnu !');
        }

        return $this->render('backoffice/reset.html.twig', [
            'allChampionnatsReset' => $allChampionnatsReset,
            'messageJoueurs' => $messageJoueurs,
            'joueursIssued' => $joueursIssued
        ]);
    }

    function getLatestDate(Championnat $championnat): DateTime {
        return max(array_map(function($renc) {
            return $renc->getDateReport();
            }, $championnat->getRencontres()->toArray()));
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
            $division->setNbJoueurs(9); /** Nombre de joueurs par défaut dans une division */
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

        $this->em->flush();

        return [$division, $poule];
    }
}
