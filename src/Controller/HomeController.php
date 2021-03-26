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
     */
    public function indexAction(): Response
    {
        $type = ($this->get('session')->get('type') != null ? $this->get('session')->get('type') : 'departementale');
        if ($type == 'departementale') $dates = $this->journeeDepartementaleRepository->findAllDates();
        else if ($type == 'paris') $dates = $this->journeeParisRepository->findAllDates();
        else $dates = $this->journeeDepartementaleRepository->findAllDates();
        $NJournee = 1;

        while ($NJournee <= 7 && !$dates[$NJournee - 1]["undefined"] && (int) (new DateTime())->diff($dates[$NJournee - 1]["date"])->format('%R%a') < 0){
            $NJournee++;
        }

        return $this->redirectToRoute('journee.show', [
            'type' => $type,
            'id' => $NJournee
        ]);
    }

    /**
     * @param string $type
     * @param int $id
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @Route("/journee/{type}/{id}", name="journee.show")
     */
    public function journee(string $type, int $id): Response
    {
        if ($type == 'departementale') {
            // On vérifie que la journée existe
            if ((!$journee = $this->journeeDepartementaleRepository->find($id))) throw $this->createNotFoundException('Journée inexistante');
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
        }
        else if ($type == 'paris') {
            if ((!$journee = $this->journeeParisRepository->find($id))) throw $this->createNotFoundException('Journée inexistante');
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
        }
        else throw $this->createNotFoundException('Championnat inexistant');

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
     */
    public function edit(string $type, $compo, Request $request, InvalidSelectionController $invalidSelectionController) : Response
    {
        $journees = $form = null;
        $nbJoueursDivision = $nbMaxJoueurs = 0;
        // TODO Fix $joueursBrules
        $joueursBrules = $brulageSelectionnables = $idEquipes = [];
        if ($type != ('departementale' || 'paris')) throw $this->createNotFoundException('Championnat inexistant');

        if ($type == 'departementale'){
            if (!($compo = $this->rencontreDepartementaleRepository->find($compo))) throw $this->createNotFoundException('Journée inexistante');
            $nbJoueursDivision = $compo->getIdEquipe()->getIdDivision()->getNbJoueursChamp($type);
            $nbMaxJoueurs = $this->divisionRepository->getMaxNbJoueursChamp($type);
            $idEquipesBrulage = $this->equipeDepartementalRepository->getIdEquipesBrulees('MAX');
            $brulageSelectionnables = $this->competiteurRepository->getBrulagesSelectionnables($type, $compo->getIdEquipe()->getNumero(), $compo->getIdJournee()->getIdJournee(), $idEquipesBrulage, $nbMaxJoueurs, $this->getParameter('limite_brulage_dep'));
            $form = $this->createForm(RencontreDepartementaleType::class, $compo, [
                'nbMaxJoueurs' => $nbMaxJoueurs,
                'limiteBrulage' => $this->getParameter('limite_brulage_dep')
            ]);
            $journees = $this->journeeDepartementaleRepository->findAll();
            $idEquipes = $this->equipeDepartementalRepository->getIdEquipesBrulees('MIN');
        }
        else if ($type == 'paris'){
            if (!($compo = $this->rencontreParisRepository->find($compo))) throw $this->createNotFoundException('Journée inexistante');
            $nbMaxJoueurs = $this->divisionRepository->getMaxNbJoueursChamp($type);
            $idEquipesBrulage = $this->equipeParisRepository->getIdEquipesBrulees('MAX');
            $brulageSelectionnables = $this->competiteurRepository->getBrulagesSelectionnables($type, $compo->getIdEquipe()->getNumero(), $compo->getIdJournee()->getIdJournee(), $idEquipesBrulage, $nbMaxJoueurs, $this->getParameter('limite_brulage_paris'));
            $form = $this->createForm(RencontreParisType::class, $compo, [
                'nbMaxJoueurs' => $nbMaxJoueurs,
                'limiteBrulage' => $this->getParameter('limite_brulage_paris')
            ]);
            $journees = $this->journeeParisRepository->findAll();
            $nbJoueursDivision = $compo->getIdEquipe()->getIdDivision()->getNbJoueursChamp($type);
            $idEquipes = $this->equipeParisRepository->getIdEquipesBrulees('MIN');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** Liste des joueurs brûlés en J2 */
            $joueursBrulesRegleJ2 = array_column(array_filter($brulageSelectionnables, function($joueur)
                {   return ($joueur["bruleJ2"]);    }
            ), 'idCompetiteur');

            /** On vérifie qu'il n'y aie pas 2 joueurs brûlés ou + sélectionnés pour respecter la règle de la J2 **/
            $nbJoueursBruleJ2 = 0;
            for ($i = 0; $i < $nbJoueursDivision; $i++) {
                if ($form->getData()->getIdJoueurN($i) && in_array($form->getData()->getIdJoueurN($i)->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
            }

            if ($nbJoueursBruleJ2 >= 2) $this->addFlash('fail', $nbJoueursBruleJ2 . ' joueurs brûlés sont sélectionnés (règle de la J2 en rouge)');
            else {
                /** On vérifie si le joueur devient brûlé dans de futures compositions **/
                for ($i = 0; $i < $nbJoueursDivision; $i++) {
                    if ($form->getData()->getIdJoueurN($i)) $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueurN($i)->getIdCompetiteur(), count($journees), $nbMaxJoueurs);
                }
                $this->em->flush();
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
     */
    public function emptyComposition(string $type, int $idRencontre, bool $fromTemplate, int $nbJoueurs) : Response
    {
        $compo = null;
        if ($type == 'departementale'){
            if (!($compo = $this->rencontreDepartementaleRepository->find($idRencontre))) throw $this->createNotFoundException('Rencontre inexistante');
        }
        else if ($type == 'paris'){
            if (!($compo = $this->rencontreParisRepository->find($idRencontre))) throw $this->createNotFoundException('Rencontre inexistante');
        }
        else throw $this->createNotFoundException('Championnat inexistant');

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