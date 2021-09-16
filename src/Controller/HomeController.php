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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        // Si l'utilisateur actuel est sélectionné pour la journée actuelle
        $selected = in_array($this->getUser()->getIdCompetiteur(), $selectedPlayers);

        $allChampionnats = $this->championnatRepository->findAll();
        $allDisponibilites = $this->competiteurRepository->findAllDisposRecapitulatif($allChampionnats);

        // Brûlages des joueurs
        $divisions = $championnat->getDivisions()->toArray();
        $brulages = $divisions ? $this->competiteurRepository->getBrulages($type, $id, $idEquipesBrulage, max(array_map(function($division){return $division->getNbJoueurs();}, $divisions))) : null;

        return $this->render('journee/index.html.twig', [
            'journee' => $journee,
            'idJournee' => $numJournee,
            'equipesSansDivision' => $equipesSansDivision,
            'journees' => $journees,
            'nbTotalJoueurs' => $nbTotalJoueurs,
            'nbMinJoueurs' => $nbMinJoueurs,
            'allChampionnats' => $allChampionnats,
            'selected' => $selected,
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
            'allDisponibilites' => $allDisponibilites
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

        $data = null;
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
                    return $this->redirectToRoute('informations');
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
}