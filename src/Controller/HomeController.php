<?php

namespace App\Controller;

use App\Entity\Championnat;
use App\Form\RencontreType;
use App\Form\SettingsType;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteRepository;
use App\Repository\RencontreRepository;
use App\Repository\SettingsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FFTTApi\FFTTApi;
use FFTTApi\Model\VirtualPoints;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $em;
    private $competiteurRepository;
    private $championnatRepository;
    private $disponibiliteRepository;
    private $rencontreRepository;
    private $invalidSelectionController;
    private $settingsRepository;

    /**
     * @param ChampionnatRepository $championnatRepository
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param RencontreRepository $rencontreRepository
     * @param SettingsRepository $settingsRepository
     * @param InvalidSelectionController $invalidSelectionController
     * @param EntityManagerInterface $em
     */
    public function __construct(ChampionnatRepository $championnatRepository,
                                DisponibiliteRepository $disponibiliteRepository,
                                CompetiteurRepository $competiteurRepository,
                                RencontreRepository $rencontreRepository,
                                SettingsRepository $settingsRepository,
                                InvalidSelectionController $invalidSelectionController,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreRepository = $rencontreRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->championnatRepository = $championnatRepository;
        $this->invalidSelectionController = $invalidSelectionController;
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * @param Championnat $championnat
     * @return int
     */
    public function getJourneeToPlay(Championnat $championnat): int
    {
        $journees = $championnat->getJournees()->toArray();
        $IDsJournees = array_map(function($j) {
            return $j->getIdJournee();
        }, $journees);
        $idJournee = 0;

        /** Récupérer la prochaine journée à jouer */
        while ($idJournee < $championnat->getNbJournees() - 1 && !$journees[$idJournee]->getUndefined() && (int) (new DateTime())->diff($journees[$idJournee]->getDateJournee())->format('%R%a') < 0){
            $idJournee++;
        }

        return $IDsJournees[$idJournee];
    }

    /**
     * @Route("/", name="index")
     * @throws Exception
     */
    public function indexAction(): Response
    {
        if (!$this->get('session')->get('type')) $championnat = $this->championnatRepository->getFirstChampionnatAvailable();
        else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $this->championnatRepository->getFirstChampionnatAvailable());

        if ($championnat){
            return $this->redirectToRoute('journee.show', [
                'type' => $championnat->getIdChampionnat(),
                'id' => $this->getJourneeToPlay($championnat)
            ]);
        } else return $this->render('journee/noChamp.html.twig', [
            'allChampionnats' => null,
            'journees' => null
        ]);
    }

    /**
     * @Route("/journee/{type}", name="index.type", requirements={"type"="\d+"})
     * @param int $type
     * @return Response
     * @throws Exception
     */
    public function indexTypeAction(int $type): Response
    {
        $championnat = ($this->championnatRepository->find($type) ?: $this->championnatRepository->getFirstChampionnatAvailable());
        if ($championnat) {
            return $this->redirectToRoute('journee.show', [
                'type' => $championnat->getIdChampionnat(),
                'id' => $this->getJourneeToPlay($championnat)
            ]);
        } else return $this->redirectToRoute('index', []);
    }

    /**
     * @param int $type
     * @param int $id
     * @return Response
     * @throws Exception
     * @Route("/journee/{type}/{id}", name="journee.show", requirements={"type"="\d+", "id"="\d+"})
     */
    public function journee(int $type, int $id): Response
    {
        if (!($championnat = $this->championnatRepository->find($type))) return $this->redirectToRoute('index');
        $journees = $championnat->getJournees()->toArray();

        if (!in_array($id, array_map(function ($journee){ return $journee->getIdJournee(); }, $journees))){
            $this->addFlash('fail', 'Journée inexistante pour ce championnat');
            return $this->redirectToRoute('index.type', ['type' => $type]);
        }
        $journee = array_values(array_filter($journees, function($journee) use ($id) { return ($journee->getIdJournee() == $id ? $journee : null); }))[0];

        $this->get('session')->set('type', $type);

        // Objet Disponibilité du joueur
        $dispoJoueur = $this->disponibiliteRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]);

        // Joueurs ayant déclaré leur disponibilité
        $joueursDeclares = $this->disponibiliteRepository->findJoueursDeclares($id, $type);

        // Joueurs n'ayant pas déclaré leur disponibilité
        $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, $type);

        // Compositions d'équipe
        $compos = $this->rencontreRepository->getRencontres($id, $type);

        // Numero de la journée
        $numJournee = array_search($journee, $journees)+1;

        // Joueurs sélectionnées
        $selectedPlayers = $this->rencontreRepository->getSelectedPlayers($compos);

        // Nombre maximal de joueurs pour les compos du championnat sélectionné
        $nbTotalJoueurs = array_sum(array_map(function($compo) use ($type) {
            return $compo->getIdEquipe()->getIdDivision()->getNbJoueurs();
        }, $compos));

        // Numéros des équipes valides pour le brûlage
        $equipes = $championnat->getEquipes()->toArray();
        $equipesBrulage = array_map(function($equipe){
            return $equipe->getNumero();
        }, array_filter($equipes, function($equipe){
            return $equipe->getIdDivision() != null;
        }));
        sort($equipesBrulage, SORT_NUMERIC);
        $idEquipesVisuel = array_slice($equipesBrulage, 1, count($equipesBrulage));
        $idEquipesBrulage = array_slice($equipesBrulage, 0, count($equipesBrulage) - 1);

        // Nombre minimal critique de joueurs pour les compos du championnat
        $nbMinJoueurs = array_sum(array_map(function($compo) use ($type) {
            return $compo->getIdEquipe()->getIdDivision()->getNbJoueurs() - 1;
        }, $compos));

        // Equipes sans divisions affiliées
        $equipesSansDivision = array_map(function($equipe){
            return $equipe->getNumero();
        }, array_filter($equipes, function($equipe){
            return $equipe->getIdDivision() == null;
        }));

        $nbDispos = count(array_filter($joueursDeclares, function($dispo){return $dispo->getDisponibilite();}));

        // Si l'utilisateur actuel est disponible pour la journée actuelle
        $disponible = ($dispoJoueur ? ($dispoJoueur->getDisponibilite() ? 1 : 0) : -1);

        // Si l'utilisateur est sélectionné pour la journée actuelle
        $selectionArray = array_values(array_filter(array_map(function($compo) {
            return in_array($this->getUser()->getIdCompetiteur(), $compo->getSelectedPlayers()) ? $compo : null;
        }, $compos), function($compoFiltree){
            return $compoFiltree != null;
        }));

        $selection = count($selectionArray) ? $selectionArray[0]->getIdEquipe()->getNumero() : null;

        $allChampionnats = $this->championnatRepository->findAll();
        $allDisponibilites = $this->competiteurRepository->findAllDisposRecapitulatif($allChampionnats);

        // Brûlages des joueurs
        $divisions = $championnat->getDivisions()->toArray();
        $brulages = $divisions ? $this->competiteurRepository->getBrulages($type, $id, $idEquipesBrulage, max(array_map(function($division){return $division->getNbJoueurs();}, $divisions))) : null;

        // Récupération des points virtuels de l'utilisateur
        $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
        $virtualPoints = new VirtualPoints(0, 0);
        if ($this->getUser()->getLicence()) {
            try {
                $virtualPoints = $api->getJoueurVirtualPoints($this->getUser()->getLicence());
            } catch (Exception $e) {}
        }

        return $this->render('journee/index.html.twig', [
            'journee' => $journee,
            'idJournee' => $numJournee,
            'equipesSansDivision' => $equipesSansDivision,
            'journees' => $journees,
            'nbTotalJoueurs' => $nbTotalJoueurs,
            'nbMinJoueurs' => $nbMinJoueurs,
            'allChampionnats' => $allChampionnats,
            'selection' => $selection,
            'championnat' => $championnat,
            'compos' => $compos,
            'idEquipes' => $idEquipesVisuel,
            'selectedPlayers' => $selectedPlayers,
            'dispos' => $joueursDeclares,
            'disponible' => $disponible,
            'joueursNonDeclares' => $joueursNonDeclares,
            'dispoJoueur' => $dispoJoueur ? $dispoJoueur->getIdDisponibilite() : -1,
            'nbDispos' => $nbDispos,
            'brulages' => $brulages,
            'allDisponibilites' => $allDisponibilites,
            'virtualPoints' => $virtualPoints->getVirtualPoints(),
            'virtualPointsProgression' => $virtualPoints->getVirtualPoints() - $this->getUser()->getClassementOfficiel()
        ]);
    }

    /**
     * @Route("/composition/{type}/edit/{compo}", name="composition.edit", requirements={"type"="\d+", "compo"="\d+"})
     * @param int $type
     * @param int $compo
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function edit(int $type, int $compo, Request $request) : Response
    {
        if (!($championnat = $this->championnatRepository->find($type))) {
            $this->addFlash('fail', 'Championnat inexistant');
            return $this->redirectToRoute('index');
        }

        if (!($compo = $this->rencontreRepository->find($compo))) {
            $this->addFlash('fail', 'Journée inexistante pour ce championnat');
            return $this->redirectToRoute('index.type', ['type' => $type]);
        }

        $dateDepassee = intval((new DateTime())->diff($compo->getIdJournee()->getDateJournee())->format('%R%a')) >= 0;
        $dateReporteeDepassee = intval((new DateTime())->diff($compo->getDateReport())->format('%R%a')) >= 0;
        if (!(($dateDepassee && !$compo->isReporte()) || ($dateReporteeDepassee && $compo->isReporte()) || $compo->getIdJournee()->getUndefined())) {
            $this->addFlash('fail', 'Cette rencontre n\'est plus modifiable : date de journée dépassée');
            return $this->redirectToRoute('journee.show', ['type' => $type, 'id' => $compo->getIdJournee()->getIdJournee()]);
        }

        $journees = $championnat->getJournees()->toArray();
        $idJournee = array_search($compo->getIdJournee(), $journees)+1;
        if (!$compo->getIdEquipe()->getIdDivision()) {
            $this->addFlash('fail', 'Cette rencontre n\'est pas modifiable car l\'équipe n\'a pas de division associée');
            return $this->redirectToRoute('journee.show', ['type' => $type, 'id' => $compo->getIdJournee()->getIdJournee()]);
        }

        $allChampionnats = $this->championnatRepository->findAll();

        /** Nombre de joueurs maximum par équipe du championnat */
        $nbMaxJoueurs = max(array_map(function($division){return $division->getNbJoueurs();}, $championnat->getDivisions()->toArray()));

        /** Numéros des équipes valides pour le brûlage */
        $equipes = $championnat->getEquipes()->toArray();
        $equipesBrulage = array_map(function($equipe){
            return $equipe->getNumero();
        }, array_filter($championnat->getEquipes()->toArray(), function($equipe){
            return $equipe->getIdDivision() != null;
        }));
        sort($equipesBrulage, SORT_NUMERIC);
        $idEquipesBrulageVisuel = array_slice($equipesBrulage, 1, count($equipesBrulage));
        $idEquipesBrulage = array_slice($equipesBrulage, 0, count($equipesBrulage) - 1);

        $brulageSelectionnables = $this->competiteurRepository->getBrulagesSelectionnables($championnat, $compo->getIdEquipe()->getNumero(), $compo->getIdJournee()->getIdJournee(), $idEquipesBrulage, $nbMaxJoueurs, $championnat->getLimiteBrulage());
        $form = $this->createForm(RencontreType::class, $compo, [
            'nbMaxJoueurs' => $nbMaxJoueurs,
            'limiteBrulage' => $championnat->getLimiteBrulage()
        ]);

        $joueursBrules = $this->competiteurRepository->getBrulesDansEquipe($compo->getIdEquipe()->getNumero(), $compo->getIdJournee()->getIdJournee(), $type, $nbMaxJoueurs, $championnat->getLimiteBrulage());
        $nbJoueursDivision = $compo->getIdEquipe()->getIdDivision()->getNbJoueurs();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nbJoueursBruleJ2 = 0;
            if ($championnat->isJ2Rule()){
                /** Liste des joueurs brûlés en J2 pour les championnats ayant cette règle */
                $joueursBrulesRegleJ2 = array_column(array_filter($brulageSelectionnables,
                    function($joueur){
                        return ($joueur["bruleJ2"]);
                    }), 'idCompetiteur');

                /** On vérifie qu'il n'y aie pas plus de 2 joueurs brûlés J2 sélectionnés **/
                for ($i = 0; $i < $nbJoueursDivision; $i++) {
                    if ($form->getData()->getIdJoueurN($i) && in_array($form->getData()->getIdJoueurN($i)->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
                }
            }

            if ($nbJoueursBruleJ2 >= 2) $this->addFlash('fail', $nbJoueursBruleJ2 . ' joueurs brûlés J2 sont sélectionnés');
            else {
                /** On sauvegarde la composition d'équipe */
                try {
                    $this->em->flush();

                    /** On vérifie que chaque joueur devenant brûlé pour de futures compositions y soit désélectionné pour chaque journée **/
                    /**  Si pas en dernière journée ni en dernière équipe **/
                    if (max(array_map(function($eq) { return $eq->getNumero(); }, $equipes)) != $compo->getIdEquipe()->getNumero() && end($journees)->getIdJournee() != $compo->getIdJournee()->getIdJournee()){
                        $journeesToRecalculate = array_slice($journees, $idJournee - 1, count($journees) - 1);
                        foreach ($journeesToRecalculate as $journeeToRecalculate) {
                            for ($j = 0; $j < $nbJoueursDivision; $j++) {
                                if ($form->getData()->getIdJoueurN($j)) $this->invalidSelectionController->checkInvalidSelection($championnat->getLimiteBrulage(), $championnat->getIdChampionnat(), $form->getData()->getIdJoueurN($j)->getIdCompetiteur(), $nbMaxJoueurs, $compo->getIdEquipe()->getNumero(), $journeeToRecalculate->getIdJournee());
                            }
                        }
                    }

                    $this->em->flush();
                    $this->addFlash('success', 'Composition modifiée');

                    return $this->redirectToRoute('journee.show', [
                        'type' => $type,
                        'id' => $compo->getIdJournee()->getIdJournee()
                    ]);
                } catch (Exception $e) {
                    if ($e->getPrevious()->getCode() == "23000"){
                        if (str_contains($e->getPrevious()->getMessage(), 'CHK_renc_joueurs')) $this->addFlash('fail', 'Un joueur ne peut être sélectionné qu\'une seule fois');
                        else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    }
                    else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                }
            }
        }

        return $this->render('journee/edit.html.twig', [
            'joueursBrules' => $joueursBrules,
            'journees' => $journees,
            'nbJoueursDivision' => $nbJoueursDivision,
            'brulageSelectionnables' => $brulageSelectionnables,
            'idEquipesBrulagePrint' => $idEquipesBrulageVisuel,
            'compo' => $compo,
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'idJournee' => $idJournee,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/composition/empty/{idCompo}/{type}/{idJournee}", name="composition.vider", requirements={"idCompo"="\d+"})
     * @param int $idCompo
     * @param int $type
     * @param int $idJournee
     * @return Response
     */
    public function emptyComposition(int $idCompo, int $type, int $idJournee) : Response
    {
        if (!($compo = $this->rencontreRepository->find($idCompo))) {
            $this->addFlash('fail', 'Rencontre inexistante');
            return $this->redirectToRoute('journee.show', ['type' => $type, 'id' => $idJournee]);
        }

        for ($i = 0; $i < $compo->getIdEquipe()->getIdDivision()->getNbJoueurs(); $i++){
            $compo->setIdJoueurNToNull($i);
        }

        $this->em->flush();
        $this->addFlash('success', 'Composition vidée');
        return $this->redirectToRoute('journee.show', [
            'type' => $compo->getIdChampionnat()->getIdChampionnat(),
            'id' => $compo->getIdJournee()->getIdJournee()
        ]);
    }

    /**
     * @Route("/informations/{type}", name="informations")
     */
    public function infos(Request $request, string $type): Response
    {
        if (!$this->get('session')->get('type')) $championnat = $this->championnatRepository->getFirstChampionnatAvailable();
        else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $this->championnatRepository->getFirstChampionnatAvailable());

        $journees = ($championnat ? $championnat->getJournees()->toArray() : []);
        $allChampionnats = $this->championnatRepository->findAll();

        $settings = $this->settingsRepository->find(1);
        try {
            $data = $settings->getInformations($type);
        } catch (Exception $e) {
            throw $this->createNotFoundException('Cette catégorie n\'existe pas');
        }

        $form = null;
        $isAdmin = $this->getUser()->isAdmin();
        if ($isAdmin){
            $form = $this->createForm(SettingsType::class, $settings, [
                'type_data' => $type
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

        return $this->render('journee/infos.html.twig', [
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'form' => $isAdmin ? $form->createView() : null,
            'journees' => $journees,
            'HTMLContent' => $data,
            'type' => $type
        ]);
    }

    /**
     * Renvoie un template des anciennes compositions d'équipe de l'adversaire des précédentes journées
     * @Route("/journee/last_compos_adversaire", name="index.lastComposAdversaire", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    function getPreviousComposAdversaire(Request $request): JsonResponse {
        $journees = [];
        $erreur = null;
        $nomAdversaire = null;
        set_time_limit(intval($this->getParameter('time_limit_last_compos_ajax')));

        try {
            /** On récupère les paramètres d'Ajax */
            $nomAdversaire = mb_convert_case($request->request->get('nomAdversaire'), MB_CASE_UPPER, "UTF-8");
            $lienDivision = $request->request->get('lienDivision');

            /** Objet API */
            $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));

            /** On récupère l'ensemble des matches (antèrieurs à aujourd'hui) de la poule de l'adversaire */
            $rencontresPoules = array_filter($api->getRencontrePouleByLienDivision($lienDivision), function($renc) use($nomAdversaire) {
                return (mb_convert_case($renc->getNomEquipeA(), MB_CASE_UPPER, "UTF-8") == $nomAdversaire || mb_convert_case($renc->getNomEquipeB(), MB_CASE_UPPER, "UTF-8") == $nomAdversaire) && $renc->getDatePrevue() < new DateTime();
            });

            /** On récupère toutes les équipes de la poule pour extraire le numéro du club adversaire */
            $equipesPoules = $api->getClassementPouleByLienDivision($lienDivision);
            $numeroClubAdversaire = array_filter($equipesPoules, function($equ) use ($nomAdversaire) {
                return $equ->getNomEquipe() == $nomAdversaire;
            });
            $numeroClubAdversaire = count($numeroClubAdversaire) == 1 ? $numeroClubAdversaire[array_key_first($numeroClubAdversaire)]->getNumero() : null;

            foreach ($rencontresPoules as $rencontrePoule){
                $journee = [];
                $domicile = mb_convert_case($rencontrePoule->getNomEquipeA(), MB_CASE_UPPER, "UTF-8") == $nomAdversaire;

                /** On récupère le numéro du club adversaire .... de notre adversaire */
                $nomAdversaireBis = $domicile ? mb_convert_case($rencontrePoule->getNomEquipeB(), MB_CASE_UPPER, "UTF-8") : mb_convert_case($rencontrePoule->getNomEquipeA(), MB_CASE_UPPER, "UTF-8");
                $numeroClubAdversaireBis = array_filter($equipesPoules, function($equ) use ($nomAdversaireBis) {
                    return $equ->getNomEquipe() == $nomAdversaireBis;
                });
                $numeroClubAdversaireBis = count($numeroClubAdversaireBis) == 1 ? $numeroClubAdversaireBis[array_key_first($numeroClubAdversaireBis)]->getNumero() : null;

                /** On récupère les détails des rencontres  de l'adversaire pour extraire les joueurs alignés lors des précédentes journées */
                $detailsRencontre = $api->getDetailsRencontreByLien($rencontrePoule->getLien(), $domicile ? $numeroClubAdversaire : $numeroClubAdversaireBis, $domicile ? $numeroClubAdversaireBis : $numeroClubAdversaire);
                $joueursAdversaire = $domicile ? $detailsRencontre->getJoueursA() : $detailsRencontre->getJoueursB();
                $joueursAdversaireBis = !$domicile ? $detailsRencontre->getJoueursA() : $detailsRencontre->getJoueursB();
                $joueursAdversaireFormatted = [];

                /** On vérifie qu'il n'y aie pas d'erreur dans la feuille de match */
                $errorMatchSheet = count(array_filter($joueursAdversaire, function($joueur) {
                    return !$joueur->getLicence() || !$joueur->getPoints();
                })) > 0;

                /** On formatte la liste des joueurs et on leur associe leurs résultats avec les points de leurs adversaires s'il n'y a pas d'erreur dans la feuille de match */
                if (!$errorMatchSheet){
                    /** Liste des parties des joueurs lors de la rencontre */
                    $parties = $detailsRencontre->getParties();
                    foreach ($joueursAdversaire as $joueurAdversaire) {
                        if (count($joueursAdversaire)){
                            $matches = array_filter($parties, function($partie) use ($joueurAdversaire, $domicile) {
                                return $domicile ? $partie->getAdversaireA() == $joueurAdversaire->getNom() . ' ' . $joueurAdversaire->getPrenom() : $partie->getAdversaireB() == $joueurAdversaire->getNom() . ' ' . $joueurAdversaire->getPrenom();
                            } );

                            $resultatMatches = array_map(function($match) use ($domicile, $joueursAdversaireBis) {
                                $score = $domicile ? $match->getScoreA() : $match->getScoreB();
                                $nomJoueurAdversaireBis = !$domicile ? $match->getAdversaireA() : $match->getAdversaireB();

                                $joueurAdversaireBis = array_values(array_filter($joueursAdversaireBis, function($joueurAdversaireBis) use ($nomJoueurAdversaireBis) {
                                    return $joueurAdversaireBis->getNom() . ' ' . $joueurAdversaireBis->getPrenom() == $nomJoueurAdversaireBis;
                                }));

                                return [
                                    'resultat' => ($score == 2 ? 'green' : 'red lighten-1'),
                                    'pointsJoueurAdversaire' => count($joueurAdversaireBis) ? $joueurAdversaireBis[0]->getPoints() : '<i style="font-size: 1.5em" class="material-icons tiny">help_outline</i>'
                                ];
                            }, $matches);

                            $joueursAdversaireFormatted[$joueurAdversaire->getNom()]['points'] = $joueurAdversaire->getPoints();
                            $joueursAdversaireFormatted[$joueurAdversaire->getNom()]['resultats'] = $resultatMatches;
                        }
                    }
                }

                $journee['nomAdversaireBis'] = mb_convert_case($nomAdversaireBis, MB_CASE_TITLE, "UTF-8");
                $journee['joueurs'] = $joueursAdversaireFormatted;
                $journee['errorMatchSheet'] = $errorMatchSheet;
                array_push($journees, $journee);
            }
        } catch(Exception $exception) {
            $erreur = 'Liste des joueurs alignés par l\'adversaire lors des journées précédentes non disponible';
        }

        return new JsonResponse($this->render('ajax/lastComposAdversaire.html.twig', [
            'journees' => $journees,
            'erreur' => $erreur,
            'nomAdversaire' => mb_convert_case($nomAdversaire, MB_CASE_TITLE, "UTF-8")
        ])->getContent());
    }

    /**
     * Renvoie un template du classement des points virtuels de tous les joueurs du club
     * @Route("/journee/classement-virtual-points", name="index.classementVirtualPoints", methods={"POST"})
     * @return JsonResponse
     */
    function getClassementPointsVirtuelsClub(): JsonResponse {
        $competiteurs = $this->competiteurRepository->findJoueursByRole('Competiteur', null);
        $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
        $classementPointsVirtuelsMensuel = array_map(function($joueur) use ($api) {
            $virtualPoint = new VirtualPoints(0, 0);

            if ($joueur->getLicence()) {
                try {
                    $virtualPoint = $api->getJoueurVirtualPoints($joueur->getLicence());
                } catch (Exception $e) {}
            }
            return [
                'nom' => $joueur->getNom() . ' ' . $joueur->getPrenom(),
                'avatar' => 'images/profile_pictures/' . ($joueur->getAvatar() ?: 'images/account.png'),
                'pointsVirtuelsPointsWon' => $virtualPoint->getPointsWon(),
                'pointsVirtuelsVirtualPoints' => $virtualPoint->getVirtualPoints(),
                'pointsVirtuelsSaison' => $virtualPoint->getVirtualPoints() - $joueur->getClassementOfficiel()
            ];
        }, $competiteurs);
        $classementPointsVirtuelsPhase = $classementPointsVirtuelsMensuel;

        usort($classementPointsVirtuelsMensuel, function ($a, $b) {
            if ($a['pointsVirtuelsPointsWon'] == $b['pointsVirtuelsPointsWon']) {
                return $b['pointsVirtuelsVirtualPoints'] - $a['pointsVirtuelsVirtualPoints'];
            }
            return $b['pointsVirtuelsPointsWon'] - $a['pointsVirtuelsPointsWon'];
        });

        usort($classementPointsVirtuelsPhase, function ($a, $b) {
            if ($a['pointsVirtuelsSaison'] == $b['pointsVirtuelsSaison']) {
                return $b['pointsVirtuelsVirtualPoints'] - $a['pointsVirtuelsVirtualPoints'];
            }
            return $b['pointsVirtuelsSaison'] - $a['pointsVirtuelsSaison'];
        });

        return new JsonResponse($this->render('ajax/classementVirtualPoints.html.twig', [
            'classementPointsVirtuelsMensuel' => $classementPointsVirtuelsMensuel,
            'classementPointsVirtuelsPhase' => $classementPointsVirtuelsPhase,
        ])->getContent());
    }
}