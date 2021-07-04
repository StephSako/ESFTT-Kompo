<?php

namespace App\Controller;

use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\EquipeRepository;
use Cocur\Slugify\Slugify;
use Exception;
use FFTTApi\FFTTApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FFTTApiController extends AbstractController
{

    private $competiteurRepository;
    private $championnatRepository;
    private $equipeRepository;

    /**
     * ContactController constructor.
     */
    public function __construct(CompetiteurRepository $competiteurRepository,
                                ChampionnatRepository $championnatRepository,
                                EquipeRepository $equipeRepository)
    {
        $this->competiteurRepository = $competiteurRepository;
        $this->championnatRepository = $championnatRepository;
        $this->equipeRepository = $equipeRepository;
    }

    /**
     * @Route("/fftt_api", name="fftt_api")
     * @throws Exception
     */
    public function index(): Response
    {
        $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
        $messageJoueurs = '';
        $messageEquipes = '';

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
                    $joueursIssued[$competiteur->getIdCompetiteur()]['pointsFFTT'] = $joueur->getPoints();
                    $joueursIssued[$competiteur->getIdCompetiteur()]['nomFFTT'] = $joueur->getNom() . ' ' . $joueur->getPrenom();
                    $joueursIssued[$competiteur->getIdCompetiteur()]['sameName'] = $sameName;
                }
            }
        }
        if (!count($joueursIssued)) $messageJoueurs = 'Tous les joueurs sont à jour.';
        else if (count($joueursIssued) < count($joueursKompo)) $messageJoueurs = count($joueursIssued) . ' joueurs doivent être mis à jour';

        /** Gestion des équipes */
        $equipesKompo = $this->equipeRepository->getEquipesDepartementalesApiFFTT('Départemental');
        $equipesFFTT = $api->getEquipesByClub('08951331', 'M');
        $equipesIssued = [];

        $equipesIDsFFTT = array_map(function ($equipe) {
            return intval(explode(' ', $equipe->getLibelle())[2]);
        }, $api->getEquipesByClub('08951331', 'M'));

        $equipesIDsKompo = array_map(function ($equipe) {
            return $equipe->getNumero();
        }, $equipesKompo);

        $idEquipesUnrecorded = array_merge(array_diff($equipesIDsFFTT, $equipesIDsKompo), array_diff($equipesIDsKompo, $equipesIDsFFTT));

        /** TODO On vérifie que les équipes Kompo sont recensées ??? */

        foreach ($equipesFFTT as $equipe) {
            $idEquipeFFTT = intval(explode(' ', $equipe->getLibelle())[2]);
            $libelleDivisionEquipeFFTT = explode(' ', $equipe->getDivision());
            $pouleEquipeFFTT = $libelleDivisionEquipeFFTT[count($libelleDivisionEquipeFFTT)-1];
            $divisionEquipeFFTT = $libelleDivisionEquipeFFTT[0][0] . $libelleDivisionEquipeFFTT[1][0];
            $equipeIssued = [];
            /** On vérifie que l'équipe FFTT est recensée */
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

                $sameDivision = $equipeKompo->getIdDivision()->getShortName() == $divisionEquipeFFTT;
                $samePoule = $equipeKompo->getIdPoule()->getPoule() == mb_strtoupper($pouleEquipeFFTT);
                if (!$sameDivision || !$samePoule){
                    $equipeIssued['numeroKompo'] = $equipeKompo->getNumero();
                    $equipeIssued['divisionFFTT'] = $divisionEquipeFFTT;
                    $equipeIssued['divisionKompo'] = $equipeKompo->getIdDivision()->getShortName();
                    $equipeIssued['pouleFFTT'] = $pouleEquipeFFTT;
                    $equipeIssued['pouleKompo'] = $equipeKompo->getIdPoule()->getPoule();
                    $equipeIssued['sameDivision'] = $sameDivision;
                    $equipeIssued['samePoule'] = $samePoule;
                    array_push($equipesIssued, $equipeIssued);
                }
            }
        }
        if (!count($equipesIssued)) $messageEquipes = 'Toutes les équipes sont à jour.';
        else if (count($equipesIssued) < count($equipesKompo)) $messageEquipes = count($equipesIssued) . ' équipes doivent être mises à jour';

        /*dump(mb_convert_case('écarté', MB_CASE_TITLE, "UTF-8"));
        dump(mb_convert_case('écarté', MB_CASE_UPPER, "UTF-8"));
        dump(mb_convert_case('écarté', MB_CASE_LOWER, "UTF-8"));*/

        /** Gestion des rencontres */
        $rencontresKompo = $this->championnatRepository->getAllRencontres()['Départemental'];
        $rencontresParEquipes = [];

        $nbEquipe = 0;
        foreach ($equipesFFTT as $equipe){
            $nbRencontre = 0;
            $rencontresEquipeKompo = array_values(array_values($rencontresKompo)[$nbEquipe]);
            $rencontresFFTT = $api->getRencontrePouleByLienDivision($equipe->getLienDivision());

            foreach ($rencontresFFTT as $rencontre){
                if (str_contains($rencontre->getLien(), 'LA+FRETTE')){
                    if ($rencontresEquipeKompo[$nbRencontre]['adversaire'] != null && $rencontresEquipeKompo[$nbRencontre]['exempt'] == false && ($rencontre->getNomEquipeA() != 'Exempt' || $rencontre->getNomEquipeB() != 'Exempt')){
                        $domicile = str_contains($rencontre->getNomEquipeA(), 'LA FRETTE');
                        if (($domicile && (new Slugify())->slugify($rencontresEquipeKompo[$nbRencontre]['adversaire']) != (new Slugify())->slugify($rencontre->getNomEquipeB())) ||
                            (!$domicile && (new Slugify())->slugify($rencontresEquipeKompo[$nbRencontre]['adversaire']) != (new Slugify())->slugify($rencontre->getNomEquipeA())) ||
                            ($domicile != $rencontresEquipeKompo[$nbRencontre]['domicile']) )
                        {
                            $rencontreTemp = [];
                            $rencontreTemp['equipeESFTT'] = ($domicile ? $rencontre->getNomEquipeA() : $rencontre->getNomEquipeB());
                            $rencontreTemp['journee'] = explode(' ', $rencontre->getLibelle())[5];
                            $rencontreTemp['adversaireFFTT'] = mb_convert_case($domicile ? $rencontre->getNomEquipeB() : $rencontre->getNomEquipeA(), MB_CASE_TITLE, "UTF-8");
                            $rencontreTemp['adversaireKompo'] = $rencontresEquipeKompo[$nbRencontre]['adversaire'];
                            $rencontreTemp['domicileFFTT'] = $domicile;
                            $rencontreTemp['domicileKompo'] = $rencontresEquipeKompo[$nbRencontre]['domicile'];
                            array_push($rencontresParEquipes, $rencontreTemp);
                        }
                    }
                    $nbRencontre++;
                }
            }
            $nbEquipe++;
        }

        $rencontresParEquipesSorted = [];
        foreach ($rencontresParEquipes as $key => $item) {
            $rencontresParEquipesSorted[mb_convert_case($item['equipeESFTT'], MB_CASE_TITLE, "UTF-8")][$key] = $item;
        }

        return $this->render('backoffice/reset.html.twig', [
            'messageJoueurs' => $messageJoueurs,
            'messageEquipes' => $messageEquipes,
            'joueursIssued' => $joueursIssued,
            'equipesIssued' => $equipesIssued,
            'rencontresParEquipesSorted' => $rencontresParEquipesSorted
        ]);
    }

}
