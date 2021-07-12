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
        $allChampionnats = $this->championnatRepository->findAll();
        $championnatActif = $allChampionnats[0];

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
        $messageJoueurs = (!count($joueursIssued) ? 'Tous les joueurs sont à jour' : '<span class=\'red-text text-lighten-1\'>' .count($joueursIssued) . '</span> joueurs doivent être mis à jour');

        /** Gestion des équipes */
        $equipesKompo = $this->equipeRepository->getEquipesDepartementalesApiFFTT('Départemental');
        $equipesFFTT = array_filter($api->getEquipesByClub('08951331', 'M'), function (Equipe $eq) use ($championnatActif) {
            $organisme_pere = explode('=', explode('&', $eq->getLienDivision())[2])[1];
            return $organisme_pere == $championnatActif->getLienFfttApi();
        });
        $equipesIssued = [];

        $equipesIDsFFTT = array_map(function ($equipe) {
            return intval(explode(' ', $equipe->getLibelle())[2]);
        }, $api->getEquipesByClub('08951331', 'M'));

        $equipesIDsKompo = array_map(function ($equipe) {
            return $equipe->getNumero();
        }, $equipesKompo);

        $idEquipesUnrecorded = array_merge(array_diff($equipesIDsFFTT, $equipesIDsKompo), array_diff($equipesIDsKompo, $equipesIDsFFTT));

        /** TODO On vérifie que les équipes Kompo sont recensées */
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
        $messageEquipes = (!count($equipesIssued) ? 'Toutes les équipes sont à jour' : '<span class=\'red-text text-lighten-1\'>' . count($equipesIssued) . '</span> équipes doivent être mises à jour');

        /*dump(mb_convert_case('écarté', MB_CASE_TITLE, "UTF-8"));
        dump(mb_convert_case('écarté', MB_CASE_UPPER, "UTF-8"));
        dump(mb_convert_case('écarté', MB_CASE_LOWER, "UTF-8"));*/

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
                        if (($domicile && mb_convert_case($rencontresEquipeKompo[$nbRencontre]->getAdversaire(), MB_CASE_TITLE, "UTF-8") != mb_convert_case($rencontre->getNomEquipeB(), MB_CASE_TITLE, "UTF-8")) ||
                            (!$domicile && mb_convert_case($rencontresEquipeKompo[$nbRencontre]->getAdversaire(), MB_CASE_TITLE, "UTF-8") != mb_convert_case($rencontre->getNomEquipeA(), MB_CASE_TITLE, "UTF-8")) ||
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
        $messageRencontres = (!count($rencontresParEquipes) ? 'Toutes les rencontres sont à jour' : '<span class=\'red-text text-lighten-1\'>' . count($rencontresParEquipes) . '</span> rencontres doivent être mises à jour et leurs compositions d\'équipe vidées');

        $rencontresParEquipesSorted = [];
        foreach ($rencontresParEquipes as $key => $item) {
            $rencontresParEquipesSorted[mb_convert_case($item['equipeESFTT'], MB_CASE_TITLE, "UTF-8")][$key] = $item;
        }

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

        $messageDates = (!count($datesIssued) ? 'Toutes les dates sont à jour' : '<span class=\'red-text text-lighten-1\'>' . count($datesIssued) . '</span> dates doivent être mises à jour');


        /** On vérifie que la phase est terminée pour être reset **/
        $phaseFinished = $this->getLatestDate($championnatActif) < new DateTime();

        /** On vérifie que toutes les disponiblités seront supprimées */
        $messageDisponiblites = count($championnatActif->getDispos()->toArray()) ? '<span class=\'red-text text-lighten-1\'>' . count($championnatActif->getDispos()->toArray()) . '</span> disponibilités de joueurs seront supprimées' : 'Toutes les disponibilités de joueurs ont été supprimées';






        /** Si le button est cliqué */
        if ($request->request->get('resetPhase') && $request->request->get('idChampionnat')) {
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

            /** On reset les joueurs **/
            foreach ($joueursIssued as $joueurIssued) {
                if (!$joueurIssued['sameName']) $joueurIssued['joueur']->setNom($joueurIssued['nomFFTT'])->setPrenom($joueurIssued['prenomFFTT']);
                $joueurIssued['joueur']->setClassementOfficiel($joueurIssued['pointsFFTT']);
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
            $this->addFlash('success', 'Phase réinitialisée');
            return $this->redirectToRoute('backoffice.reset.phase');
        }

        return $this->render('backoffice/reset.html.twig', [
            'phaseFinished' => $phaseFinished,
            'messageJoueurs' => $messageJoueurs,
            'messageEquipes' => $messageEquipes,
            'messageRencontres' => $messageRencontres,
            'messageDisponiblites' => $messageDisponiblites,
            'messageDates' => $messageDates,
            'joueursIssued' => $joueursIssued,
            'equipesIssued' => $equipesIssued,
            'rencontresParEquipesSorted' => $rencontresParEquipesSorted,
            'datesIssued' => $datesIssued
        ]);
    }

    function getLatestDate(Championnat $championnat): DateTime {
        return max(array_map(function($renc) {
            return $renc->getDateReport();
            }, $championnat->getRencontres()->toArray()));
    }
}
