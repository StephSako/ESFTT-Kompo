<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\RencontreDepartementaleType;
use App\Form\RencontreParisBasType;
use App\Form\RencontreParisHautType;
use App\Notification\ContactNotification;
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
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

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
    public function indexAction()
    {
        $type = ($this->get('session')->get('type') ?: 'departementale');
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
     * @param CacheInterface $cache
     * @param FFTTApiController $apiController
     * @return Response
     * @throws InvalidArgumentException
     * @Route("/journee/{type}/{id}", name="journee.show")
     */
    public function journee(string $type, int $id, CacheInterface $cache, FFTTApiController $apiController)
    {
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

            // Disponibilité du joueur
            $disponible = ($dispoJoueur ? $dispoJoueur->getDisponibilite() : null);
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
            $disponible = ($dispoJoueur ? $dispoJoueur->getDisponibilite() : null);
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        /** Génération des classements des poules grâce à l'API de la FFTT stockés dans le cache */
        $classement = $apiController->getClassement($cache, $type);

        $nbDispos = count(array_filter($joueursDeclares, function($dispo)
            {
                return $dispo->getDisponibilite();
            }
        ));

        return $this->render('journee/index.html.twig', [
            'journee' => $journee,
            'journees' => $journees,
            'compos' => $compos,
            'selectedPlayers' => $selectedPlayers,
            'dispos' => $joueursDeclares,
            'disponible' => $disponible,
            'joueursNonDeclares' => $joueursNonDeclares,
            'dispoJoueur' => $dispoJoueur,
            'nbDispos' => $nbDispos,
            'brulages' => $brulages,
            'classement' => $classement,
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
        $form = $idEquipe = $selectionnables = $journees = null;
        $joueursBrules = $futursSelectionnes = [];

        if ($type == 'departementale'){
            if (!($compo = $this->rencontreDepartementaleRepository->find($compo))) throw $this->createNotFoundException('Journée inexistante');

            $selectionnables = $this->disponibiliteDepartementaleRepository->findJoueursSelectionnables($compo->getIdJournee()->getIdJournee(), $compo->getIdEquipe()->getIdEquipe());

            $brulesJ2 = $this->rencontreDepartementaleRepository->getBrulesJ2($compo->getIdEquipe());
            $form = $this->createForm(RencontreDepartementaleType::class, $compo);
            $journees = $this->journeeDepartementaleRepository->findAll();

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
            $idEquipe = $compo->getIdEquipe()->getIdEquipe();
            $journees = $this->journeeParisRepository->findAll();

            if ($idEquipe == 1) $form = $this->createForm(RencontreParisHautType::class, $compo);
            else if ($idEquipe == 2) $form = $this->createForm(RencontreParisBasType::class, $compo);

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

            if ($type == 'departementale' || ($type == 'paris' && $idEquipe == 1)) {
                if ($form->getData()->getIdJoueur4()) if (in_array($form->getData()->getIdJoueur4()->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
                    if ($type == 'paris' && $idEquipe == 1) {
                        if ($form->getData()->getIdJoueur5()) if (in_array($form->getData()->getIdJoueur5()->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
                        if ($form->getData()->getIdJoueur6()) if (in_array($form->getData()->getIdJoueur6()->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
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

                if ($type == 'departementale' || ($type == 'paris' && $idEquipe == 1)) {
                    $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueur4());

                    if ($type == 'paris' && $idEquipe == 1) {
                        $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueur5());
                        $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueur6());
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

    /**
     * @Route("/notifySelectedPlayers/{type}/{idCompo}", name="notify.selectedPlayers")
     * @param $type
     * @param $idCompo
     * @param ContactNotification $contactNotification
     * @param Request $request
     * @return Response
     */
    public function notifySelectedPlayersAction($type, $idCompo, ContactNotification $contactNotification, Request $request)
    {
        $titre = $request->request->get('titre');
        $message = $request->request->get('message');

        $compo = null;
        if ($type == 'departementale') {
            $compo = $this->rencontreDepartementaleRepository->find($idCompo);
            $json = json_encode(['message' => $contactNotification->notify((new Contact())->setTitre($titre)->setMessage($message)->setCompetiteurs($compo->getListSelectedPlayers()), $this->getUser())]);
        }
        else if ($type == 'paris') {
            $compo = $this->rencontreParisRepository->find($idCompo);
            $contactNotification->notify((new Contact())->setTitre($titre)->setMessage($message)->setCompetiteurs($compo->getListSelectedPlayers()), $this->getUser()->getIdCompetiteur());
            $json = json_encode(['message' => $contactNotification->notify((new Contact())->setTitre($titre)->setMessage($message)->setCompetiteurs($compo->getListSelectedPlayers()), $this->getUser())]);
        }
        else $json = json_encode(['message' => 'Championnat inexistant ...']);

        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}