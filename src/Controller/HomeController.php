<?php

namespace App\Controller;

use App\Entity\Rencontre;
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
    private $settingsRepository;

    const ABSENT_ABSENT = 'Absent Absent';
    const WO = 'WO';

    /**
     * @param ChampionnatRepository $championnatRepository
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param RencontreRepository $rencontreRepository
     * @param SettingsRepository $settingsRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(ChampionnatRepository $championnatRepository,
                                DisponibiliteRepository $disponibiliteRepository,
                                CompetiteurRepository $competiteurRepository,
                                RencontreRepository $rencontreRepository,
                                SettingsRepository $settingsRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreRepository = $rencontreRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->championnatRepository = $championnatRepository;
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * @Route("/", name="index")
     * @param UtilController $utilController
     * @return Response
     */
    public function indexAction(UtilController $utilController): Response
    {
        if ($utilController->nextJourneeToPlayAllChamps()){
            return $this->redirectToRoute('journee.show', [
                'type' => $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat()->getIdChampionnat(),
                'idJournee' => $utilController->nextJourneeToPlayAllChamps()->getIdJournee()
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
        $championnat = $this->championnatRepository->find($type);
        if ($championnat) {
            return $this->redirectToRoute('journee.show', [
                'type' => $championnat->getIdChampionnat(),
                'idJournee' => $championnat->getNextJourneeToPlay() ? $championnat->getNextJourneeToPlay()->getIdJournee() : $championnat->getJournees()->toArray()[0]->getIdJournee()
            ]);
        } else return $this->redirectToRoute('index');
    }

    /**
     * @param int $type
     * @param int $idJournee
     * @param ContactController $contactController
     * @param UtilController $utilController
     * @return Response
     * @Route("/journee/{type}/{idJournee}", name="journee.show", requirements={"type"="\d+", "idJournee"="\d+"})
     * @throws Exception
     */
    public function journee(int $type, int $idJournee, ContactController $contactController, UtilController $utilController): Response
    {
        if (!($championnat = $this->championnatRepository->find($type))) return $this->redirectToRoute('index');
        $journees = $championnat->getJournees()->toArray();

        if (!in_array($idJournee, array_map(function ($journee){ return $journee->getIdJournee(); }, $journees))){
            $this->addFlash('fail', 'Journée inexistante pour ce championnat');
            return $this->redirectToRoute('index.type', ['type' => $type]);
        }
        $journee = array_values(array_filter($journees, function($journee) use ($idJournee) { return ($journee->getIdJournee() == $idJournee ? $journee : null); }))[0];

        $this->get('session')->set('type', $type);

        // Disponibilité du joueur
        $disposJoueur = $this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idChampionnat' => $type]);
        $dispoJoueur = array_values(array_filter($disposJoueur, function($dispo) use ($idJournee) {
            return $dispo->getIdJournee()->getIdJournee() == $idJournee;
        }));
        $dispoJoueur = count($dispoJoueur) ? $dispoJoueur[0] : null;
        $disposJoueurFormatted = null;
        if ($this->getUser()->isCompetiteur()) {
            $disposJoueurFormatted = [];
            foreach($disposJoueur as $dispo) {
                $disposJoueurFormatted[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
            }
        }

        // Compositions d'équipe
        $compos = $this->rencontreRepository->getRencontres($idJournee, $type);

        // Numero de la journée
        $numJournee = array_search($journee, $journees)+1;

        // Disponibilités des joueurs pour la journée par équipe
        $nbMaxJoueursParDivision = array_map(function($compo) use ($type) { return $compo->getIdEquipe()->getIdDivision()->getNbJoueurs(); }, $compos);
        $disponibilitesJournee = $this->competiteurRepository->findDisposJoueurs($idJournee, $type, $nbMaxJoueursParDivision ? max($nbMaxJoueursParDivision) : $this->getParameter('nb_joueurs_default_division'));
        $joueursNonDeclares = [];
        $joueursDisponibles = [];
        foreach ($disponibilitesJournee as $equipe) {
            foreach ($equipe as $dispoJoueurEquipe) {
                if ($dispoJoueurEquipe['disponibilite'] == null) $joueursNonDeclares[] = $dispoJoueurEquipe;
                else if ($dispoJoueurEquipe['disponibilite'] == '1') $joueursDisponibles[] = $dispoJoueurEquipe;
            }
        }
        $joueursNonDeclaresContact = $contactController->returnPlayersContactByMedia(array_map(function($dispo){ return $dispo['joueur']; }, $joueursNonDeclares));

        $messageJoueursSansDispo = $objetJoueursSansDispos = null;
        if (count($joueursNonDeclares)) {
            $messageJoueursSansDispo = $this->settingsRepository->find('mail-sans-dispo')->getContent();

            /** Formattage du message **/
            setlocale (LC_TIME, 'fr_FR.utf8','fra');
            date_default_timezone_set('Europe/Paris');
            $str_replacers = [
                'old' => ['[#n_journee#]', '[#date_journee#]', '[#nom_redacteur#]', '[#nom_championnat#]'],
                'new' => [$numJournee, $journee->getDateJourneeFrench(), $this->getUser()->getPrenom() . ' ' . $this->getUser()->getNom(), $championnat->getNom()]
            ];
            $objetJoueursSansDispos = 'Disponibilité non déclarée - J' . $numJournee . ' - ' . $championnat->getNom() . ' - ' . $journee->getDateJournee()->format('d/m/Y');
            $messageJoueursSansDispo = str_replace($str_replacers['old'], $str_replacers['new'], $messageJoueursSansDispo);
            $messageJoueursSansDispo = str_replace('</p><p>', '%0D%0A%0D%0A', $messageJoueursSansDispo);
            $messageJoueursSansDispo = str_replace('<p>', '', $messageJoueursSansDispo);
            $messageJoueursSansDispo = str_replace('</p>', '', $messageJoueursSansDispo);
            $messageJoueursSansDispo = strip_tags($messageJoueursSansDispo);
        }

        // Joueurs sélectionnées
        $selectedPlayers = $this->rencontreRepository->getSelectedPlayers($compos);

        // Nombre maximal de joueurs pour les compos du championnat sélectionné
        $nbMaxSelectedJoueurs = array_sum(array_map(function($compo) use ($type) {
            return !$compo->isExempt() ? $compo->getIdEquipe()->getIdDivision()->getNbJoueurs() : 0;
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

        // Si l'utilisateur actuel est disponible pour la journée actuelle
        $disponible = ($dispoJoueur ? ($dispoJoueur->getDisponibilite() ? 1 : 0) : -1);

        // Si l'utilisateur est sélectionné pour la journée actuelle
        $selection = $this->getUser()->isSelectedIn($compos);

        $allChampionnats = $this->championnatRepository->findAll();
        $allDisponibilites = $this->competiteurRepository->findAllDisposRecapitulatif($allChampionnats);

        // Brûlages des joueurs
        $divisions = $championnat->getDivisions()->toArray();
        $brulages = $divisions ? $this->competiteurRepository->getBrulages($type, $idJournee, $idEquipesBrulage, max(array_map(function($division){return $division->getNbJoueurs();}, $divisions))) : null;

        $allValidPlayers = $this->competiteurRepository->findBy(['isArchive' => false], ['nom' => 'ASC', 'prenom' => 'ASC']);
        $countJoueursCertifMedicPerim = count(array_filter($allValidPlayers, function ($joueur) {
            return $joueur->isCertifMedicalInvalid()['status'] && $joueur->isCompetiteur();
        }));

        /** Joueurs sans licence définie */
        $countJoueursWithoutLicence = count(array_filter($allValidPlayers, function ($joueur) {
            return !$joueur->getLicence() && $joueur->isCompetiteur();
        }));
        $joueursWithoutLicence = [
            'count' => $countJoueursWithoutLicence,
            'message' => $countJoueursWithoutLicence ? 'Il y a <b>' . $countJoueursWithoutLicence . ' compétiteur' . ($countJoueursWithoutLicence > 1 ? 's' : '') . '</b> dont la licence n\'est pas définie' : ''
        ];

        /** Compétiteurs sans classement officiel défini */
        $countCompetiteursWithoutClassement = count(array_filter($allValidPlayers, function ($joueur) {
            return !$joueur->getClassementOfficiel() && $joueur->isCompetiteur();
        }));
        $competiteursWithoutClassement = [
            'count' => $countCompetiteursWithoutClassement,
            'message' => $countCompetiteursWithoutClassement ? ($countJoueursWithoutLicence ? ' et ' : 'Il y a ' ) . '<b>' . $countCompetiteursWithoutClassement . ' compétiteur' . ($countCompetiteursWithoutClassement > 1 ? 's' : '') . '</b> dont le classement officiel n\'est pas défini' : ''
        ];

        $linkNextJournee = ($utilController->nextJourneeToPlayAllChamps()->getDateJournee() !== $journee->getDateJournee() ? '/journee/' . $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat()->getIdChampionnat() . '/' . $utilController->nextJourneeToPlayAllChamps()->getIdJournee() : null);
        $journeesWithReportedRencontres = $this->rencontreRepository->getJourneesWithReportedRencontres($championnat->getIdChampionnat());
        $journeesWithReportedRencontresFormatted = array_filter($journeesWithReportedRencontres['rencontres'], function (Rencontre $r) use ($idJournee) {
            return $r->getIdJournee()->getIdJournee() == $idJournee;
        });

        return $this->render('journee/index.html.twig', [
            'journee' => $journee,
            'idJournee' => $numJournee,
            'equipesSansDivision' => $equipesSansDivision,
            'journees' => $journees,
            'journeesWithReportedRencontres' => $journeesWithReportedRencontres['ids'],
            'journeesWithReportedRencontresFormatted' => $journeesWithReportedRencontresFormatted,
            'nbMaxSelectedJoueurs' => $nbMaxSelectedJoueurs,
            'nbMaxPotentielPlayers' => array_sum(array_map(function($equipe) { return count($equipe); }, $disponibilitesJournee)),
            'nbMinJoueurs' => $nbMinJoueurs,
            'allChampionnats' => $allChampionnats,
            'selection' => $selection,
            'championnat' => $championnat,
            'compos' => $compos,
            'idEquipes' => $idEquipesVisuel,
            'selectedPlayers' => $selectedPlayers,
            'disponibilitesJournee' => $disponibilitesJournee,
            'disponible' => $disponible,
            'joueursNonDeclaresContact' => $joueursNonDeclaresContact,
            'dispoJoueur' => $dispoJoueur ? $dispoJoueur->getIdDisponibilite() : -1,
            'disposJoueur' => $disposJoueurFormatted,
            'nbDispos' => count($joueursDisponibles),
            'nbNonDeclares' => count($joueursNonDeclares),
            'brulages' => $brulages,
            'isPreRentreeLaunchable' => $utilController->isPreRentreeLaunchable($championnat)['launchable'],
            'allDisponibilites' => $allDisponibilites,
            'countJoueursCertifMedicPerim' => $countJoueursCertifMedicPerim,
            'joueursWithoutLicence' => $joueursWithoutLicence,
            'competiteursWithoutClassement' => $competiteursWithoutClassement,
            'messageJoueursSansDispo' => $messageJoueursSansDispo,
            'objetJoueursSansDispos' => $objetJoueursSansDispos,
            'linkNextJournee' => $linkNextJournee
        ]);
    }

    /**
     * @Route("/journee/edit/{type}/{compo}", name="composition.edit", requirements={"type"="\d+", "compo"="\d+"})
     * @param int $type
     * @param int $compo
     * @param Request $request
     * @param UtilController $utilController
     * @return Response
     */
    public function edit(int $type, int $compo, Request $request, UtilController $utilController) : Response
    {
        if (!($championnat = $this->championnatRepository->find($type))) {
            $this->addFlash('fail', 'Championnat inexistant');
            return $this->redirectToRoute('index');
        }

        if (!($compo = $this->rencontreRepository->find($compo))) {
            $this->addFlash('fail', 'Journée inexistante pour ce championnat');
            return $this->redirectToRoute('index.type', ['type' => $type]);
        }

        if ($compo->isExempt()) {
            $this->addFlash('fail', 'Journée exemptée pour l\'équipe ' . $compo->getIdEquipe()->getNumero());
            return $this->redirectToRoute('journee.show', ['type' => $type, 'idJournee' => $compo->getIdJournee()->getIdJournee()]);
        }

        $dateDepassee = intval((new DateTime())->diff($compo->getIdJournee()->getDateJournee())->format('%R%a')) >= 0;
        $dateReporteeDepassee = intval((new DateTime())->diff($compo->getDateReport())->format('%R%a')) >= 0;
        if (!(($dateDepassee && !$compo->isReporte()) || ($dateReporteeDepassee && $compo->isReporte()) || $compo->getIdJournee()->getUndefined())) {
            $this->addFlash('fail', 'Cette rencontre n\'est plus modifiable : date de journée dépassée');
            return $this->redirectToRoute('journee.show', ['type' => $type, 'idJournee' => $compo->getIdJournee()->getIdJournee()]);
        }

        $journees = $championnat->getJournees()->toArray();
        $idJournee = array_search($compo->getIdJournee(), $journees)+1;
        if (!$compo->getIdEquipe()->getIdDivision()) {
            $this->addFlash('fail', 'Cette rencontre n\'est pas modifiable car l\'équipe n\'a pas de division associée');
            return $this->redirectToRoute('journee.show', ['type' => $type, 'idJournee' => $compo->getIdJournee()->getIdJournee()]);
        }

        $allChampionnats = $this->championnatRepository->findAll();

        /** Nombre de joueurs maximum par équipe du championnat */
        $nbMaxJoueurs = max(array_map(function($division){return $division->getNbJoueurs();}, $championnat->getDivisions()->toArray()));

        /** Numéros des équipes valides pour le brûlage */
        $equipesBrulage = array_map(function($equipe){
            return $equipe->getNumero();
        }, array_filter($championnat->getEquipes()->toArray(), function($equipe){
            return $equipe->getIdDivision() != null;
        }));
        sort($equipesBrulage, SORT_NUMERIC);
        $idEquipesBrulageVisuel = array_slice($equipesBrulage, 1, count($equipesBrulage));
        $idEquipesBrulage = array_slice($equipesBrulage, 0, count($equipesBrulage) - 1);

        $joueursSelectionnables = $this->competiteurRepository->getJoueursSelectionnablesOptGroup($nbMaxJoueurs, $championnat->getLimiteBrulage(), $compo);
        $brulageSelectionnables = $this->competiteurRepository->getBrulagesSelectionnables($championnat, $compo->getIdEquipe()->getNumero(), $compo->getIdJournee()->getIdJournee(), $idEquipesBrulage, $nbMaxJoueurs, $championnat->getLimiteBrulage());
        $form = $this->createForm(RencontreType::class, $compo, [
            'joueursSelectionnables' => $joueursSelectionnables,
            'editCompoMode' => true
        ]);

        $joueursBrules = $this->competiteurRepository->getBrulesDansEquipe($compo->getIdEquipe()->getNumero(), $compo->getIdJournee()->getIdJournee(), $type, $nbMaxJoueurs, $championnat->getLimiteBrulage());
        $nbJoueursDivision = $compo->getIdEquipe()->getIdDivision()->getNbJoueurs();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $nbJoueursBruleJ2 = 0;
            if ($championnat->isJ2Rule()) {
                /** Liste des joueurs brûlés en J2 pour les championnats ayant cette règle */
                $joueursBrulesRegleJ2 = array_column(array_filter($brulageSelectionnables['joueurs'],
                    function($joueur){
                        return ($joueur["bruleJ2"]);
                    }), 'idCompetiteur');

                /** On vérifie qu'il n'y aie pas plus de 2 joueurs brûlés J2 sélectionnés **/
                for ($i = 0; $i < $nbJoueursDivision; $i++) {
                    if ($form->getData()->getIdJoueurN($i) && in_array($form->getData()->getIdJoueurN($i)->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
                }
            }

            if ($nbJoueursBruleJ2 >= 2) $this->addFlash('fail', $nbJoueursBruleJ2 . ' joueurs brûlés spécial J2 sont sélectionnés');
            else {
                /** On sauvegarde la composition d'équipe */
                try {
                    $this->em->flush();

                    /** On vérifie que chaque joueur devenant brûlé pour de futures compositions y soit désélectionné pour chaque journée **/
                    $journeesToRecalculate = array_slice($journees, $idJournee - 1, count($journees) - 1);
                    $invalidCompos = [];

                    foreach ($journeesToRecalculate as $journeeToRecalculate) {
                        for ($j = 0; $j < $nbJoueursDivision; $j++) {
                            if ($form->getData()->getIdJoueurN($j)) {
                                $invalidCompo = $this->rencontreRepository->getSelectedWhenBurnt($form->getData()->getIdJoueurN($j)->getIdCompetiteur(), $journeeToRecalculate->getIdJournee(), $championnat->getLimiteBrulage(), $nbMaxJoueurs, $championnat->getIdChampionnat());
                                if ($invalidCompo){
                                    array_push($invalidCompos, ...$invalidCompo);
                                    $utilController->deleteInvalidSelectedPlayers($invalidCompo, $nbMaxJoueurs, $form->getData()->getIdJoueurN($j)->getIdCompetiteur());
                                }
                            }
                        }
                    }

                    /** Si le joueur devient indisponible et qu'il est sélectionné, on re-trie la composition d'équipe */
                    foreach ($invalidCompos as $invalidCompo){
                        $invalidCompo['compo']->sortComposition();
                    }

                    /** On trie la composition d'équipe dans l'ordre décroissant des classements si le championnat possède cette règle */
                    $compo->sortComposition();

                    $this->em->flush();
                    $this->addFlash('success', 'Composition modifiée');

                    return $this->redirectToRoute('journee.show', [
                        'type' => $type,
                        'idJournee' => $compo->getIdJournee()->getIdJournee()
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

        // Disponibilités du joueur
        $disposJoueur = $this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idChampionnat' => $type]);
        $disposJoueurFormatted = [];
        foreach($disposJoueur as $dispo) {
            $disposJoueurFormatted[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
        }

        return $this->render('journee/edit.html.twig', [
            'joueursBrules' => $joueursBrules,
            'journees' => $journees,
            'journeesWithReportedRencontres' => $this->rencontreRepository->getJourneesWithReportedRencontres($championnat->getIdChampionnat())['ids'],
            'nbJoueursDivision' => $nbJoueursDivision,
            'brulageSelectionnables' => $brulageSelectionnables['par_equipes'],
            'idEquipesBrulagePrint' => $idEquipesBrulageVisuel,
            'compo' => $compo,
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'idJournee' => $idJournee,
            'form' => $form->createView(),
            'disposJoueur' => $disposJoueurFormatted
        ]);
    }

    /**
     * @Route("/journee/empty/{type}/{idJournee}/{idCompo}", name="composition.vider", requirements={"idCompo"="\d+"})
     * @param int $idCompo
     * @param int $type
     * @param int $idJournee
     * @return Response
     */
    public function emptyComposition(int $type, int $idJournee, int $idCompo) : Response
    {
        if (!($compo = $this->rencontreRepository->find($idCompo))) {
            $this->addFlash('fail', 'Rencontre inexistante');
            return $this->redirectToRoute('journee.show', ['type' => $type, 'idJournee' => $idJournee]);
        }

        if ($compo->isExempt()) {
            $this->addFlash('fail', 'Journée exemptée pour l\'équipe ' . $compo->getIdEquipe()->getNumero());
            return $this->redirectToRoute('journee.show', ['type' => $type, 'idJournee' => $compo->getIdJournee()->getIdJournee()]);
        }

        $dateDepassee = intval((new DateTime())->diff($compo->getIdJournee()->getDateJournee())->format('%R%a')) >= 0;
        $dateReporteeDepassee = intval((new DateTime())->diff($compo->getDateReport())->format('%R%a')) >= 0;
        if (!(($dateDepassee && !$compo->isReporte()) || ($dateReporteeDepassee && $compo->isReporte()) || $compo->getIdJournee()->getUndefined())) {
            $this->addFlash('fail', 'Cette rencontre n\'est plus modifiable : date de journée dépassée');
            return $this->redirectToRoute('journee.show', ['type' => $type, 'idJournee' => $compo->getIdJournee()->getIdJournee()]);
        }

        $compo->emptyCompo();
        $this->em->flush();
        $this->addFlash('success', 'Composition vidée');
        return $this->redirectToRoute('journee.show', [
            'type' => $compo->getIdChampionnat()->getIdChampionnat(),
            'idJournee' => $compo->getIdJournee()->getIdJournee()
        ]);
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
     * Renvoie un template des anciennes compositions d'équipe de l'adversaire des précédentes journées
     * @Route("/journee/last_compos_adversaire", name="index.lastComposAdversaire", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getPreviousComposAdversaire(Request $request): JsonResponse {
        $journees = [];
        $erreur = null;
        $nomAdversaire = null;
        set_time_limit(intval($this->getParameter('time_limit_ajax')));

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

            foreach ($rencontresPoules as $rencontrePoule) {
                $journee = [];

                /** Si une des rencontre jouées par l'adversaire est exemptée */
                if ($rencontrePoule->getNomEquipeA() == null || $rencontrePoule->getNomEquipeB() == null) $journee['exempt'] = true;
                else {
                    $domicile = mb_convert_case($rencontrePoule->getNomEquipeA(), MB_CASE_UPPER, "UTF-8") == $nomAdversaire;

                    /** On récupère le numéro du club adversaire ... de notre adversaire */
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
                    $matchesDoubles = [];

                    /** Résultat de la rencontre */
                    $resultat = ['score' => $domicile ?
                        $detailsRencontre->getScoreEquipeA() . ' - ' . $detailsRencontre->getScoreEquipeB() :
                        $detailsRencontre->getScoreEquipeB() . ' - ' . $detailsRencontre->getScoreEquipeA()
                    ];

                    $isTeamForfeit = $detailsRencontre->getScoreEquipeA() == 0 && !count($detailsRencontre->getJoueursA()) && $detailsRencontre->getScoreEquipeB() != 0 && count($detailsRencontre->getJoueursB()) ?
                        $detailsRencontre->getNomEquipeA() :
                            (($detailsRencontre->getScoreEquipeB() == 0 && !count($detailsRencontre->getJoueursB()) && $detailsRencontre->getScoreEquipeA() != 0 && count($detailsRencontre->getJoueursA())) ?
                                $detailsRencontre->getNomEquipeB() : null);

                    if (($detailsRencontre->getScoreEquipeA() > $detailsRencontre->getScoreEquipeB() && !$domicile) || ($domicile && $detailsRencontre->getScoreEquipeA() < $detailsRencontre->getScoreEquipeB()))
                        $resultat['resultat'] = 'red lighten-1';
                    else if (($detailsRencontre->getScoreEquipeA() > $detailsRencontre->getScoreEquipeB() && $domicile) || ($detailsRencontre->getScoreEquipeA() < $detailsRencontre->getScoreEquipeB() && !$domicile))
                        $resultat['resultat'] = 'green';
                    else if ($detailsRencontre->getScoreEquipeA() == $detailsRencontre->getScoreEquipeB())
                        $resultat['resultat'] = 'grey darken-1';
                    else $resultat['resultat'] = null;

                    /** On vérifie qu'il n'y aie pas d'erreur dans la feuille de match */
                    $errorMatchSheet = count($detailsRencontre->getParties()) && count(array_filter($joueursAdversaire, function($joueur) {
                            return !$joueur->getLicence() || !$joueur->getPoints();
                        })) == count($joueursAdversaire) && count(array_filter($joueursAdversaireBis, function($joueur) {
                            return !$joueur->getLicence() || !$joueur->getPoints();
                        })) == count($joueursAdversaireBis);

                    /** On formatte la liste des joueurs et on leur associe leurs résultats avec les points de leurs adversaires s'il n'y a pas d'erreur dans la feuille de match */
                    if ($isTeamForfeit) {
                        $joueursAdversaireFormatted = [1];
                        $journee['teamForfeit'] = mb_convert_case($isTeamForfeit, MB_CASE_TITLE, "UTF-8");
                    } else if (!$errorMatchSheet) {
                        /** Liste des parties des joueurs lors de la rencontre */
                        $parties = $detailsRencontre->getParties();

                        /** Mapping des doubles */
                        $matchesDoubles = array_filter(array_map(function($partieDouble) use ($domicile, $joueursAdversaire, $detailsRencontre, $joueursAdversaireBis) {
                            $partieDoubleFormatted = [];
                            preg_match('/([a-zà-ÿA-ZÀ-Ý\s\-\']+) et ([a-zà-ÿA-ZÀ-Ý\s\-\']+)/', $domicile ? $partieDouble->getAdversaireA() : $partieDouble->getAdversaireB(), $joueursBinomeDouble);
                            preg_match('/([a-zà-ÿA-ZÀ-Ý\s\-\']+) et ([a-zà-ÿA-ZÀ-Ý\s\-\']+)/', $domicile ? $partieDouble->getAdversaireB() : $partieDouble->getAdversaireA(), $joueursBinomeDoubleBis);

                            if (count($joueursBinomeDouble) == 3 || count($joueursBinomeDoubleBis) == 3) {
                                // Gestion du binôme de l'équipe adversaire
                                if (($domicile ? $partieDouble->getAdversaireA() : $partieDouble->getAdversaireB()) == self::ABSENT_ABSENT) {
                                    $partieDoubleFormatted['isBinomeWinner'] = 'red';

                                    if ($domicile && $partieDouble->getAdversaireA() == self::ABSENT_ABSENT) $partieDoubleFormatted['isBinomeWO'] = true;
                                    else if (!$domicile && $partieDouble->getAdversaireB() == self::ABSENT_ABSENT) $partieDoubleFormatted['isBinomeWO'] = true;
                                } else {
                                    $partieDoubleFormatted['isBinomeWinner'] = ($domicile ? $partieDouble->getScoreA() > $partieDouble->getScoreB() : $partieDouble->getScoreB() > $partieDouble->getScoreA()) ? 'green' : 'red';
                                    if (count($joueursBinomeDouble) == 3 && in_array($joueursBinomeDouble[1], array_keys($joueursAdversaire)) && in_array($joueursBinomeDouble[2], array_keys($joueursAdversaire))) {
                                        $partieDoubleFormatted['binomeAdversaire'] = [$joueursAdversaire[$joueursBinomeDouble[1]]->getPoints(), $joueursAdversaire[$joueursBinomeDouble[2]]->getPoints()];
                                    }
                                }

                                // Gestion du binôme de l'équipe adversaire bis
                                if (($domicile ? $partieDouble->getAdversaireB() : $partieDouble->getAdversaireA()) == self::ABSENT_ABSENT) {
                                    $partieDoubleFormatted['isBinomeWinner'] = 'green';

                                    if ($domicile && $partieDouble->getAdversaireB() == self::ABSENT_ABSENT) $partieDoubleFormatted['isBinomeBisWO'] = true;
                                    else if (!$domicile && $partieDouble->getAdversaireA() == self::ABSENT_ABSENT) $partieDoubleFormatted['isBinomeBisWO'] = true;
                                } else {
                                    if (count($joueursBinomeDoubleBis) == 3 && in_array($joueursBinomeDoubleBis[1], array_keys($joueursAdversaireBis)) && in_array($joueursBinomeDoubleBis[2], array_keys($joueursAdversaireBis))) {
                                        $partieDoubleFormatted['binomeAdversaireBis'] = [$joueursAdversaireBis[$joueursBinomeDoubleBis[1]]->getPoints(), $joueursAdversaireBis[$joueursBinomeDoubleBis[2]]->getPoints()];
                                    }
                                }
                                return $partieDoubleFormatted;
                            } else {
                                unset($partieDouble);
                                return [];
                            }
                        }, $parties));

                        foreach ($joueursAdversaire as $nomJoueurAdversaire => $joueurAdversaire) {
                            if (count($joueursAdversaire)){
                                $matches = array_filter($parties, function($partie) use ($nomJoueurAdversaire, $domicile) {
                                    return $domicile ? $partie->getAdversaireA() == $nomJoueurAdversaire : $partie->getAdversaireB() == $nomJoueurAdversaire;
                                });

                                $resultatMatches = array_map(function($match) use ($domicile, $joueursAdversaireBis) {
                                    $isWinner = $domicile ? $match->getScoreA() > $match->getScoreB() : $match->getScoreB() > $match->getScoreA();
                                    $nomJoueurAdversaireBis = !$domicile ? $match->getAdversaireA() : $match->getAdversaireB();

                                    $joueurAdversaireBis = array_values(array_filter($joueursAdversaireBis, function($joueurAdversaireBis) use ($nomJoueurAdversaireBis) {
                                        return $joueurAdversaireBis->getNom() . ' ' . $joueurAdversaireBis->getPrenom() == $nomJoueurAdversaireBis;
                                    }));

                                    // Si le joueur adversaire est WO
                                    $pointsAdversaire = '<i style="font-size: 1.5em" class="material-icons tiny">help</i>';
                                    $isWo = ($domicile && $match->getAdversaireB() == self::ABSENT_ABSENT) || (!$domicile && $match->getAdversaireA() == self::ABSENT_ABSENT);
                                    if ($isWo) {
                                        $pointsAdversaire = self::WO;
                                    } else if (count($joueurAdversaireBis) && $joueurAdversaireBis[0]->getPoints()) {
                                        $pointsAdversaire = $joueurAdversaireBis[0]->getPoints();
                                    }

                                    return [
                                        'resultat' => ($isWinner ? 'green' . ($isWo ? ' lighten-3' : '') : 'red lighten-1'),
                                        'pointsJoueurAdversaire' => $pointsAdversaire
                                    ];
                                }, $matches);

                                $joueursAdversaireFormatted[trim($joueurAdversaire->getNom()) . '#' . $joueurAdversaire->getLicence()]['points'] = $joueurAdversaire->getPoints();
                                $joueursAdversaireFormatted[trim($joueurAdversaire->getNom()) . '#' . $joueurAdversaire->getLicence()]['resultats'] = $resultatMatches;
                            }
                        }
                    }

                    $journee['domicile'] = $domicile;
                    $journee['exempt'] = false;
                    $journee['nomAdversaireBis'] = mb_convert_case($nomAdversaireBis, MB_CASE_TITLE, "UTF-8");
                    $journee['resultat'] = $resultat;
                    $journee['joueurs'] = $joueursAdversaireFormatted;
                    $journee['doubles'] = $matchesDoubles;
                    $journee['errorMatchSheet'] = $errorMatchSheet;
                }
                $journees[] = $journee;
            }
        } catch(Exception $e) {
            $erreur = 'Liste des joueurs adversaires indisponible';
        }

        return new JsonResponse($this->render('ajax/lastComposAdversaire.html.twig', [
            'journees' => $journees,
            'erreur' => $erreur,
            'nomAdversaire' => mb_convert_case($nomAdversaire, MB_CASE_TITLE, "UTF-8")
        ])->getContent());
    }

    /**
     * Renvoie un template du classement des points virtuels mensuels et de la phase des joueurs compétiteurs
     * @Route("/journee/general-classement-virtuel", name="index.generalClassementsVirtuels", methods={"POST"})
     * @return JsonResponse
     */
    public function getClassementVirtuelsClub(): JsonResponse {
        set_time_limit(intval($this->getParameter('time_limit_ajax')));
        $classementProgressionMensuel = [];
        $classementPointsSaison = [];
        $classementProgressionSaison = [];
        $classementProgressionPhase = [];
        $classementPointsMensuel = [];
        $classementPointsPhase = [];
        $erreur = null;

        try {
            $competiteurs = $this->competiteurRepository->findJoueursByRole('Competiteur', null);
            $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
            $classementProgressionMensuel = array_map(function($joueur) use ($api) {
                $virtualPoint = null;
                if ($joueur->getLicence()) {
                    try {
                        $virtualPoint = $api->getJoueurVirtualPoints($joueur->getLicence());
                    } catch (Exception $e) {}
                }
                return [
                    'idCompetiteur' => $joueur->getIdCompetiteur(),
                    'nom' => $joueur->getNom() . ' ' . $joueur->getPrenom(),
                    'hasLicence' => (bool)$joueur->getLicence(),
                    'avatar' => ($joueur->getAvatar() ? 'images/profile_pictures/' . $joueur->getAvatar() : 'images/account.png'),
                    'pointsVirtuelsPointsWonSaison' => $virtualPoint && $joueur->getLicence() && $virtualPoint->getVirtualPoints() != 0.0 ? $virtualPoint->getSeasonlyPointsWon() : null,
                    'pointsVirtuelsPointsWonMensuel' => $virtualPoint && $joueur->getLicence() && $virtualPoint->getVirtualPoints() != 0.0 ? $virtualPoint->getPointsWon() : null,
                    'pointsVirtuelsVirtualPoints' => $virtualPoint && $joueur->getLicence() && $virtualPoint->getVirtualPoints() != 0.0 ? $virtualPoint->getVirtualPoints() : null,
                    'pointsVirtuelsPointsWonPhase' => $virtualPoint && $joueur->getLicence() ? $virtualPoint->getVirtualPoints() - $joueur->getClassementOfficiel() : null
                ];
            }, $competiteurs);
            $classementProgressionSaison = $classementProgressionMensuel;
            $classementPointsSaison = $classementProgressionMensuel;
            $classementProgressionPhase = $classementProgressionMensuel;
            $classementPointsMensuel = $classementProgressionMensuel;
            $classementPointsPhase = $classementProgressionMensuel;

            /** Classement sur la saison selon les progressions */
            usort($classementProgressionSaison, function ($a, $b) {
                if (!$a['pointsVirtuelsVirtualPoints']) return true;
                else if (!$b['pointsVirtuelsVirtualPoints']) return false;

                if ($a['pointsVirtuelsPointsWonSaison'] == $b['pointsVirtuelsPointsWonSaison']) {
                    return $b['pointsVirtuelsVirtualPoints'] > $a['pointsVirtuelsVirtualPoints'];
                }
                return $b['pointsVirtuelsPointsWonSaison'] > $a['pointsVirtuelsPointsWonSaison'];
            });

            /** Table de référence pour le calcul des gaps */
            $referenceTable = array_map(function($joueur) {
                $joueur['pointsVirtuelsVirtualPoints'] = $joueur['pointsVirtuelsVirtualPoints'] - $joueur['pointsVirtuelsPointsWonSaison'];
                return $joueur;
            }, $classementProgressionSaison);

            usort($referenceTable, function ($a, $b) {
                if (!$a['pointsVirtuelsVirtualPoints']) return true;
                else if (!$b['pointsVirtuelsVirtualPoints']) return false;

                if ($a['pointsVirtuelsVirtualPoints'] == $b['pointsVirtuelsVirtualPoints']) {
                    return $b['pointsVirtuelsPointsWonSaison'] > $a['pointsVirtuelsPointsWonSaison'];
                }
                return $b['pointsVirtuelsVirtualPoints'] > $a['pointsVirtuelsVirtualPoints'];
            });

            $referenceTable = array_map(function ($joueur) {
                return $joueur['idCompetiteur'];
            }, $referenceTable);

            /** Classement sur la saison selon les points */
            usort($classementPointsSaison, function ($a, $b) {
                if (!$a['pointsVirtuelsVirtualPoints']) return true;
                else if (!$b['pointsVirtuelsVirtualPoints']) return false;

                if ($a['pointsVirtuelsVirtualPoints'] == $b['pointsVirtuelsVirtualPoints']) {
                    return $b['pointsVirtuelsPointsWonSaison'] > $a['pointsVirtuelsPointsWonSaison'];
                }
                return $b['pointsVirtuelsVirtualPoints'] > $a['pointsVirtuelsVirtualPoints'];
            });

            /** Tableau général des gaps */
            $gaps = $this->getGaps($referenceTable, $classementPointsSaison);

            $classementPointsSaison = $this->getClassementVirtuelClubGapped($gaps, $classementPointsSaison);

            /** Classement mensuel selon les progressions */
            usort($classementProgressionMensuel, function ($a, $b) {
                if (!$a['pointsVirtuelsVirtualPoints']) return true;
                else if (!$b['pointsVirtuelsVirtualPoints']) return false;

                if ($a['pointsVirtuelsPointsWonMensuel'] == $b['pointsVirtuelsPointsWonMensuel']) {
                    return $b['pointsVirtuelsVirtualPoints'] > $a['pointsVirtuelsVirtualPoints'];
                }
                return $b['pointsVirtuelsPointsWonMensuel'] > $a['pointsVirtuelsPointsWonMensuel'];
            });

            /** Classement de phase selon les progressions */
            usort($classementProgressionPhase, function ($a, $b) {
                if (!$a['pointsVirtuelsVirtualPoints']) return true;
                else if (!$b['pointsVirtuelsVirtualPoints']) return false;

                if ($a['pointsVirtuelsPointsWonPhase'] == $b['pointsVirtuelsPointsWonPhase']) {
                    return $b['pointsVirtuelsVirtualPoints'] > $a['pointsVirtuelsVirtualPoints'];
                }
                return $b['pointsVirtuelsPointsWonPhase'] > $a['pointsVirtuelsPointsWonPhase'];
            });

            /** Classement mensuel selon les points */
            usort($classementPointsMensuel, function ($a, $b) {
                if (!$a['pointsVirtuelsVirtualPoints']) return true;
                else if (!$b['pointsVirtuelsVirtualPoints']) return false;

                if ($a['pointsVirtuelsVirtualPoints'] == $b['pointsVirtuelsVirtualPoints']) {
                    return $b['pointsVirtuelsPointsWonMensuel'] > $a['pointsVirtuelsPointsWonMensuel'];
                }
                return $b['pointsVirtuelsVirtualPoints'] > $a['pointsVirtuelsVirtualPoints'];
            });
            $classementPointsMensuel = $this->getClassementVirtuelClubGapped($gaps, $classementPointsMensuel);

            /** Classement de phase selon les points */
            usort($classementPointsPhase, function ($a, $b) {
                if (!$a['pointsVirtuelsVirtualPoints']) return true;
                else if (!$b['pointsVirtuelsVirtualPoints']) return false;

                if ($a['pointsVirtuelsVirtualPoints'] == $b['pointsVirtuelsVirtualPoints']) {
                    return $b['pointsVirtuelsPointsWonPhase'] > $a['pointsVirtuelsPointsWonPhase'];
                }
                return $b['pointsVirtuelsVirtualPoints'] > $a['pointsVirtuelsVirtualPoints'];
            });
            $classementPointsPhase = $this->getClassementVirtuelClubGapped($gaps, $classementPointsPhase);
        } catch(Exception $exception) {
            $erreur = 'Classement virtuel général indisponible.';
        }

        return new JsonResponse($this->render('ajax/classementVirtualPoints.html.twig', [
            'classementProgressionSaison' => $classementProgressionSaison,
            'classementProgressionMensuel' => $classementProgressionMensuel,
            'classementProgressionPhase' => $classementProgressionPhase,
            'classementPointsMensuel' => $classementPointsMensuel,
            'classementPointsPhase' => $classementPointsPhase,
            'classementPointsSaison' => $classementPointsSaison,
            'erreur' => $erreur,
        ])->getContent());
    }

    /**
     * Retourne les gaps des joueurs (places gagnées/perdues des joueurs au cours de la saison)
     * @param array $referenceTable
     * @param array $classements
     * @return array
     */
    public function getGaps(array $referenceTable, array $classements): array {
        $gaps = [];

        foreach ($classements as $key => $joueur){
            $gap = (array_keys(array_filter($referenceTable, function ($idCompetiteur) use($joueur) {
                    return $idCompetiteur == $joueur['idCompetiteur'];
                }))[0]) - $key;

            if ($gap == 0) $gaps[$joueur['idCompetiteur']] = ['color' => 'grey', 'gap' => 0, 'increase' => false];
            else $gaps[$joueur['idCompetiteur']] = [
                'color' => $gap > 0 ? 'green' : 'red',
                'gap' => $gap,
                'increase' => $gap > 0
            ];
        }

        return $gaps;
    }

    /**
     * Retourne le classement virtuel gappé
     * @param array $gaps
     * @param array $classementToGap
     * @return array
     */
    public function getClassementVirtuelClubGapped(array $gaps, array $classementToGap): array {
        return array_map(function($classement) use ($gaps) {
            $classement['gap'] = $gaps[$classement['idCompetiteur']];
            return $classement;
        }, $classementToGap);
    }

    /**
     * Renvoie un template des points virtuels mensuels de l'utilisateur actif avec un historique sur les 8 dernières phases
     * @Route("/journee/personnal-classement-virtuel", name="index.personnelClassementVirtuel", methods={"POST"})
     * @return JsonResponse
     */
    public function getPersonnalClassementVirtuelsClub(): JsonResponse {
        set_time_limit(intval($this->getParameter('time_limit_ajax')));
        $erreur = null;
        $virtualPointsProgression = null;
        $points = null;
        $annees = null;

        $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
        $virtualPoints = new VirtualPoints(0, 0, 0);
        if ($this->getUser()->getLicence()) {
            try {
                $virtualPoints = $api->getJoueurVirtualPoints($this->getUser()->getLicence());
                $virtualPointsProgression = $virtualPoints->getSeasonlyPointsWon();
                $virtualPoints = $virtualPoints->getVirtualPoints();
                $historique = array_slice($api->getHistoriqueJoueurByLicence($this->getUser()->getLicence()), -8);
                $points = array_map(function($histo) {
                    return $histo->getPoints();
                }, $historique);
                $annees = array_map(function($histo) {
                    return $histo->getAnneeFin();
                }, $historique);
            } catch (Exception $e) {
                $erreur = 'Points virtuels indisponibles';
            }
        } else $erreur = 'Licence du joueur inexistante';

        return new JsonResponse($this->render('ajax/personnalVirtualPoints.html.twig', [
            'virtualPoints' => $virtualPoints,
            'virtualPointsProgression' => $virtualPointsProgression,
            'erreur' => $erreur,
            'points' => $points,
            'annees' => $annees
        ])->getContent());
    }

    /**
     * Renvoie un template du classement de la poule de l'équipe cliquée
     * @Route("/journee/classement-poule", name="index.classementPoule", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getClassementPoule(Request $request): JsonResponse {
        set_time_limit(intval($this->getParameter('time_limit_ajax')));
        $classementPoule = [];
        $erreur = null;
        $ourTeam = null;

        try {
            $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
            $lienDivision = $request->request->get('lienDivision');
            $classementPouleAPI = $api->getClassementPouleByLienDivision($lienDivision);
            $points = null;
            $classement = 0;

            foreach ($classementPouleAPI as $equipe) {
                if ($points != $equipe->getPoints()) $classement++;
                if (!$ourTeam) $ourTeam = str_contains(mb_convert_case($equipe->getNomEquipe(), MB_CASE_LOWER, "UTF-8"), mb_convert_case($this->getParameter('club_name'), MB_CASE_LOWER, "UTF-8")) ? mb_convert_case($equipe->getNomEquipe(), MB_CASE_TITLE, "UTF-8") : null;

                $classementPoule[] = [
                    'nom' => mb_convert_case($equipe->getNomEquipe(), MB_CASE_TITLE, "UTF-8"),
                    'points' => $equipe->getPoints(),
                    'classement' => $points != $equipe->getPoints() ? $classement : null,
                    'isOurClub' => str_contains(mb_convert_case($equipe->getNomEquipe(), MB_CASE_LOWER, "UTF-8"), mb_convert_case($this->getParameter('club_name'), MB_CASE_LOWER, "UTF-8")) ? 'bold' : null
                ];
                $points = $equipe->getPoints();
            }
        } catch(Exception $exception) {
            $erreur = 'Classement de la poule indisponible';
        }

        return new JsonResponse($this->render('ajax/classementPoule.html.twig', [
            'classementPoule' => $classementPoule,
            'erreur' => $erreur,
            'ourTeam' => $ourTeam
        ])->getContent());
    }
}