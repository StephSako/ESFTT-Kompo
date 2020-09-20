<?php

namespace App\Controller;

use App\Form\RencontreDepartementaleType;
use App\Form\RencontreParisBasType;
use App\Form\RencontreParisHautType;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use App\Repository\JourneeParisRepository;
use App\Repository\RencontreDepartementaleRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\RencontreParisRepository;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use FFTTApi\Exception\InvalidURIParametersException;
use FFTTApi\Exception\JoueurNotFound;
use FFTTApi\Exception\NoFFTTResponseException;
use FFTTApi\Exception\URIPartNotValidException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FFTTApi\FFTTApi;

class HomeController extends AbstractController
{
    private $em;
    private $competiteurRepository;
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
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                JourneeParisRepository $journeeParisRepository,
                                DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                CompetiteurRepository $competiteurRepository,
                                RencontreDepartementaleRepository $rencontreDepartementaleRepository,
                                RencontreParisRepository $rencontreParisRepository,
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
    }

    /**
     * @Route("/test_api", name="test_api")
     */
    public function testApiFFTTAction(){
        $api = new FFTTApi("SW405", "d7ZG56dQKf");
        try {
            var_dump($api->getEquipesByClub("08951331"));
        } catch (InvalidURIParametersException $e) {var_dump($e->getMessage());
        } catch (NoFFTTResponseException $e) {var_dump($e->getMessage());
        } catch (URIPartNotValidException $e) {var_dump($e->getMessage());
        } catch (JoueurNotFound $e) {var_dump($e->getMessage());
        }

        return new Response('');
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

        while ($NJournee <= 7 && (int) (new DateTime())->diff($dates[$NJournee]["date"])->format('%R%a') <= 0){
            $NJournee++;
        }

        return $this->redirectToRoute('journee.show', [
            'type' => $type,
            'id' => $NJournee
        ]);
    }

    /**
     * @param $type
     * @param $id
     * @Route("/journee/{type}/{id}", name="journee.show")
     * @return Response
     * @throws DBALException
     */
    public function journee($type, $id)
    {
        if ($type == 'departementale'){
            $this->get('session')->set('type', $type);
            $journees = $this->journeeDepartementaleRepository->findAll();

            if ((!$journee = $this->journeeDepartementaleRepository->find($id))) throw $this->createNotFoundException('Journée inexistante');

            $disposJoueur = $this->getUser() ? $this->disponibiliteDepartementaleRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]) : null;
            $joueursDeclares = $this->disponibiliteDepartementaleRepository->findAllDisposByJournee($id);
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, 'disponibilite_departementale');
            $compos = $this->rencontreDepartementaleRepository->findBy(['idJournee' => $id]);
            $selectedPlayers = $this->rencontreDepartementaleRepository->getSelectedPlayers($compos);
            $brulages = $this->competiteurRepository->getCompetiteurBrulageDepartemental($journee->getIdJournee());

            if (array_key_exists($journee->getIdJournee(), $this->getUser()->getDisposDepartementales())) $disponible = $this->getUser()->getDisposDepartementales()[$journee->getIdJournee()];
            else $disponible = null;
        }
        else if ($type == 'paris'){
            $this->get('session')->set('type', $type);
            $journees = $this->journeeParisRepository->findAll();

            if ((!$journee = $this->journeeParisRepository->find($id))) throw $this->createNotFoundException('Journée inexistante');

            $disposJoueur = $this->getUser() ? $this->disponibiliteParisRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]) : null;
            $joueursDeclares = $this->disponibiliteParisRepository->findAllDisposByJournee($id);
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, 'disponibilite_paris');
            $compos = $this->rencontreParisRepository->findBy(['idJournee' => $id]);
            $selectedPlayers = $this->rencontreParisRepository->getSelectedPlayers($compos);
            $brulages = $this->competiteurRepository->getCompetiteurBrulageParis($journee->getIdJournee());

            if (array_key_exists($journee->getIdJournee(), $this->getUser()->getDisposParis())) $disponible = $this->getUser()->getDisposParis()[$journee->getIdJournee()];
            else $disponible = null;
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        $classement = array("1"=>[], "2"=>[], "3"=>[], "4"=>[]);

        $nbDispos = count(array_filter($joueursDeclares, function($dispo)
            {
                return $dispo->getDisponibilite() & true;
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
            'disposJoueur' => $disposJoueur,
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
        $joueursBrules = $joueursPreBrules = [];

        if ($type == 'departementale'){
            if (!($compo = $this->rencontreDepartementaleRepository->find($compo))) throw $this->createNotFoundException('Journée inexistante');

            $selectionnables = $this->disponibiliteDepartementaleRepository->findSelectionnablesDepartementales($compo->getIdJournee()->getIdJournee(), $compo->getIdEquipe()->getIdEquipe());
            $form = $this->createForm(RencontreDepartementaleType::class, $compo);
            $journees = $this->journeeDepartementaleRepository->findAll();
        }
        else if ($type == 'paris'){
            if (!($compo = $this->rencontreParisRepository->find($compo))) throw $this->createNotFoundException('Journée inexistante');

            $selectionnables = $this->disponibiliteParisRepository->findSelectionnablesParis($compo->getIdJournee()->getIdJournee(), $compo->getIdEquipe()->getIdEquipe());
            $idEquipe = $compo->getIdEquipe()->getIdEquipe();
            $journees = $this->journeeParisRepository->findAll();

            if ($idEquipe == 1) $form = $this->createForm(RencontreParisHautType::class, $compo);
            else if ($idEquipe == 2) $form = $this->createForm(RencontreParisBasType::class, $compo);
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** On vérifie que le joueur n'est pas brûlé et selectionné dans de futures compositions **/
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

        if ($type == 'departementale'){
            $brulages = $this->competiteurRepository->getCompetiteurBrulageDepartemental($compo->getIdJournee()->getIdJournee());

            /** Formation de la liste des joueurs brûlés et pré-brûlés en championnat départemental **/
            foreach ($brulages as $nom => $brulage){
                switch ($compo->getIdEquipe()->getIdEquipe()){
                    case 1:
                        if ($brulage["E1"] == 1) array_push($joueursPreBrules, $nom);
                        break;
                    case 2:
                        if ($brulage["E1"] >= 2) array_push($joueursBrules, $nom);
                        else if ($brulage["E2"] == 1) array_push($joueursPreBrules, $nom);
                        break;
                    case 3:
                        if (!($brulage["E1"] < 2 && $brulage["E2"] < 2)) array_push($joueursBrules, $nom);
                        else if ($brulage["E3"] == 1) array_push($joueursPreBrules, $nom);
                        break;
                    case 4:
                        if (!($brulage["E1"] < 2 && $brulage["E2"] < 2 && $brulage["E3"] < 2)) array_push($joueursBrules, $nom);
                        break;
                }
            }
        }
        else if ($type == 'paris'){
            $brulages = $this->competiteurRepository->getCompetiteurBrulageParis($compo->getIdJournee()->getIdJournee());

            /** Formation de la liste des joueurs brûlés et pré-brûlés en championnat de Paris **/
            foreach ($brulages as $nom => $brulage) {
                switch ($compo->getIdEquipe()->getIdEquipe()){
                    case 1:
                        if ($brulage["E1"] >= 3) array_push($joueursBrules, $nom);
                        break;
                    case 2:
                        if ($brulage["E1"] == 2) array_push($joueursPreBrules, $nom);
                        break;
                }
            }
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        return $this->render('journee/edit.html.twig', [
            'joueursBrules' => $joueursBrules,
            'joueursPreBrules' => $joueursPreBrules,
            'selectionnables' => $selectionnables,
            'journees' => $journees,
            'compo' => $compo,
            'type' => $type,
            'form' => $form->createView()
        ]);
    }
}