<?php

namespace App\Controller;

use App\Form\RencontreDepartementaleType;
use App\Form\RencontreParisSixJoueursType;
use App\Form\RencontreParisTroisJoueursType;
use App\Form\RencontreParisNeufJoueursType;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
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
     * @Route("/journee/{type}/{id}", name="journee.show")
     */
    public function journee(string $type, int $id): Response
    {
        $nbJournees = $nbEquipes = null;
        if ($type == 'departementale') {
            // On vérifie que la journée existe
            if ((!$journee = $this->journeeDepartementaleRepository->find($id))) throw $this->createNotFoundException('Journée inexistante');
            $this->get('session')->set('type', $type);

            // Toutes les journées du type de championnat visé
            $journees = $this->journeeDepartementaleRepository->findAll();

            // Objet Disponibilité du joueur
            $dispoJoueur = $this->getUser() ? $this->disponibiliteDepartementaleRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]) : null;

            // Joueurs ayant déclaré leur disponibilité
            $joueursDeclares = $this->disponibiliteDepartementaleRepository->findJoueursDeclares($id);

            // Joueurs n'ayant pas déclaré leur disponibilité
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, $type);

            // Compositions d'équipe
            $compos = $this->rencontreDepartementaleRepository->findBy(['idJournee' => $id]);

            // Joueurs sélectionnées
            $selectedPlayers = $this->rencontreDepartementaleRepository->getSelectedPlayers($compos);

            // Brûlages des joueurs
            $brulages = $this->competiteurRepository->getBrulagesDepartemental($journee->getIdJournee());

            // Nombre d'équipes
            $nbEquipes = count($compos);

            // Nombre de journées
            $nbJournees = count($journees);
        }
        else if ($type == 'paris') {
            if ((!$journee = $this->journeeParisRepository->find($id))) throw $this->createNotFoundException('Journée inexistante');
            $this->get('session')->set('type', $type);
            $journees = $this->journeeParisRepository->findAll();
            $dispoJoueur = $this->getUser() ? $this->disponibiliteParisRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]) : null;
            $joueursDeclares = $this->disponibiliteParisRepository->findJoueursDeclares($id);
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, $type);
            $compos = $this->rencontreParisRepository->findBy(['idJournee' => $id]);
            $selectedPlayers = $this->rencontreParisRepository->getSelectedPlayers($compos);
            $brulages = $this->competiteurRepository->getBrulagesParis($journee->getIdJournee());
            $nbEquipes = count($compos);
            $nbJournees = count($journees);
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        $nbDispos = count(array_filter($joueursDeclares, function($dispo)
            {
                return $dispo->getDisponibilite();
            }
        ));
        $disponible = ($dispoJoueur ? $dispoJoueur->getDisponibilite() : null);

        return $this->render('journee/index.html.twig', [
            'journee' => $journee,
            'journees' => $journees,
            'compos' => $compos,
            'nbEquipes' => $nbEquipes,
            'selectedPlayers' => $selectedPlayers,
            'dispos' => $joueursDeclares,
            'disponible' => $disponible,
            'joueursNonDeclares' => $joueursNonDeclares,
            'dispoJoueur' => $dispoJoueur,
            'nbDispos' => $nbDispos,
            'nbJournees' => $nbJournees,
            'brulages' => $brulages,
            'allDisponibilitesDepartementales' => $this->competiteurRepository->findAllDisposRecapitulatif("departementale"),
            'allDisponibiliteParis' => $this->competiteurRepository->findAllDisposRecapitulatif("paris")
        ]);
    }

    /**
     * @Route("/composition/{type}/edit/{compo}", name="composition.edit")
     * @param string $type
     * @param $compo
     * @param Request $request
     * @param InvalidSelectionController $invalidSelectionController
     * @return Response
     */
    public function edit(string $type, $compo, Request $request, InvalidSelectionController $invalidSelectionController) : Response
    {
        $form = $divisionNbJoueursChampParis = $selectionnables = $journees = $nbEquipes = null;
        $joueursBrules = $futursSelectionnes = [];

        if ($type == 'departementale'){
            if (!($compo = $this->rencontreDepartementaleRepository->find($compo))) throw $this->createNotFoundException('Journée inexistante');

            $selectionnables = $this->disponibiliteDepartementaleRepository->findJoueursSelectionnables($compo->getIdJournee()->getIdJournee(), $compo->getIdEquipe()->getIdEquipe());

            $brulesJ2 = $this->rencontreDepartementaleRepository->getBrulesJ2($compo->getIdEquipe());
            $form = $this->createForm(RencontreDepartementaleType::class, $compo);
            $journees = $this->journeeDepartementaleRepository->findAll();
            try {
                $nbEquipes = $this->equipeDepartementalRepository->getNbEquipesDepartementales();
            } catch (NoResultException | NonUniqueResultException $e) {
                $nbEquipes = 0;
            }

            $brulages = $this->competiteurRepository->getBrulagesDepartemental($compo->getIdJournee()->getIdJournee());
            /** Formation de la liste des joueurs brûlés et pré-brûlés en championnat départemental **/
            foreach ($brulages as $joueur => $brulage){
                switch ($compo->getIdEquipe()->getIdEquipe()){
                    case 1:
                        if (in_array($joueur, $selectionnables)) {
                            $futursSelectionnes[$joueur] = $brulage;
                            $futursSelectionnes[$joueur]["idCompetiteur"] = $brulage["idCompetiteur"];
                            $futursSelectionnes[$joueur]["E1"] = intval($futursSelectionnes[$joueur]["E1"]);
                            $futursSelectionnes[$joueur]["E1"]++;
                        }
                        break;
                    case 2:
                        if ($brulage["E1"] >= 2) array_push($joueursBrules, $joueur);
                        else if (in_array($joueur, $selectionnables)) {
                            $futursSelectionnes[$joueur] = $brulage;
                            $futursSelectionnes[$joueur]["idCompetiteur"] = $brulage["idCompetiteur"];
                            $futursSelectionnes[$joueur]["E2"] = intval($futursSelectionnes[$joueur]["E2"]);
                            $futursSelectionnes[$joueur]["E2"]++;
                        }
                        break;
                    case 3:
                        if (($brulage["E1"] + $brulage["E2"]) >= 2) array_push($joueursBrules, $joueur);
                        else if (in_array($joueur, $selectionnables)) {
                            $futursSelectionnes[$joueur] = $brulage;
                            $futursSelectionnes[$joueur]["idCompetiteur"] = $brulage["idCompetiteur"];
                            $futursSelectionnes[$joueur]["E3"] = intval($futursSelectionnes[$joueur]["E3"]);
                            $futursSelectionnes[$joueur]["E3"]++;
                        }
                        break;
                    case 4:
                        if (($brulage["E1"] + $brulage["E2"] + $brulage["E3"]) >= 2) array_push($joueursBrules, $joueur);
                        else if (in_array($joueur, $selectionnables)) {
                            $futursSelectionnes[$joueur] = $brulage;
                            $futursSelectionnes[$joueur]["idCompetiteur"] = $brulage["idCompetiteur"];
                        }
                        break;
                }
            }
        }
        else if ($type == 'paris'){
            if (!($compo = $this->rencontreParisRepository->find($compo))) throw $this->createNotFoundException('Journée inexistante');

            $selectionnables = $this->disponibiliteParisRepository->findJoueursSelectionnables($compo->getIdJournee()->getIdJournee(), $compo->getIdEquipe()->getIdEquipe());

            $brulesJ2 = $this->rencontreParisRepository->getBrulesJ2($compo->getIdEquipe());
            $divisionNbJoueursChampParis = ($compo->getIdEquipe()->getIdDivision() ? $compo->getIdEquipe()->getIdDivision()->getNbJoueursChampParis() : null);
            $journees = $this->journeeParisRepository->findAll();
            try {
                $nbEquipes = $this->equipeParisRepository->getNbEquipesParis();
            } catch (NoResultException | NonUniqueResultException $e) {
                $nbEquipes = 0;
            }

            if ($divisionNbJoueursChampParis == 3) $form = $this->createForm(RencontreParisTroisJoueursType::class, $compo);
            else if ($divisionNbJoueursChampParis == 6) $form = $this->createForm(RencontreParisSixJoueursType::class, $compo);
            else if ($divisionNbJoueursChampParis == 9) $form = $this->createForm(RencontreParisNeufJoueursType::class, $compo);
            else $form = $this->createForm(RencontreParisNeufJoueursType::class, $compo);

            $brulages = $this->competiteurRepository->getBrulagesParis($compo->getIdJournee()->getIdJournee());
            /** Formation de la liste des joueurs brûlés et pré-brûlés en championnat de Paris **/
            foreach ($brulages as $joueur => $brulage){
                switch ($compo->getIdEquipe()->getIdEquipe()){
                    case 1:
                        if (in_array($joueur, $selectionnables)) {
                            $futursSelectionnes[$joueur] = $brulage;
                            $futursSelectionnes[$joueur]["idCompetiteur"] = $brulage["idCompetiteur"];
                            $futursSelectionnes[$joueur]["E1"] = intval($futursSelectionnes[$joueur]["E1"]);
                            $futursSelectionnes[$joueur]["E1"]++;
                        }
                        break;
                    case 2:
                        if ($brulage["E1"] >= 3) array_push($joueursBrules, $joueur);
                        else if (in_array($joueur, $selectionnables)) {
                            $futursSelectionnes[$joueur] = $brulage;
                            $futursSelectionnes[$joueur]["idCompetiteur"] = $brulage["idCompetiteur"];
                        }
                        break;
                }
            }
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        foreach ($futursSelectionnes as $joueur => $fields){
            if ($compo->getIdJournee()->getIdJournee() == 2 && $compo->getIdEquipe()->getIdEquipe() > 1) $futursSelectionnes[$joueur]["bruleJ2"] = (in_array($fields["idCompetiteur"], $brulesJ2) ? true : false);
            else $futursSelectionnes[$joueur]["bruleJ2"] = false;
        }

        $form->handleRequest($request);

        $joueursBrulesRegleJ2 = array_column(array_filter($futursSelectionnes, function($joueur)
            {   return ($joueur["bruleJ2"]);    }
        ), 'idCompetiteur');

        if ($form->isSubmitted() && $form->isValid()) {

            /** On vérifie qu'il n'y aie pas 2 joueurs brûlés pour la règle de la J2 **/
            $nbJoueursBruleJ2 = 0;

            if ($form->getData()->getIdJoueur1()) if (in_array($form->getData()->getIdJoueur1()->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
            if ($form->getData()->getIdJoueur2()) if (in_array($form->getData()->getIdJoueur2()->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
            if ($form->getData()->getIdJoueur3()) if (in_array($form->getData()->getIdJoueur3()->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;

            if ($type == 'departementale' || ($type == 'paris' && ($divisionNbJoueursChampParis == null || $divisionNbJoueursChampParis > 3))) {
                if ($form->getData()->getIdJoueur4()) if (in_array($form->getData()->getIdJoueur4()->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
                if ($type == 'paris' && ($divisionNbJoueursChampParis == null || $divisionNbJoueursChampParis > 3)) {
                    if ($form->getData()->getIdJoueur5()) if (in_array($form->getData()->getIdJoueur5()->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
                    if ($form->getData()->getIdJoueur6()) if (in_array($form->getData()->getIdJoueur6()->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
                }
                if ($type == 'paris' && ($divisionNbJoueursChampParis == null || $divisionNbJoueursChampParis > 6)) {
                    if ($form->getData()->getIdJoueur7()) if (in_array($form->getData()->getIdJoueur7()->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
                    if ($form->getData()->getIdJoueur8()) if (in_array($form->getData()->getIdJoueur8()->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
                    if ($form->getData()->getIdJoueur9()) if (in_array($form->getData()->getIdJoueur9()->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
                }
            }

            if ($nbJoueursBruleJ2 >= 2) $this->addFlash('fail', $nbJoueursBruleJ2 . ' joueurs brûlés sont sélectionnés (règle de la J2 en rouge)');
            else {
                /** On vérifie si le joueur devient brûlé dans de futures compositions **/
                $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueur1());
                $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueur2());
                $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueur3());

                if ($type == 'departementale' || ($type == 'paris' && ($divisionNbJoueursChampParis == null || $divisionNbJoueursChampParis > 3))) {
                    $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueur4());
                    if ($type == 'paris' && ($divisionNbJoueursChampParis == null || $divisionNbJoueursChampParis > 3)) {
                        $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueur5());
                        $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueur6());
                    }
                    if ($type == 'paris' && ($divisionNbJoueursChampParis == null || $divisionNbJoueursChampParis > 6)) {
                        $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueur7());
                        $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueur8());
                        $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueur9());
                    }
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
            'futursSelectionnes' => $futursSelectionnes,
            'journees' => $journees,
            'brulages' => $brulages,
            'nbEquipes' => $nbEquipes,
            'compo' => $compo,
            'type' => $type,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/composition/empty/{type}/{id}/{fromTemplate}", name="composition.vider")
     * @param string $type
     * @param int id
     * @param bool $fromTemplate // Affiche le flash uniquement s'il est activé depuis le template journee/index.html.twig
     * @return Response
     */
    public function emptyComposition(string $type, int $id, bool $fromTemplate) : Response
    {
        if (!($type == 'departementale' || $type == 'paris')) throw $this->createNotFoundException('Championnat inexistant');

        $compo = null;
        if ($type == 'departementale'){
            if (!($compo = $this->rencontreDepartementaleRepository->find($id))) throw $this->createNotFoundException('Rencontre inexistante');

            $compo->setIdJoueur1(null);
            $compo->setIdJoueur2(null);
            $compo->setIdJoueur3(null);
            $compo->setIdJoueur4(null);
        }
        else if ($type == 'paris'){
            if (!($compo = $this->rencontreParisRepository->find($id))) throw $this->createNotFoundException('Rencontre inexistante');

            $compo->setIdJoueur1(null);
            $compo->setIdJoueur2(null);
            $compo->setIdJoueur3(null);

            if ($compo->getIdEquipe()->getIdEquipe() == 1){
                $compo->setIdJoueur4(null);
                $compo->setIdJoueur5(null);
                $compo->setIdJoueur6(null);
                $compo->setIdJoueur7(null);
                $compo->setIdJoueur8(null);
                $compo->setIdJoueur9(null);
            }
        }

        $this->em->flush();
        if ($fromTemplate) $this->addFlash('success', 'Composition vidée avec succès !');

        return $this->redirectToRoute('journee.show', [
            'type' => $compo->getIdJournee()->getLinkType(),
            'id' => $compo->getIdJournee()->getIdJournee()
        ]);
    }
}