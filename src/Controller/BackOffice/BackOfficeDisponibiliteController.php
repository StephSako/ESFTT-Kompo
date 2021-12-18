<?php

namespace App\Controller\BackOffice;

use App\Controller\InvalidSelectionController;
use App\Entity\Disponibilite;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteRepository;
use App\Repository\JourneeRepository;
use App\Repository\RencontreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeDisponibiliteController extends AbstractController
{
    private $em;
    private $disponibiliteRepository;
    private $competiteurRepository;
    private $journeeRepository;
    private $rencontreRepository;
    private $championnatRepository;
    private $invalidSelectionController;

    /**
     * BackOfficeController constructor.
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param JourneeRepository $journeeRepository
     * @param EntityManagerInterface $em
     * @param InvalidSelectionController $invalidSelectionController
     * @param ChampionnatRepository $championnatRepository
     * @param RencontreRepository $rencontreRepository
     */
    public function __construct(DisponibiliteRepository $disponibiliteRepository,
                                CompetiteurRepository $competiteurRepository,
                                JourneeRepository $journeeRepository,
                                EntityManagerInterface $em,
                                InvalidSelectionController $invalidSelectionController,
                                ChampionnatRepository $championnatRepository,
                                RencontreRepository $rencontreRepository)
    {
        $this->em = $em;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->journeeRepository = $journeeRepository;
        $this->rencontreRepository = $rencontreRepository;
        $this->championnatRepository = $championnatRepository;
        $this->invalidSelectionController = $invalidSelectionController;
    }

    /**
     * @Route("/backoffice/disponibilites", name="backoffice.disponibilites")
     * @return Response
     * @throws Exception
     */
    public function index(): Response
    {
        return $this->render('backoffice/disponibilites/index.html.twig', [
            'disponibilites' => $this->competiteurRepository->findAllDisponibilites($this->championnatRepository->findAll())
        ]);
    }

    /**
     * @Route("/backoffice/disponibilites/new", name="backoffice.disponibilite.new", methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function new(Request $request):Response
    {
        /** On récupère les paramètres */
        $idJournee = $request->request->get('idJournee');
        $disponibiliteBoolean = $request->request->get('disponibiliteBoolean');
        $idCompetiteur = $request->request->get('idCompetiteur');
        /** Message d'erreur */

        try {
            if (!($journee = $this->journeeRepository->find($idJournee))) throw new Exception('Journée inexistante', 1234);
            if (!($competiteur = $this->competiteurRepository->find($idCompetiteur))) throw new Exception('Membre inexistant', 1234);

            $disponibilite = new Disponibilite($competiteur, $journee, $disponibiliteBoolean, $journee->getIdChampionnat());

            $this->em->persist($disponibilite);
            $this->em->flush();
            $idDisponibilite = $disponibilite->getIdDisponibilite();
        } catch (Exception $e) {
            $response = new Response(json_encode($e->getCode() == 1234 ? $e->getMessage() : 'Disponibilité déjà renseignée pour cette journée'), 400);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        return new JsonResponse($this->render('ajax/backoffice/dispos/disponibilite.html.twig', [
            'idJournee' => $journee->getIdJournee(),
            'idCompetiteur' => $idCompetiteur,
            'idDisponibilite' => $idDisponibilite,
            'disponibiliteBoolean' => $disponibiliteBoolean,
        ])->getContent());
    }

    /**
     * @Route("/backoffice/disponibilites/update", name="backoffice.disponibilite.update", methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function update(Request $request):Response
    {
        /** On récupère les paramètres */
        $idDisponibilite = $request->request->get('idDisponibilite');
        $disponibiliteBoolean = $request->request->get('disponibiliteBoolean');
        $idCompetiteur = $request->request->get('idCompetiteur');
        $idJournee = $request->request->get('idJournee');
        /** Message d'erreur */
        $message = null;

        try {
            if (!($competiteur = $this->competiteurRepository->find($idCompetiteur))) throw new Exception('Membre inexistant', 1234);
            if (!($dispoJoueur = $this->disponibiliteRepository->find($idDisponibilite))) throw new Exception('Disponibilité inexistante', 1234);

            $dispoJoueur->setDisponibilite($disponibiliteBoolean);

            /** On supprime le joueur des compositions d'équipe de la journée actuelle s'il est indisponible */
            if (!$disponibiliteBoolean){
                $nbMaxJoueurs = $this->rencontreRepository->getNbJoueursMaxJournee($dispoJoueur->getIdJournee()->getIdJournee())['nbMaxJoueurs'];
                $invalidCompos = $this->rencontreRepository->getSelectedWhenIndispo($competiteur->getIdCompetiteur(), $dispoJoueur->getIdJournee()->getIdJournee(), $nbMaxJoueurs, $dispoJoueur->getIdChampionnat()->getIdChampionnat());
                $this->invalidSelectionController->deleteInvalidSelectedPlayers($invalidCompos, $nbMaxJoueurs, $competiteur->getIdCompetiteur());

                foreach ($invalidCompos as $compo){
                    /** Si le joueur devient indisponible et qu'il est sélectionné, on re-trie la composition d'équipe */
                    $compo['compo']->sortComposition();
                }
            }

            $this->em->flush();
        } catch (Exception $e) {
            $response = new Response(json_encode($e->getCode() == 1234 ? $e->getMessage() : "Une erreur s'est produite"), 400);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        return new JsonResponse($this->render('ajax/backoffice/dispos/disponibilite.html.twig', [
            'message' => $message,
            'idJournee' => $idJournee,
            'idDisponibilite' => $idDisponibilite,
            'idCompetiteur' => $idCompetiteur,
            'disponibiliteBoolean' => $disponibiliteBoolean,
        ])->getContent());
    }
}
