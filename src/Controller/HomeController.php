<?php

namespace App\Controller;

use App\Form\RencontreDepartementaleType;
use App\Form\RencontreParisType;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use App\Repository\DivisionRepository;
use App\Repository\EquipeDepartementaleRepository;
use App\Repository\EquipeParisRepository;
use App\Repository\JourneeParisRepository;
use App\Repository\RencontreDepartementaleRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\RencontreParisRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $em;
    private $competiteurRepository;
    private $equipeDepartementalRepository;
    private $equipeParisRepository;
    private $disponibiliteDepartementaleRepository;
    private $disponibiliteParisRepository;
    private $journeeDepartementaleRepository;
    private $journeeParisRepository;
    private $rencontreDepartementaleRepository;
    private $rencontreParisRepository;
    private $divisionRepository;

    /**
     * @param JourneeDepartementaleRepository $journeeDepartementaleRepository
     * @param JourneeParisRepository $journeeParisRepository
     * @param DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param RencontreDepartementaleRepository $rencontreDepartementaleRepository
     * @param RencontreParisRepository $rencontreParisRepository
     * @param EquipeDepartementaleRepository $equipeDepartementalRepository
     * @param EquipeParisRepository $equipeParisRepository
     * @param DivisionRepository $divisionRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                JourneeParisRepository $journeeParisRepository,
                                DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                CompetiteurRepository $competiteurRepository,
                                RencontreDepartementaleRepository $rencontreDepartementaleRepository,
                                RencontreParisRepository $rencontreParisRepository,
                                EquipeDepartementaleRepository $equipeDepartementalRepository,
                                EquipeParisRepository $equipeParisRepository,
                                DivisionRepository $divisionRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreDepartementaleRepository = $rencontreDepartementaleRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->disponibiliteDepartementaleRepository = $disponibiliteDepartementaleRepository;
        $this->disponibiliteParisRepository = $disponibiliteParisRepository;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->rencontreParisRepository = $rencontreParisRepository;
        $this->equipeDepartementalRepository = $equipeDepartementalRepository;
        $this->equipeParisRepository = $equipeParisRepository;
        $this->divisionRepository = $divisionRepository;
    }

    /**
     * @Route("/", name="index")
     * @throws Exception
     */
    public function indexAction(): Response
    {
        $type = ($this->get('session')->get('type') != null ? $this->get('session')->get('type') : 'departementale');
        if ($type == 'departementale') $dates = $this->journeeDepartementaleRepository->findAllDates();
        else if ($type == 'paris') $dates = $this->journeeParisRepository->findAllDates();
        else throw new Exception('Ce championnat est inexistant', 500);
        $idJournee = 1;

        while ($idJournee <= 7 && !$dates[$idJournee - 1]["undefined"] && (int) (new DateTime())->diff($dates[$idJournee - 1]["dateJournee"])->format('%R%a') < 0){
            $idJournee++;
        }

        return $this->redirectToRoute('journee.show', [
            'type' => $type,
            'id' => $idJournee
        ]);
    }

    /**
     * @Route("/journee/{type}", name="index.type")
     * @param string $type
     * @return Response
     * @throws Exception
     */
    public function indexTypeAction(string $type): Response
    {
        if ($type == 'departementale') $dates = $this->journeeDepartementaleRepository->findAllDates();
        else if ($type == 'paris') $dates = $this->journeeParisRepository->findAllDates();
        else throw new Exception('Ce championnat est inexistant', 500);
        $idJournee = 1;

        while ($idJournee <= 7 && !$dates[$idJournee - 1]["undefined"] && (int) (new DateTime())->diff($dates[$idJournee - 1]["dateJournee"])->format('%R%a') < 0){
            $idJournee++;
        }

        return $this->redirectToRoute('journee.show', [
            'type' => $type,
            'id' => $idJournee
        ]);
    }

    /**
     * @param string $type
     * @param int $id
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws Exception
     * @Route("/journee/{type}/{id}", name="journee.show")
     */
    public function journee(string $type, int $id): Response
    {
        if ($type == 'departementale') {
            // On vérifie que la journée existe
            if ((!$journee = $this->journeeDepartementaleRepository->find($id))) throw new Exception('Cette journée est inexistante', 500);
            $this->get('session')->set('type', $type);

            // Toutes les journées du type de championnat visé
            $journees = $this->journeeDepartementaleRepository->findAll();

            // Objet Disponibilité du joueur
            $dispoJoueur = $this->disponibiliteDepartementaleRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]);

            // Joueurs ayant déclaré leur disponibilité
            $joueursDeclares = $this->disponibiliteDepartementaleRepository->findJoueursDeclares($id);

            // Joueurs n'ayant pas déclaré leur disponibilité
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, $type);

            // Compositions d'équipe
            $compos = $this->rencontreDepartementaleRepository->getRencontresDepartementales($id);

            // Joueurs sélectionnées
            $selectedPlayers = $this->rencontreDepartementaleRepository->getSelectedPlayers($compos);

            // Nombre maximal de joueurs pour les compos du championnat départemental
            $nbTotalJoueurs = array_sum(array_map(function($compo) use ($type) {
                return $compo->getIdEquipe()->getIdDivision()->getNbJoueursChampDepartementale();
            }, $compos));

            // Id des équipes valides
            $idEquipesVisuel = $this->equipeDepartementalRepository->getIdEquipesBrulees('MIN');
            $idEquipesBrulage = $this->equipeDepartementalRepository->getIdEquipesBrulees('MAX');

            // Nombre minimal critique de joueurs pour les compos du championnat départemental
            $nbMinJoueurs = array_sum(array_map(function($compo) use ($type) {
                return $compo->getIdEquipe()->getIdDivision()->getNbJoueursChampDepartementale() - 1;
            }, $compos));

            // Equipes sans divisions affiliées
            $equipesSansDivision = $this->equipeDepartementalRepository->getEquipesSansDivision();
        }
        else if ($type == 'paris') {
            if ((!$journee = $this->journeeParisRepository->find($id))) throw new Exception('Cette journée est inexistante', 500);
            $this->get('session')->set('type', $type);
            $journees = $this->journeeParisRepository->findAll();
            $dispoJoueur = $this->disponibiliteParisRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]);
            $joueursDeclares = $this->disponibiliteParisRepository->findJoueursDeclares($id);
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, $type);
            $compos = $this->rencontreParisRepository->getRencontresParis($id);
            $selectedPlayers = $this->rencontreParisRepository->getSelectedPlayers($compos);
            $idEquipesVisuel = $this->equipeParisRepository->getIdEquipesBrulees('MIN');
            $idEquipesBrulage = $this->equipeParisRepository->getIdEquipesBrulees('MAX');
            $nbTotalJoueurs = array_sum(array_map(function($compo) use ($type) {
                return $compo->getIdEquipe()->getIdDivision()->getNbJoueursChampParis();
            }, $compos));
            $nbMinJoueurs = array_sum(array_map(function($compo) use ($type) {
                return $compo->getIdEquipe()->getIdDivision()->getNbJoueursChampParis() - 1;
            }, $compos));
            $equipesSansDivision = $this->equipeParisRepository->getEquipesSansDivision();
        }
        else throw new Exception('Ce championnat est inexistant', 500);

        $nbDispos = count(array_filter($joueursDeclares, function($dispo)
            {
                return $dispo->getDisponibilite();
            }
        ));

        // Si l'utilisateur actuel est disponible pour la journée actuelle
        $disponible = ($dispoJoueur ? $dispoJoueur->getDisponibilite() : null);

        // Si l'utilisateur actuel est sélectionné pour la journée actuelle
        $selected = in_array($this->getUser()->getIdCompetiteur(), $selectedPlayers);

        $allDisponibilitesDepartementales = $this->competiteurRepository->findAllDisposRecapitulatif('departementale', count($journees));
        $allDisponibiliteParis = $this->competiteurRepository->findAllDisposRecapitulatif('paris', count($journees));
        $nbJournees = count($journees);

        // Brûlages des joueurs
        $brulages = $this->competiteurRepository->getBrulages($type, $journee->getIdJournee(), $idEquipesBrulage, $this->divisionRepository->getMaxNbJoueursChamp($type));

        return $this->render('journee/index.html.twig', [
            'journee' => $journee,
            'equipesSansDivision' => $equipesSansDivision,
            'journees' => $journees,
            'nbTotalJoueurs' => $nbTotalJoueurs,
            'nbMinJoueurs' => $nbMinJoueurs,
            'selected' => $selected,
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
            'allDisponibilitesDepartementales' => $allDisponibilitesDepartementales,
            'allDisponibiliteParis' => $allDisponibiliteParis
        ]);
    }

    /**
     * @Route("/composition/{type}/edit/{compo}", name="composition.edit")
     * @param string $type
     * @param $compo
     * @param Request $request
     * @param InvalidSelectionController $invalidSelectionController
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function edit(string $type, $compo, Request $request, InvalidSelectionController $invalidSelectionController) : Response
    {
        $journees = $form = null;
        $nbMaxJoueurs = 0;
        $brulageSelectionnables = $idEquipes = $joueursBrules = [];
        if ($type != ('departementale' || 'paris')) throw new Exception('Ce championnat est inexistant', 500);

        if ($type == 'departementale'){
            if (!($compo = $this->rencontreDepartementaleRepository->find($compo))) throw new Exception('Cette journée est inexistante', 500);
            if (!$compo->getIdEquipe()->getIdDivision()) throw new Exception('Cette rencontre n\'est pas modifiable car l\'équipe n\'a pas de division associée', 500);
            $nbMaxJoueurs = $this->divisionRepository->getMaxNbJoueursChamp($type);
            $idEquipesBrulage = $this->equipeDepartementalRepository->getIdEquipesBrulees('MAX');
            $brulageSelectionnables = $this->competiteurRepository->getBrulagesSelectionnables($type, $compo->getIdEquipe()->getNumero(), $compo->getIdJournee()->getIdJournee(), $idEquipesBrulage, $nbMaxJoueurs, $this->getParameter('limite_brulage_departementale'));
            $form = $this->createForm(RencontreDepartementaleType::class, $compo, [
                'nbMaxJoueurs' => $nbMaxJoueurs,
                'limiteBrulage' => $this->getParameter('limite_brulage_departementale')
            ]);
            $journees = $this->journeeDepartementaleRepository->findAll();
            $idEquipes = $this->equipeDepartementalRepository->getIdEquipesBrulees('MIN');
        }
        else if ($type == 'paris'){
            if (!($compo = $this->rencontreParisRepository->find($compo))) throw new Exception('Cette journée est inexistante', 500);
            if (!$compo->getIdEquipe()->getIdDivision()) throw new Exception('Cette rencontre n\'est pas modifiable car l\'équipe n\'a pas de division associée', 500);
            $nbMaxJoueurs = $this->divisionRepository->getMaxNbJoueursChamp($type);
            $idEquipesBrulage = $this->equipeParisRepository->getIdEquipesBrulees('MAX');
            $brulageSelectionnables = $this->competiteurRepository->getBrulagesSelectionnables($type, $compo->getIdEquipe()->getNumero(), $compo->getIdJournee()->getIdJournee(), $idEquipesBrulage, $nbMaxJoueurs, $this->getParameter('limite_brulage_paris'));
            $form = $this->createForm(RencontreParisType::class, $compo, [
                'nbMaxJoueurs' => $nbMaxJoueurs,
                'limiteBrulage' => $this->getParameter('limite_brulage_paris')
            ]);
            $journees = $this->journeeParisRepository->findAll();
            $idEquipes = $this->equipeParisRepository->getIdEquipesBrulees('MIN');
        }
        $joueursBrules = $this->competiteurRepository->getBrulesDansEquipe($compo->getIdEquipe()->getNumero(), $compo->getIdJournee()->getIdJournee(), $type, $nbMaxJoueurs, $this->getParameter('limite_brulage_' . $type));
        $nbJoueursDivision = $compo->getIdEquipe()->getIdDivision()->getNbJoueursChamp($type);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nbJoueursBruleJ2 = 0;
            if ($type == 'departementale'){
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
                        'type' => $type,
                        'form' => $form->createView()
                    ]);
                }

                $this->addFlash('success', 'Composition modifiée avec succès !');

                return $this->redirectToRoute('journee.show', [
                    'type' => $compo->getIdJournee()->getLinkType(),
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
            'type' => $type,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/composition/empty/{type}/{idRencontre}/{fromTemplate}/{nbJoueurs}", name="composition.vider")
     * @param string $type
     * @param int $idRencontre
     * @param bool $fromTemplate // Affiche le flash uniquement s'il est activé depuis le template journee/index.html.twig
     * @param int $nbJoueurs
     * @return Response
     * @throws Exception
     */
    public function emptyComposition(string $type, int $idRencontre, bool $fromTemplate, int $nbJoueurs) : Response
    {
        $compo = null;
        if ($type == 'departementale'){
            if (!($compo = $this->rencontreDepartementaleRepository->find($idRencontre))) throw new Exception('Cette rencontre est inexistante', 500);
        }
        else if ($type == 'paris'){
            if (!($compo = $this->rencontreParisRepository->find($idRencontre))) throw new Exception('Cette rencontre est inexistante', 500);
        }
        else throw new Exception('Ce championnat est inexistant', 500);

        for ($i = 0; $i < $nbJoueurs; $i++){
            $compo->setIdJoueurN($i, null);
        }

        $this->em->flush();
        if ($fromTemplate) $this->addFlash('success', 'Composition vidée avec succès !');

        return $this->redirectToRoute('journee.show', [
            'type' => $compo->getIdJournee()->getLinkType(),
            'id' => $compo->getIdJournee()->getIdJournee()
        ]);
    }
}