<?php

namespace App\Controller;

use App\Repository\CompetiteurRepository;
use Exception;
use FFTTApi\FFTTApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FFTTApiController extends AbstractController
{

    private $competiteurRepository;

    /**
     * ContactController constructor.
     */
    public function __construct(CompetiteurRepository $competiteurRepository)
    {
        $this->competiteurRepository = $competiteurRepository;
    }

    /**
     * @Route("/fftt_api", name="fftt_api")
     * @throws Exception
     */
    public function index(): Response
    {
        $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
        $joueursKompo = $this->competiteurRepository->findBy(['visitor' => 0]);
        $joueursFFTT = $api->getJoueursByClub('08951331');
        $joueursIssued = [];
        $message = '';

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

        // dump($api->getEquipesByClub('08951331'));
        return $this->render('TEST.html.twig', [
            'message' => $message,
            'joueursIssued' => $joueursIssued
        ]);
    }
}
