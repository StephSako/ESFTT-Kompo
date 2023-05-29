<?php

namespace App\Controller;

use App\Entity\TournoiFFTT\Tournoi;
use App\Form\SettingsType;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteRepository;
use App\Repository\RencontreRepository;
use App\Repository\SettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FooterController extends AbstractController
{
    private $em;
    private $competiteurRepository;
    private $championnatRepository;
    private $disponibiliteRepository;
    private $rencontreRepository;
    private $settingsRepository;
    private $clientHTTP;

    /**
     * @param ChampionnatRepository $championnatRepository
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param RencontreRepository $rencontreRepository
     * @param SettingsRepository $settingsRepository
     * @param HttpClientInterface $clientHTTP
     * @param EntityManagerInterface $em
     */
    public function __construct(ChampionnatRepository $championnatRepository,
                                DisponibiliteRepository $disponibiliteRepository,
                                CompetiteurRepository $competiteurRepository,
                                RencontreRepository $rencontreRepository,
                                SettingsRepository $settingsRepository,
                                HttpClientInterface $clientHTTP,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreRepository = $rencontreRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->championnatRepository = $championnatRepository;
        $this->settingsRepository = $settingsRepository;
        $this->clientHTTP = $clientHTTP;
    }

    /**
     * @Route("/informations/{type}", name="informations")
     */
    public function getInformations(Request $request, string $type, UtilController $utilController): Response
    {
        if (!$this->get('session')->get('type')) $championnat = $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat();
        else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat());

        $setting = $this->settingsRepository->find($type);
        if (!$setting) {
            $this->addFlash('fail', 'Page d\'information inexistante');
            return $this->redirectToRoute('index.type', ['type' => $championnat->getIdChampionnat()]);
        }

        // Disponibilités du joueur
        $id = $championnat->getIdChampionnat();
        $disposJoueur = $this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idChampionnat' => $id]);
        $disposJoueurFormatted = null;
        if ($this->getUser()->isCompetiteur()) {
            $disposJoueurFormatted = [];
            foreach($disposJoueur as $dispo) {
                $disposJoueurFormatted[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
            }
        }

        $journees = $championnat->getJournees()->toArray();
        $allChampionnats = $this->championnatRepository->findAll();
        $setting = $this->settingsRepository->find($type);

        $form = null;
        $isAdmin = $this->getUser()->isAdmin();
        if ($isAdmin){
            $form = $this->createForm(SettingsType::class, $setting, [
                'show_title_form' => true
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $this->em->flush();
                    $this->addFlash('success', 'Informations modifiées');
                    return $this->redirectToRoute('informations', [
                        'type' => $type
                    ]);
                } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        $showConcernedPlayers = $setting->getDisplayTableRole();
        $concernedPlayers = $showConcernedPlayers ? $this->competiteurRepository->findJoueursByRole($showConcernedPlayers, null) : null;

        return $this->render('journee/infos.html.twig', [
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'form' => $isAdmin ? $form->createView() : null,
            'journees' => $journees,
            'journeesWithReportedRencontres' => $this->rencontreRepository->getJourneesWithReportedRencontres($championnat->getIdChampionnat())['ids'],
            'disposJoueur' => $disposJoueurFormatted,
            'HTMLContent' => $setting->getContent(),
            'showConcernedPlayers' => $showConcernedPlayers,
            'concernedPlayers' => $concernedPlayers,
            'title' => $setting->getTitle(),
            'label' => $setting->getLabel()
        ]);
    }

    /**
     * @Route("/aide", name="aide")
     */
    public function getHelpPage(UtilController $utilController): Response
    {
        if (!$this->get('session')->get('type')) $championnat = $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat();
        else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat());

        // Disponibilités du joueur
        $id = $championnat->getIdChampionnat();
        $disposJoueur = $this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idChampionnat' => $id]);
        $disposJoueurFormatted = null;
        if ($this->getUser()->isCompetiteur()) {
            $disposJoueurFormatted = [];
            foreach($disposJoueur as $dispo) {
                $disposJoueurFormatted[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
            }
        }

        $journees = $championnat->getJournees()->toArray();
        $allChampionnats = $this->championnatRepository->findAll();

        $markdown_data = file_get_contents(__DIR__ . $this->getParameter('read_md_path'));
        return $this->render('aide.html.twig', [
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'disposJoueur' => $disposJoueurFormatted,
            'journees' => $journees,
            'journeesWithReportedRencontres' => $this->rencontreRepository->getJourneesWithReportedRencontres($championnat->getIdChampionnat())['ids'],
            'markdown_data' => $markdown_data
        ]);
    }

    /**
     * @Route("/tournois", name="index.tournois")
     */
    public function getTournoisPage(UtilController $utilController): Response
    {
        if (!$this->get('session')->get('type')) $championnat = $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat();
        else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat());

        // Disponibilités du joueur
        $id = $championnat->getIdChampionnat();
        $disposJoueur = $this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idChampionnat' => $id]);
        $disposJoueurFormatted = null;
        if ($this->getUser()->isCompetiteur()) {
            $disposJoueurFormatted = [];
            foreach($disposJoueur as $dispo) {
                $disposJoueurFormatted[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
            }
        }

        $journees = $championnat->getJournees()->toArray();
        $allChampionnats = $this->championnatRepository->findAll();

        return $this->render('journee/tournois.html.twig', [
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'disposJoueur' => $disposJoueurFormatted,
            'journees' => $journees,
            'journeesWithReportedRencontres' => $this->rencontreRepository->getJourneesWithReportedRencontres($championnat->getIdChampionnat())['ids']
        ]);
    }

    /**
     * Renvoie la liste des tournois selon les paramètres envoyés
     * @Route("/liste/tournois", name="tournois", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getListeTournois(Request $request): JsonResponse {
        $errors = null;
        $tournois = [];
        try {
            $response = $this->clientHTTP->request(
                'GET',
                $this->getParameter('url_get_tournois') . '?page=1&itemsPerPage=100&order[startDate]=asc&startDate[after]=2023-05-29T15:24:57&endDate[before]=2023-12-31T23:59:58',
                [
                    'headers' => [
                        'Accept' => '*/*',
                        'Accept-Encoding' => 'gzip, deflate, br',
                        'Connection' => 'keep-alive',
                        'Referer' => $this->getParameter('referer_get_tournois'),
                        'Origin' => $this->getParameter('origin_get_tournois'),
                        'Host' => $this->getParameter('host_get_tournois'),
                    ]
                ]
            );
            $content = $response->toArray();

            dump(array_map(function ($tournoi) {
                return new Tournoi($tournoi);
            }, $content["hydra:member"]));

//            $tournois = $content;
        } catch (Exception $e) {
            dump($e);
        }

        return new JsonResponse($this->render('ajax/listeTournois.html.twig', array(
            'tournois' => 'tournois'
        ))->getContent());
    }
}