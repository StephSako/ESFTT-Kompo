<?php

namespace App\Controller\BackOffice;

use App\Entity\Championnat;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DivisionRepository;
use App\Repository\EquipeRepository;
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
    private $equipeRepository;
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
                                EquipeRepository $equipeRepository,
                                PouleRepository $pouleRepository,
                                EntityManagerInterface $em)
    {
        $this->competiteurRepository = $competiteurRepository;
        $this->championnatRepository = $championnatRepository;
        $this->equipeRepository = $equipeRepository;
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
        $joueursFFTT = $api->getJoueursByClub('08951331');
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
        $messageJoueurs = (!count($joueursIssued) ? 'Tous les joueurs sont à jour' : '<span class=\'red-text text-lighten-1\'>' .count($joueursIssued) . '</span> joueurs seront mis à jour');

        $allChampionnats = $this->championnatRepository->findAll();
        $allChampionnatsReset = []; /** Tableau où sera stockées toute la data à update par championnat */

        foreach($allChampionnats as $championnatActif){
            $allChampionnatsReset[$championnatActif->getNom()] = [];

            /** Gestion des équipes */
            $equipesKompo = $this->equipeRepository->getEquipesDepartementalesApiFFTT('Départemental');
            $equipesFFTT = array_filter($api->getEquipesByClub('08951331', 'M'), function (Equipe $eq) use ($championnatActif) {
                $organisme_pere = explode('=', explode('&', $eq->getLienDivision())[2])[1];
                return $organisme_pere == $championnatActif->getLienFfttApi();
            });

            /** On vérifie que le championnat est enregistré du côté de la FFTT en comptant les équipes */
            $allChampionnatsReset[$championnatActif->getNom()]["recorded"] = count($equipesFFTT) > 0;

            if ($allChampionnatsReset[$championnatActif->getNom()]["recorded"]){
                $allChampionnatsReset[$championnatActif->getNom()]["idChampionnat"] = $championnatActif->getIdChampionnat();

                $equipesIssued = [];

                $equipesIDsFFTT = array_map(function ($equipe) {
                    return intval(explode(' ', $equipe->getLibelle())[2]);
                }, $equipesFFTT);

                $equipesIDsKompo = array_map(function ($equipe) {
                    return $equipe->getNumero();
                }, $equipesKompo);

                $idEquipesUnrecorded = array_merge(array_diff($equipesIDsFFTT, $equipesIDsKompo), array_diff($equipesIDsKompo, $equipesIDsFFTT));

                /** TODO On vérifie à ce que les équipes Kompo soient recensées */
                foreach ($equipesFFTT as $index => $equipe) {
                    $idEquipeFFTT = $index + 1;
                    $libelleDivisionEquipeFFTT = explode(' ', $equipe->getDivision());
                    $pouleEquipeFFTT = $libelleDivisionEquipeFFTT[count($libelleDivisionEquipeFFTT)-1];
                    $divisionEquipeFFTT = $libelleDivisionEquipeFFTT[0][0] . $libelleDivisionEquipeFFTT[1][0];
                    $equipeIssued = [];

                    /** On vérifie que l'équipe FFTT est recensée */
                    //TODO Faire pour une equipe vide en passant l'objet dans le tableau
                    if (in_array($idEquipeFFTT, $idEquipesUnrecorded)){
                        $equipeIssued['numeroKompo'] = null;
                        $equipeIssued['divisionFFTT'] = $divisionEquipeFFTT;
                        $equipeIssued['divisionKompo'] = null;
                        $equipeIssued['pouleFFTT'] = $pouleEquipeFFTT;
                        $equipeIssued['pouleKompo'] = null;
                        $equipeIssued['sameDivision'] = false;
                        $equipeIssued['samePoule'] = false;
                        array_push($equipesIssued, $equipeIssued);
                    }
                    else {
                        $equipeKompo = array_values(array_filter($equipesKompo, function ($equipe) use ($idEquipeFFTT) {
                            return $equipe->getNumero() == $idEquipeFFTT;
                        }))[0];

                        $sameDivision = $equipeKompo->getIdDivision() && $equipeKompo->getIdDivision()->getShortName() == $divisionEquipeFFTT;
                        $samePoule = $equipeKompo->getIdPoule() && $equipeKompo->getIdPoule()->getPoule() == mb_strtoupper($pouleEquipeFFTT);
                        if (!$sameDivision || !$samePoule){
                            $equipeIssued['equipe'] = $equipeKompo;
                            $equipeIssued['divisionFFTT'] = $divisionEquipeFFTT;
                            $equipeIssued['pouleFFTT'] = $pouleEquipeFFTT;
                            $equipeIssued['sameDivision'] = $sameDivision;
                            $equipeIssued['samePoule'] = $samePoule;
                            array_push($equipesIssued, $equipeIssued);
                        }
                    }
                }
                $allChampionnatsReset[$championnatActif->getNom()]["teams"] = $equipesIssued;
                $allChampionnatsReset[$championnatActif->getNom()]["teamsMessage"] = (!count($equipesIssued) ? 'Toutes les équipes sont à jour' : '<span class=\'red-text text-lighten-1\'>' . count($equipesIssued) . '</span> équipes seront mises à jour');

                /** Gestion des rencontres */
                //TODO get les rencontres sous forme d'objets
                $rencontresKompo = array_filter($this->rencontreRepository->findAll(), function ($renc) use ($championnatActif) {
                    return $renc->getIdChampionnat()->getIdChampionnat() == $championnatActif->getIdChampionnat();
                });
                $rencontresParEquipes = [];
                $datesFFTT = [];

                foreach ($equipesFFTT as $index => $equipe){
                    $nbEquipe = $index + 1;
                    $nbRencontre = 0;
                    //TODO Get les rencontres selon l'équipe du bon championnat
                    $rencontresEquipeKompo = array_values(array_filter($rencontresKompo, function ($renc) use ($nbEquipe) {
                        return $renc->getIdEquipe()->getIdEquipe() == $nbEquipe;
                    }));
                    $rencontresFFTT = $api->getRencontrePouleByLienDivision($equipe->getLienDivision());

                    if (!$datesFFTT){
                        $datesFFTT = array_values(array_unique(array_map(function($renc) {
                            return strtotime($renc->getDatePrevue()->format('d-m-Y'));
                        }, $rencontresFFTT)));
                    }

                    foreach ($rencontresFFTT as $rencontre){
                        if (str_contains($rencontre->getLien(), 'LA+FRETTE')){
                            if ($rencontresEquipeKompo[$nbRencontre]->getAdversaire() != null && $rencontresEquipeKompo[$nbRencontre]->isExempt() == false && ($rencontre->getNomEquipeA() != 'Exempt' || $rencontre->getNomEquipeB() != 'Exempt')){
                                $domicile = str_contains($rencontre->getNomEquipeA(), 'LA FRETTE');
                                if (($domicile && $rencontresEquipeKompo[$nbRencontre]->getAdversaire() != mb_convert_case($rencontre->getNomEquipeB(), MB_CASE_TITLE, "UTF-8")) ||
                                    (!$domicile && $rencontresEquipeKompo[$nbRencontre]->getAdversaire()!= mb_convert_case($rencontre->getNomEquipeA(), MB_CASE_TITLE, "UTF-8")) ||
                                    ($domicile != $rencontresEquipeKompo[$nbRencontre]->getDomicile()) )
                                {
                                    $rencontreTemp = [];
                                    $rencontreTemp['rencontre'] = $rencontresEquipeKompo[$nbRencontre];
                                    $rencontreTemp['equipeESFTT'] = ($domicile ? $rencontre->getNomEquipeA() : $rencontre->getNomEquipeB());
                                    $rencontreTemp['journee'] = explode(' ', $rencontre->getLibelle())[5];
                                    $rencontreTemp['adversaireFFTT'] = mb_convert_case($domicile ? $rencontre->getNomEquipeB() : $rencontre->getNomEquipeA(), MB_CASE_TITLE, "UTF-8");
                                    $rencontreTemp['domicileFFTT'] = $domicile;
                                    $rencontreTemp['dateReelle'] = $rencontre->getDatePrevue();
                                    array_push($rencontresParEquipes, $rencontreTemp);
                                }
                            }
                            $nbRencontre++;
                        }
                    }
                }
                $allChampionnatsReset[$championnatActif->getNom()]["matchesMessage"] = (!count($rencontresParEquipes) ? 'Toutes les rencontres sont à jour' : '<span class=\'red-text text-lighten-1\'>' . count($rencontresParEquipes) . '</span> rencontres seront mises à jour et leurs compositions d\'équipe vidées');

                $rencontresParEquipesSorted = [];
                foreach ($rencontresParEquipes as $key => $item) {
                    $rencontresParEquipesSorted[mb_convert_case($item['equipeESFTT'], MB_CASE_TITLE, "UTF-8")][$key] = $item;
                }
                $allChampionnatsReset[$championnatActif->getNom()]["matches"] = $rencontresParEquipesSorted;

                /** On vérifie les dates **/
                $datesKompo = $championnatActif->getJournees()->toArray();
                $datesIssued = [];

                foreach ($datesFFTT as $index => $dateFFTT) {
                    if ($datesKompo[$index]->getDateJournee()->getTimestamp() == $dateFFTT) unset($datesFFTT[$index]);
                    else {
                        $dateIssued = [];
                        $dateIssued['journee'] = $datesKompo[$index];
                        $dateIssued['nJournee'] = $index + 1;
                        $dateIssued['dateFFTT'] = (new DateTime())->setTimestamp($dateFFTT);
                        array_push($datesIssued, $dateIssued);
                    }
                }

                $allChampionnatsReset[$championnatActif->getNom()]["dates"] = $datesIssued;
                $allChampionnatsReset[$championnatActif->getNom()]["datesMessage"] = (!count($datesIssued) ? 'Toutes les dates sont à jour' : '<span class=\'red-text text-lighten-1\'>' . count($datesIssued) . '</span> dates seront mises à jour');

                /** On vérifie que la phase est terminée pour être reset **/
                $allChampionnatsReset[$championnatActif->getNom()]["finished"] = $this->getLatestDate($championnatActif) < new DateTime();

                /** On vérifie que toutes les disponiblités seront supprimées */
                $allChampionnatsReset[$championnatActif->getNom()]["disposMessage"] = count($championnatActif->getDispos()->toArray()) ? '<span class=\'red-text text-lighten-1\'>' . count($championnatActif->getDispos()->toArray()) . '</span> disponibilités de joueurs seront supprimées' : 'Toutes les disponibilités de joueurs ont été supprimées';
            } else {
                $allChampionnatsReset[$championnatActif->getNom()]["messageChamppionnat"] = "Le championnat n'est pas enregistré pour le club pour l'instant";
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
        } else if ($request->request->get('resetChampionnats') && $request->request->get('idChampionnat')) {
            $idChampionnat = intval($request->request->get('idChampionnat'));

            /** On supprime toutes les dispos du championnat sélectionné **/
            $this->championnatRepository->deleteData('Disponibilite', $idChampionnat);

            /** On reset les rencontres **/
            foreach ($rencontresParEquipes as $rencontresParEquipe) {
                $rencontresParEquipe['rencontre']->setAdversaire($rencontresParEquipe['adversaireFFTT'])
                    ->setDomicile($rencontresParEquipe['domicileFFTT'])
                    ->setHosted(false)->setExempt(false)->setReporte(false)
                    ->setDateReport($rencontresParEquipe['dateReelle']);
            }

            /** On reset les joueurs des compos */
            foreach ($rencontresKompo as $rencontreKompo) {
                for ($i = 0; $i < $rencontreKompo->getIdEquipe()->getIdDivision()->getNbJoueurs(); $i++){
                    $rencontreKompo->setIdJoueurNToNull($i);
                }
            }

            /** On reset les dates des journées */
            //TODO Update du nb de journées du championnat si besoin
            foreach ($datesIssued as $dateIssued) {
                $dateIssued['journee']->setDateJournee($dateIssued['dateFFTT'])->setUndefined(false);
            }


            /** On reset les équipes */
            //TODO Créer les divisions/poules si inexistants
            foreach ($equipesIssued as $equipeIssued) {
                $divisionsSearch = $this->divisionRepository->findBy(['shortName' => $equipeIssued['divisionFFTT'], 'idChampionnat' => $idChampionnat]);
                $division = count($divisionsSearch) ? $this->divisionRepository->findBy(['shortName' => $equipeIssued['divisionFFTT'], 'idChampionnat' => $idChampionnat])[0] : null;

                $poulesSearch = $this->pouleRepository->findBy(['poule' => $equipeIssued['pouleFFTT']]);
                $poule = count($poulesSearch) ? $this->pouleRepository->findBy(['poule' => $equipeIssued['pouleFFTT']])[0] : null;

                $equipeIssued['equipe']->setIdDivision($division)->setIdPoule($poule);
            }

            $this->em->flush();
            $this->addFlash('success', 'Championnats réinitialisés');
            return $this->redirectToRoute('backoffice.reset.phase');
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
}
