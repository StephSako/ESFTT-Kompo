<?php

namespace App\Controller;

use App\Repository\CompetiteurRepository;
use App\Repository\RencontreRepository;
use Cocur\Slugify\Slugify;
use Exception;
use FFTTApi\FFTTApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FFTTApiController extends AbstractController
{

    private $competiteurRepository;
    private $rencontreRepository;

    /**
     * ContactController constructor.
     */
    public function __construct(CompetiteurRepository $competiteurRepository,
                                RencontreRepository $rencontreRepository)
    {
        $this->competiteurRepository = $competiteurRepository;
        $this->rencontreRepository = $rencontreRepository;
    }

    /**
     * @Route("/fftt_api", name="fftt_api")
     * @throws Exception
     */
    public function index(): Response
    {
        $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
        $message = '';

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
                if ($joueur->getPoints() != $competiteur->getClassementOfficiel()){ /** Si les classements ne concordent pas */
                    $joueursIssued[$competiteur->getIdCompetiteur()]['recorded'] = true;
                    $joueursIssued[$competiteur->getIdCompetiteur()]['joueur'] = $competiteur;
                    $joueursIssued[$competiteur->getIdCompetiteur()]['pointsFFTT'] = $joueur->getPoints();
                    $joueursIssued[$competiteur->getIdCompetiteur()]['hasSamePoints'] = true;
                }
            }
            else {
                $joueursIssued[$competiteur->getIdCompetiteur()]['recorded'] = false;
                $joueursIssued[$competiteur->getIdCompetiteur()]['joueur'] = $competiteur;
                $joueursIssued[$competiteur->getIdCompetiteur()]['pointsFFTT'] = null;
                $joueursIssued[$competiteur->getIdCompetiteur()]['hasSamePoints'] = null;
            }
        }
        if (!count($joueursIssued)) $message = 'Tous les joueurs sont recensés et à jour.';
        else if (count($joueursIssued) < count($joueursKompo)) $message = count($joueursIssued) . ' joueurs doivent être mis à jour :';

        /** Gestion des équipes */
        /*
         * $libele = explode(' ', $rencontre->getLibelle());
            $poule = $libele[];
         */

        /** Gestion des rencontres */
        $rencontresKompo = $this->rencontreRepository->getOrderedRencontres()['Départemental'];
        $equipesFFTT = $api->getEquipesByClub('08951331', 'M');
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
                            $rencontreTemp['adversaireFFTT'] = ucwords(strtolower($domicile ? $rencontre->getNomEquipeB() : $rencontre->getNomEquipeA()));
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
            $rencontresParEquipesSorted[ucwords(strtolower($item['equipeESFTT']))][$key] = $item;
        }

        return $this->render('TEST.html.twig', [
            'message' => $message,
            'joueursIssued' => $joueursIssued,
            'rencontresParEquipesSorted' => $rencontresParEquipesSorted
        ]);
    }

}
