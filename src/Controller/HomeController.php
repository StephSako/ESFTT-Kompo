<?php

namespace App\Controller;

use App\Form\RencontreType;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteRepository;
use App\Repository\JourneeRepository;
use App\Repository\RencontreRepository;
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
    private $journeeRepository;
    private $rencontreRepository;

    /**
     * @param JourneeRepository $journeeRepository
     * @param ChampionnatRepository $championnatRepository
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param RencontreRepository $rencontreRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeRepository $journeeRepository,
                                ChampionnatRepository $championnatRepository,
                                DisponibiliteRepository $disponibiliteRepository,
                                CompetiteurRepository $competiteurRepository,
                                RencontreRepository $rencontreRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreRepository = $rencontreRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->journeeRepository = $journeeRepository;
        $this->championnatRepository = $championnatRepository;
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
            $journees = $this->journeeRepository->findAllDates($championnat->getIdChampionnat());
            $idJournee = min(array_map(function ($journee){return $journee->getIdJournee();}, $championnat->getJournees()->toArray()));

            while ($idJournee <= $championnat->getNbJournees() && !$journees[$idJournee - 1]->getUndefined() && (int) (new DateTime())->diff($journees[$idJournee - 1]->getDateJournee())->format('%R%a') < 0 && $idJournee < $championnat->getNbJournees()){
                $idJournee++;
            }

            return $this->redirectToRoute('journee.show', [
                'type' => $championnat->getIdChampionnat(),
                'id' => $idJournee
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
            $idJournee = min(array_map(function ($journee) {
                return $journee->getIdJournee();
            }, $championnat->getJournees()->toArray()));

            while ($idJournee <= $championnat->getNbJournees() && !$championnat->getJournees()->toArray()[$idJournee - 1]->getUndefined() && (int)(new DateTime())->diff($championnat->getJournees()->toArray()[$idJournee - 1]->getDateJournee())->format('%R%a') < 0 && $idJournee < $championnat->getNbJournees()) {
                $idJournee++;
            }

            return $this->redirectToRoute('journee.show', [
                'type' => $championnat->getIdChampionnat(),
                'id' => $idJournee
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
        usort($journees, function($j1, $j2){
            return $j1->getDateJournee() > $j2->getDateJournee();
        });

        if (!in_array($id, array_map(function ($journee){ return $journee->getIdJournee(); }, $journees))) throw new Exception('Cette journée est inexistante', 500);
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
        $idEquipesVisuel = array_slice($equipesBrulage, 1, count($equipesBrulage));
        $idEquipesBrulage = array_slice($equipesBrulage, 0, count($equipesBrulage) - 1);

        // Nombre minimal critique de joueurs pour les compos du championnat départemental
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
            'idJournee' => array_search($journee, $journees)+1,
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
            'allDisponibilites' => $allDisponibilites,
        ]);
    }

    /**
     * @Route("/composition/{type}/edit/{compo}", name="composition.edit", requirements={"type"="\d+", "compo"="\d+"})
     * @param int $type
     * @param int $compo
     * @param Request $request
     * @param InvalidSelectionController $invalidSelectionController
     * @return Response
     * @throws Exception
     */
    public function edit(int $type, int $compo, Request $request, InvalidSelectionController $invalidSelectionController) : Response
    {
        if (!($championnat = $this->championnatRepository->find($type))) throw new Exception('Ce championnat est inexistant', 500);
        if (!($compo = $this->rencontreRepository->find($compo))) throw new Exception('Cette journée est inexistante', 500);
        if (!$compo->getIdEquipe()->getIdDivision()) throw new Exception('Cette rencontre n\'est pas modifiable car l\'équipe n\'a pas de division associée', 500);

        $allChampionnats = $this->championnatRepository->findAll();

        // Nombre de joueurs maximum par équipe du championnat
        $nbMaxJoueurs = max(array_map(function($division){return $division->getNbJoueurs();}, $championnat->getDivisions()->toArray()));

        // Numéros des équipes valides pour le brûlage
        $equipesBrulage = array_map(function($equipe){
            return $equipe->getNumero();
        }, array_filter($championnat->getEquipes()->toArray(), function($equipe){
            return $equipe->getIdDivision() != null;
        }));
        $idEquipesBrulageVisuel = array_slice($equipesBrulage, 1, count($equipesBrulage));
        $idEquipesBrulage = array_slice($equipesBrulage, 0, count($equipesBrulage) - 1);

        $brulageSelectionnables = $this->competiteurRepository->getBrulagesSelectionnables($championnat, $compo->getIdEquipe()->getNumero(), $compo->getIdJournee()->getIdJournee(), $idEquipesBrulage, $nbMaxJoueurs, $championnat->getLimiteBrulage());
        $form = $this->createForm(RencontreType::class, $compo, [
            'nbMaxJoueurs' => $nbMaxJoueurs,
            'limiteBrulage' => $championnat->getLimiteBrulage()
        ]);
        $journees = $championnat->getJournees()->toArray();
        usort($journees, function($j1, $j2){
            return $j1->getDateJournee() > $j2->getDateJournee();
        });

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

            if ($nbJoueursBruleJ2 >= 2) $this->addFlash('fail', $nbJoueursBruleJ2 . ' joueurs brûlés sont sélectionnés (règle de la J2 en rouge)');
            else {
                /** On sauvegarde la composition d'équipe */
                try {
                    $this->em->flush();

                    /** On vérifie que chaque joueur devenant brûlé pour de futures compositions y soit désélectionné **/
                    for ($i = 0; $i < $nbJoueursDivision; $i++) {
                        if ($form->getData()->getIdJoueurN($i)) $invalidSelectionController->checkInvalidSelection($championnat, $compo, $form->getData()->getIdJoueurN($i)->getIdCompetiteur(), $nbMaxJoueurs);
                    }

                    $this->em->flush();
                    $this->addFlash('success', 'Composition modifiée avec succès !');

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
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/composition/empty/{idCompo}", name="composition.vider", requirements={"idCompo"="\d+"})
     * @param int $idCompo
     * @return Response
     * @throws Exception
     */
    public function emptyComposition(int $idCompo) : Response
    {
        if (!($compo = $this->rencontreRepository->find($idCompo))) throw new Exception('Cette rencontre est inexistante', 500);

        for ($i = 0; $i < $compo->getIdEquipe()->getIdDivision()->getNbJoueurs(); $i++){
            $compo->setIdJoueurN($i, null);
        }

        $this->em->flush();
        $this->addFlash('success', 'Composition vidée avec succès !');
        return $this->redirectToRoute('journee.show', [
            'type' => $compo->getIdChampionnat()->getIdChampionnat(),
            'id' => $compo->getIdJournee()->getIdJournee()
        ]);
    }
}