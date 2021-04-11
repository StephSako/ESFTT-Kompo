<?php

namespace App\Controller;

use App\Form\RencontreType;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteRepository;
use App\Repository\DivisionRepository;
use App\Repository\EquipeRepository;
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
    private $equipeRepository;
    private $championnatRepository;
    private $disponibiliteRepository;
    private $journeeRepository;
    private $rencontreRepository;
    private $divisionRepository;

    /**
     * @param JourneeRepository $journeeRepository
     * @param ChampionnatRepository $championnatRepository
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param RencontreRepository $rencontreRepository
     * @param EquipeRepository $equipeRepository
     * @param DivisionRepository $divisionRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeRepository $journeeRepository,
                                ChampionnatRepository $championnatRepository,
                                DisponibiliteRepository $disponibiliteRepository,
                                CompetiteurRepository $competiteurRepository,
                                RencontreRepository $rencontreRepository,
                                EquipeRepository $equipeRepository,
                                DivisionRepository $divisionRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreRepository = $rencontreRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->journeeRepository = $journeeRepository;
        $this->championnatRepository = $championnatRepository;
        $this->equipeRepository = $equipeRepository;
        $this->divisionRepository = $divisionRepository;
    }

    /**
     * @Route("/", name="index")
     * @throws Exception
     */
    public function indexAction(): Response
    {
        if ($this->get('session')->get('type')){
            if ((!$championnat = $this->championnatRepository->find($this->get('session')->get('type')))) throw new Exception('Ce championnat est inexistant', 500);
        } else $championnat = $this->championnatRepository->getFirstChamp()[0];
        dump($championnat);

        $dates = $this->journeeRepository->findAllDates($championnat->getIdChampionnat());
        $idJournee = 1;

        while ($idJournee <= 7 && !$dates[$idJournee - 1]["undefined"] && (int) (new DateTime())->diff($dates[$idJournee - 1]["dateJournee"])->format('%R%a') < 0){
            $idJournee++;
        }

        return $this->redirectToRoute('journee.show', [
            'type' => $championnat->getIdChampionnat(),
            'id' => $idJournee
        ]);
    }

    /**
     * @Route("/journee/{type}", name="index.type")
     * @param int $type
     * @return Response
     * @throws Exception
     */
    public function indexTypeAction(int $type): Response
    {
        if ((!$championnat = $this->championnatRepository->find($type))) throw new Exception('Ce championnat est inexistant', 500);
        $dates = $this->journeeRepository->findAllDates($type);
        $idJournee = 1;

        while ($idJournee <= 7 && !$dates[$idJournee - 1]["undefined"] && (int) (new DateTime())->diff($dates[$idJournee - 1]["dateJournee"])->format('%R%a') < 0){
            $idJournee++;
        }

        return $this->redirectToRoute('journee.show', [
            'type' => $championnat->getNom(),
            'id' => $idJournee
        ]);
    }

    /**
     * @param int $type
     * @param int $id
     * @return Response
     * @throws Exception
     * @Route("/journee/{type}/{id}", name="journee.show")
     */
    public function journee(int $type, int $id): Response
    {
        if ((!$championnat = $this->championnatRepository->find($type))) throw new Exception('Ce championnat est inexistant', 500);
        if ((!$journee = $this->journeeRepository->find($id))) throw new Exception('Cette journée est inexistante', 500);

        $this->get('session')->set('type', $type);

        // Toutes les journées du type de championnat visé
        $journees = $this->journeeRepository->findAll();

        // Objet Disponibilité du joueur
        $dispoJoueur = $this->disponibiliteRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]);

        // Joueurs ayant déclaré leur disponibilité
        $joueursDeclares = $this->disponibiliteRepository->findJoueursDeclares($id);

        // Joueurs n'ayant pas déclaré leur disponibilité
        $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, $type);

        // Compositions d'équipe
        $compos = $this->rencontreRepository->getRencontres($id, $type);

        // Joueurs sélectionnées
        $selectedPlayers = $this->rencontreRepository->getSelectedPlayers($compos);

        // Nombre maximal de joueurs pour les compos du championnat départemental
        $nbTotalJoueurs = array_sum(array_map(function($compo) use ($type) {
            return $compo->getIdEquipe()->getIdDivision()->getNbJoueurs();
        }, $compos));

        // Id des équipes valides
        $idEquipesVisuel = $this->equipeRepository->getIdEquipesBrulees('MIN', $type);
        $idEquipesBrulage = $this->equipeRepository->getIdEquipesBrulees('MAX', $type);

        // Nombre minimal critique de joueurs pour les compos du championnat départemental
        $nbMinJoueurs = array_sum(array_map(function($compo) use ($type) {
            return $compo->getIdEquipe()->getIdDivision()->getNbJoueurs() - 1;
        }, $compos));

        // Equipes sans divisions affiliées
        $equipesSansDivision = $this->equipeRepository->getEquipesSansDivision();


        $nbDispos = count(array_filter($joueursDeclares, function($dispo)
            {
                return $dispo->getDisponibilite();
            }
        ));

        // Si l'utilisateur actuel est disponible pour la journée actuelle
        $disponible = ($dispoJoueur ? $dispoJoueur->getDisponibilite() : null);

        // Si l'utilisateur actuel est sélectionné pour la journée actuelle
        $selected = in_array($this->getUser()->getIdCompetiteur(), $selectedPlayers);

        // TODO Classer en fonction des championnats
        $allDisponibilites = $this->competiteurRepository->findAllDisposRecapitulatif($type, count($journees));
        $nbJournees = count($journees);

        // Brûlages des joueurs
        $brulages = $this->competiteurRepository->getBrulages($type, $journee->getIdJournee(), $idEquipesBrulage, $this->getParameter('nb_max_joueurs'));

        return $this->render('journee/index.html.twig', [
            'journee' => $journee,
            'equipesSansDivision' => $equipesSansDivision,
            'journees' => $journees,
            'nbTotalJoueurs' => $nbTotalJoueurs,
            'nbMinJoueurs' => $nbMinJoueurs,
            'selected' => $selected,
            'championnat' => $championnat,
            'compos' => $compos,
            'idEquipes' => $idEquipesVisuel,
            'selectedPlayers' => $selectedPlayers,
            'dispos' => $joueursDeclares,
            'disponible' => $disponible,
            'joueursNonDeclares' => $joueursNonDeclares,
            'dispoJoueur' => $dispoJoueur,
            'nbDispos' => $nbDispos,
            'nbJournees' => $nbJournees,
            'brulages' => $brulages,
            'allDisponibilites' => $allDisponibilites,
        ]);
    }

    /**
     * @Route("/composition/{type}/edit/{compo}", name="composition.edit")
     * @param int $type
     * @param $compo
     * @param Request $request
     * @param InvalidSelectionController $invalidSelectionController
     * @return Response
     * @throws Exception
     */
    public function edit(int $type, $compo, Request $request, InvalidSelectionController $invalidSelectionController) : Response
    {
        if ((!$championnat = $this->championnatRepository->find($type))) throw new Exception('Ce championnat est inexistant', 500);
        if (!($compo = $this->rencontreRepository->find($compo))) throw new Exception('Cette journée est inexistante', 500);
        if (!$compo->getIdEquipe()->getIdDivision()) throw new Exception('Cette rencontre n\'est pas modifiable car l\'équipe n\'a pas de division associée', 500);

        $nbMaxJoueurs = $this->divisionRepository->getMaxNbJoueursChamp($type);
        $idEquipesBrulage = $this->equipeRepository->getIdEquipesBrulees('MAX', $type);
        $brulageSelectionnables = $this->competiteurRepository->getBrulagesSelectionnables($championnat->isJ2Rule(), $type, $compo->getIdEquipe()->getNumero(), $compo->getIdJournee()->getIdJournee(), $idEquipesBrulage, $nbMaxJoueurs, $championnat->getLimiteBrulage());
        $form = $this->createForm(RencontreType::class, $compo, [
            'nbMaxJoueurs' => $nbMaxJoueurs,
            'limiteBrulage' => $championnat->getLimiteBrulage()
        ]);
        $journees = $this->journeeRepository->findAll();
        $idEquipes = $this->equipeRepository->getIdEquipesBrulees('MIN', $type);

        $joueursBrules = $this->competiteurRepository->getBrulesDansEquipe($compo->getIdEquipe()->getNumero(), $compo->getIdJournee()->getIdJournee(), $type, $nbMaxJoueurs, $this->getParameter('limite_brulage_' . $type));
        $nbJoueursDivision = $compo->getIdEquipe()->getIdDivision()->getNbJoueurs();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nbJoueursBruleJ2 = 0;
            if ($championnat->isJ2Rule()){
                /** Liste des joueurs brûlés en J2 pour les championnats ayant cette règle */
                $joueursBrulesRegleJ2 = array_column(array_filter($brulageSelectionnables, function($joueur)
                {   return ($joueur["bruleJ2"]);    }
                ), 'idCompetiteur');

                /** On vérifie qu'il n'y aie pas 2 joueurs brûlés ou + sélectionnés pour respecter la règle de la J2 **/
                for ($i = 0; $i < $nbJoueursDivision; $i++) {
                    if ($form->getData()->getIdJoueurN($i) && in_array($form->getData()->getIdJoueurN($i)->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
                }
            }

            if ($nbJoueursBruleJ2 >= 2) $this->addFlash('fail', $nbJoueursBruleJ2 . ' joueurs brûlés sont sélectionnés (règle de la J2 en rouge)');
            else {
                /** On sauvegarde la composition d'équipe */
                try {
                    $this->em->flush();

                    /** On vérifie que chaque joueur qui devient brûlé pour de futures compositions y soit désélectionné **/
                    for ($i = 0; $i < $nbJoueursDivision; $i++) {
                        if ($form->getData()->getIdJoueurN($i)) $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueurN($i)->getIdCompetiteur(), count($journees), $nbMaxJoueurs);
                    }
                    $this->em->flush();
                } catch (Exception $e) {
                    if ($e->getPrevious()->getCode() == "23000"){
                        if (str_contains($e->getMessage(), 'CHK_joueurs')) $this->addFlash('fail', 'Un joueur ne peut être sélectionné qu\'une seule fois');
                    }
                    else $this->addFlash('fail', 'Le formulaire n\'est pas valide');

                    return $this->render('journee/edit.html.twig', [
                        'joueursBrules' => $joueursBrules,
                        'journees' => $journees,
                        'nbJoueursDivision' => $nbJoueursDivision,
                        'brulageSelectionnables' => $brulageSelectionnables,
                        'idEquipes' => $idEquipes,
                        'compo' => $compo,
                        'type' => $championnat->getNom(),
                        'form' => $form->createView()
                    ]);
                }

                $this->addFlash('success', 'Composition modifiée avec succès !');

                return $this->redirectToRoute('journee.show', [
                    'type' => $championnat->getSlug(),
                    'id' => $compo->getIdJournee()->getIdJournee()
                ]);
            }
        }

        return $this->render('journee/edit.html.twig', [
            'joueursBrules' => $joueursBrules,
            'journees' => $journees,
            'nbJoueursDivision' => $nbJoueursDivision,
            'brulageSelectionnables' => $brulageSelectionnables,
            'idEquipes' => $idEquipes,
            'compo' => $compo,
            'type' => $championnat->getNom(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/composition/empty/{type}/{idRencontre}/{fromTemplate}/{nbJoueurs}", name="composition.vider")
     * @param int $type
     * @param int $idRencontre
     * @param bool $fromTemplate // Affiche le flash uniquement s'il est activé depuis le template journee/index.html.twig
     * @param int $nbJoueurs
     * @return Response
     * @throws Exception
     */
    public function emptyComposition(int $type, int $idRencontre, bool $fromTemplate, int $nbJoueurs) : Response
    {
        if ((!$championnat = $this->championnatRepository->find($type))) throw new Exception('Ce championnat est inexistant', 500);
        if (!($compo = $this->rencontreRepository->find($idRencontre))) throw new Exception('Cette rencontre est inexistante', 500);

        for ($i = 0; $i < $nbJoueurs; $i++){
            $compo->setIdJoueurN($i, null);
        }

        $this->em->flush();
        if ($fromTemplate) $this->addFlash('success', 'Composition vidée avec succès !');

        return $this->redirectToRoute('journee.show', [
            'type' => $championnat->getSlug(),
            'id' => $compo->getIdJournee()->getIdJournee()
        ]);
    }
}