<?php

namespace App\Controller;

use App\Entity\Fichier;
use App\Entity\TournoiFFTT\Tableau;
use App\Entity\TournoiFFTT\Tournoi;
use App\Form\SettingsType;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteRepository;
use App\Repository\FichierRepository;
use App\Repository\RencontreRepository;
use App\Repository\SettingsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Csv\Reader;
use League\Csv\Statement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    const EXCEl_DEPARTEMENTS_CHAMP_CODE_DEPARTEMENT = 0;
    const EXCEl_DEPARTEMENTS_CHAMP_CODE_REGION = 2;
    private $em;
    private $competiteurRepository;
    private $championnatRepository;
    private $disponibiliteRepository;
    private $rencontreRepository;
    private $settingsRepository;
    private $clientHTTP;
    private $fichierRepository;

    /**
     * @param ChampionnatRepository $championnatRepository
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param FichierRepository $fichierRepository
     * @param RencontreRepository $rencontreRepository
     * @param SettingsRepository $settingsRepository
     * @param HttpClientInterface $clientHTTP
     * @param EntityManagerInterface $em
     */
    public function __construct(ChampionnatRepository   $championnatRepository,
                                DisponibiliteRepository $disponibiliteRepository,
                                CompetiteurRepository   $competiteurRepository,
                                FichierRepository       $fichierRepository,
                                RencontreRepository     $rencontreRepository,
                                SettingsRepository      $settingsRepository,
                                HttpClientInterface     $clientHTTP,
                                EntityManagerInterface  $em)
    {
        $this->em = $em;
        $this->rencontreRepository = $rencontreRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->championnatRepository = $championnatRepository;
        $this->settingsRepository = $settingsRepository;
        $this->clientHTTP = $clientHTTP;
        $this->fichierRepository = $fichierRepository;
    }

    /**
     * @Route("/informations/{type}", name="informations")
     * @param Request $request
     * @param string $type
     * @param UtilController $utilController
     * @return Response
     */
    public function getInformations(Request $request, string $type, UtilController $utilController): Response
    {
        $checkIsBackOffice = $utilController->keepBackOfficeNavbar('informations', ['type' => $type], $request->query->get('backoffice'));
        if ($checkIsBackOffice['issue']) return $checkIsBackOffice['redirect'];
        else $isBackoffice = $request->query->get('backoffice') == 'true';

        $allChampionnats = $championnat = $disposJoueurFormatted = $journees = $journeesWithReportedRencontres = null;
        if (!$isBackoffice) {
            $nextJourneeToPlayAllChampsIdChamp = $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat();
            if (!$this->get('session')->get('type')) $championnat = $nextJourneeToPlayAllChampsIdChamp;
            else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $nextJourneeToPlayAllChampsIdChamp);

            $setting = $this->settingsRepository->find($type);
            if (!$setting) {
                $this->addFlash('fail', "Page d'information inexistante");
                return $this->redirectToRoute('index.type', ['type' => $championnat->getIdChampionnat()]);
            }

            // Disponibilités du joueur
            $id = $championnat->getIdChampionnat();
            $disposJoueur = $this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idChampionnat' => $id]);
            if ($this->getUser()->isCompetiteur()) {
                $disposJoueurFormatted = [];
                foreach ($disposJoueur as $dispo) {
                    $disposJoueurFormatted[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
                }
            }

            $journees = $championnat->getJournees()->toArray();
            $allChampionnats = $this->championnatRepository->getAllChampionnats(true);
            $journeesWithReportedRencontres = $this->rencontreRepository->getJourneesWithReportedRencontres($championnat->getIdChampionnat())['ids'];
        }

        $setting = $this->settingsRepository->find($type);
        $form = null;
        $isAdmin = $this->getUser()->isAdmin();
        if ($isAdmin) {
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
                } else $this->addFlash('fail', "Le formulaire n'est pas valide");
            }
        }

        $showConcernedPlayers = $setting->getDisplayTableRole();
        $concernedPlayers = $showConcernedPlayers ? $this->competiteurRepository->findJoueursByRole($showConcernedPlayers, null) : null;

        $fichiersUploades = $this->fichierRepository->findBy(['setting' => $type]);

        return $this->render('journee/infos.html.twig', [
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'form' => $isAdmin ? $form->createView() : null,
            'journees' => $journees,
            'journeesWithReportedRencontres' => $journeesWithReportedRencontres,
            'disposJoueur' => $disposJoueurFormatted,
            'HTMLContent' => $setting->getContent(),
            'typePage' => $setting->getType(),
            'idSetting' => $setting->getId(),
            'showConcernedPlayers' => $showConcernedPlayers,
            'concernedPlayers' => $concernedPlayers,
            'title' => $setting->getTitle(),
            'label' => $setting->getLabel(),
            'isBackOffice' => $isBackoffice,
            'fichiersUploades' => $fichiersUploades
        ]);
    }

    /**
     * Appelée depuis un appel Ajax et upload un fichier depuis une page d'information
     * @Route("/informations/{type}/upload-file", name="informations.file.upload", methods={"POST"})
     * @param Request $request
     * @param string $type
     * @return JsonResponse
     */
    public function readImportFile(Request $request, string $type): JsonResponse
    {
        dump($type);
        try {
            $setting = $this->settingsRepository->find($type);
            if ($setting === null) throw new Exception('Information inexistante', 1234);

            /** @var UploadedFile $file */
            $file = $request->files->get('uploadFile');
            $fileToUpload = new Fichier($setting, $file->getClientOriginalName());
            $this->em->persist($fileToUpload);
            $this->em->flush();
            dump($fileToUpload);
        } catch (Exception $e) {
            dump($e);
        }
        return new JsonResponse('');
    }

    /**
     * @Route("/aide", name="aide")
     * @param Request $request
     * @param UtilController $utilController
     * @return Response
     */
    public function getHelpPage(Request $request, UtilController $utilController): Response
    {
        $checkIsBackOffice = $utilController->keepBackOfficeNavbar('aide', [], $request->query->get('backoffice'));
        if ($checkIsBackOffice['issue']) return $checkIsBackOffice['redirect'];
        else $isBackoffice = $request->query->get('backoffice') == 'true';

        $allChampionnats = $championnat = $disposJoueurFormatted = $journees = $journeesWithReportedRencontres = null;
        if (!$isBackoffice) {
            $nextJourneeToPlayAllChampsIdChamp = $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat();
            if (!$this->get('session')->get('type')) $championnat = $nextJourneeToPlayAllChampsIdChamp;
            else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $nextJourneeToPlayAllChampsIdChamp);

            // Disponibilités du joueur
            $id = $championnat->getIdChampionnat();
            $disposJoueur = $this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idChampionnat' => $id]);
            if ($this->getUser()->isCompetiteur()) {
                $disposJoueurFormatted = [];
                foreach ($disposJoueur as $dispo) {
                    $disposJoueurFormatted[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
                }
            }

            $journees = $championnat->getJournees()->toArray();
            $allChampionnats = $this->championnatRepository->getAllChampionnats();
            $journeesWithReportedRencontres = $this->rencontreRepository->getJourneesWithReportedRencontres($championnat->getIdChampionnat())['ids'];
        }

        $markdown_data = file_get_contents(__DIR__ . $this->getParameter('read_md_path'));
        return $this->render('aide.html.twig', [
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'disposJoueur' => $disposJoueurFormatted,
            'journees' => $journees,
            'journeesWithReportedRencontres' => $journeesWithReportedRencontres,
            'markdown_data' => $markdown_data,
            'isBackOffice' => $isBackoffice
        ]);
    }

    /**
     * @Route("/tournois", name="index.tournois")
     * @param UtilController $utilController
     * @param Request $request
     * @return Response
     */
    public function getTournoisPage(UtilController $utilController, Request $request): Response
    {
        $checkIsBackOffice = $utilController->keepBackOfficeNavbar('aide', [], $request->query->get('backoffice'));
        if ($checkIsBackOffice['issue']) return $checkIsBackOffice['redirect'];
        else $isBackoffice = $request->query->get('backoffice') == 'true';

        $allChampionnats = $championnat = $disposJoueurFormatted = $journees = $journeesWithReportedRencontres = null;
        if (!$isBackoffice) {
            $nextJourneeToPlayAllChampsIdChamp = $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat();
            if (!$this->get('session')->get('type')) $championnat = $nextJourneeToPlayAllChampsIdChamp;
            else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $nextJourneeToPlayAllChampsIdChamp);

            // Disponibilités du joueur
            $id = $championnat->getIdChampionnat();
            $disposJoueur = $this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idChampionnat' => $id]);
            if ($this->getUser()->isCompetiteur()) {
                $disposJoueurFormatted = [];
                foreach ($disposJoueur as $dispo) {
                    $disposJoueurFormatted[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
                }
            }

            $journees = $championnat->getJournees()->toArray();
            $allChampionnats = $this->championnatRepository->findAll();
            $journeesWithReportedRencontres = $this->rencontreRepository->getJourneesWithReportedRencontres($championnat->getIdChampionnat())['ids'];
        }

        return $this->render('journee/tournois.html.twig', [
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'disposJoueur' => $disposJoueurFormatted,
            'journees' => $journees,
            'journeesWithReportedRencontres' => $journeesWithReportedRencontres,
            'isBackOffice' => $isBackoffice
        ]);
    }

    /**
     * Renvoie la liste des tournois selon les paramètres envoyés
     * @Route("/tournois/liste", name="tournois.liste", methods={"GET"})
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getListeTournois(): JsonResponse
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');
        date_default_timezone_set('Europe/Paris');

        $tournois = $tournoisParMois = [];
        try {
            $response = $this->clientHTTP->request(
                'GET',
                $this->getParameter('url_get_tournois') . '/api/tournament_requests?page=1&itemsPerPage=100&order[startDate]=asc&startDate[after]=' . date('Y-m-d', strtotime('-7 days')) . 'T00:00:00&endDate[before]=' . date('Y-m-d\T', strtotime('+1 year')) . '23:59:58',
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

            $regions = $this->getListDepartementsByCodeRegion();
            $codeRegionClub = array_key_first(array_filter($regions, function ($region) {
                return in_array(intval(substr($this->getParameter('club_id'), 2, 2)), $region);
            }));

            $content = $response->toArray();

            $tournois = array_map(function ($tournoi) use ($codeRegionClub, $regions) {
                try {
                    $codeRegionTournoi = array_key_first(array_filter($regions, function ($region) use ($tournoi) {
                        return in_array(intval(substr($tournoi['address']['postalCode'], 0, 2)), $region);
                    }));
                    $departementClub = intval(substr($this->getParameter('club_id'), 2, 2));
                } catch (Exception $e) {
                    $codeRegionTournoi = $departementClub = null;
                }
                return new Tournoi($tournoi, $codeRegionClub, $codeRegionTournoi, $departementClub);
            }, $content["hydra:member"]);

            // On récupère les tournois qui ont également commencé avant aujourd'hui mais qui finissent aujourd'hui ou plus tard
            $today = (new DateTime())->setTime(0, 0)->getTimestamp();
            $tournois = array_filter($tournois, function (Tournoi $tournoi) use ($today) {
                return $tournoi->getEndDate()->getTimestamp() >= $today;
            });

            // On découpe les tournois par mois
            foreach ($tournois as $tournoi) {
                $mois = $tournoi->getStartDate()->format('m') . '/01/2000';
                $tournoisParMois[$mois][] = $tournoi;
            }
        } catch (Exception $e) {
        }

        return new JsonResponse($this->render('ajax/tournois/listeTournois.html.twig', array(
            'tournoisParMois' => $tournoisParMois,
            'dateStart' => (new DateTime())->format('d/m/Y'),
            'dateEnd' => date('d/m/Y', strtotime('+1 year'))
        ))->getContent());
    }

    /**
     * Récupérer la liste des départements par code région
     * @return array
     */
    public function getListDepartementsByCodeRegion(): array
    {
        try {
            $csv = Reader::createFromPath(__DIR__ . $this->getParameter('departements_path'));
            $records = (new Statement())->process($csv);
            $regions = [];
            foreach ($records as $i => $record) {
                if ($i > 0) { // On ignore la 1ère ligne des en-têtes
                    $codeRegion = intval($record[self::EXCEl_DEPARTEMENTS_CHAMP_CODE_REGION]);
                    $codeDepartement = intval($record[self::EXCEl_DEPARTEMENTS_CHAMP_CODE_DEPARTEMENT]);
                    if (array_key_exists($codeRegion, $regions)) $regions[$codeRegion][] = $codeDepartement;
                    else $regions[$codeRegion] = [$codeDepartement];
                }
            }
            return $regions;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Renvoie la liste des tableaux d'un tournoi selon l'id du tournoi passé en paramètre
     * @Route("/tournois/liste/tableaux", name="tournois.liste.tableaux", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getDetailsTableauxTournoi(Request $request): JsonResponse
    {
        $tableaux = [];
        $hasDocuments = false;
        try {
            $response = $this->clientHTTP->request(
                'GET',
                $this->getParameter('url_get_tournois') . '/api/tournament_requests/' . $request->get('id'),
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
            $hasDocuments = !($content['engagmentSheet'] == null && $content['rules'] == null);

            $tableaux = array_map(function ($tournoi) {
                return new Tableau($tournoi);
            }, $content["tables"]);
        } catch (Exception $e) {
        }

        $tableauxPerDay = [];
        foreach ($tableaux as $tableau) {
            $tableauxPerDay[mb_convert_case(utf8_encode(strftime('%A', $tableau->getDate()->getTimestamp())), MB_CASE_TITLE, "UTF-8")][] = $tableau;
        }

        return new JsonResponse($this->render('ajax/tournois/listeTableauxTournoi.html.twig', array(
            'tableaux' => $tableauxPerDay,
            'hasDocuments' => $hasDocuments
        ))->getContent());
    }
}