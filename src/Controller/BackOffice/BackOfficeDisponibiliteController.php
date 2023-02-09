<?php

namespace App\Controller\BackOffice;

use App\Controller\UtilController;
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

    /**
     * BackOfficeController constructor.
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param JourneeRepository $journeeRepository
     * @param EntityManagerInterface $em
     * @param ChampionnatRepository $championnatRepository
     * @param RencontreRepository $rencontreRepository
     */
    public function __construct(DisponibiliteRepository $disponibiliteRepository,
                                CompetiteurRepository $competiteurRepository,
                                JourneeRepository $journeeRepository,
                                EntityManagerInterface $em,
                                ChampionnatRepository $championnatRepository,
                                RencontreRepository $rencontreRepository)
    {
        $this->em = $em;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->journeeRepository = $journeeRepository;
        $this->rencontreRepository = $rencontreRepository;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/backoffice/disponibilites", name="backoffice.disponibilites")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function index(Request $request): Response
    {
        return $this->render('backoffice/disponibilites/index.html.twig', [
            'disponibilites' => $this->competiteurRepository->findAllDisponibilites($this->championnatRepository->findAll()),
            'active' => $request->query->get('active')
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

        return new JsonResponse($this->render('ajax/backoffice/disponibilite.html.twig', [
            'idJournee' => $journee->getIdJournee(),
            'idCompetiteur' => $idCompetiteur,
            'idDisponibilite' => $idDisponibilite,
            'disponibiliteBoolean' => $disponibiliteBoolean
        ])->getContent());
    }

    /**
     * @Route("/backoffice/disponibilites/update", name="backoffice.disponibilite.update", methods={"POST"})
     * @param Request $request
     * @param UtilController $utilController
     * @return Response
     */
    public function update(Request $request, UtilController $utilController):Response
    {
        /** On récupère les paramètres */
        $idDisponibilite = $request->request->get('idDisponibilite');
        $disponibiliteBoolean = $request->request->get('disponibiliteBoolean');
        $idCompetiteur = $request->request->get('idCompetiteur');
        $idJournee = $request->request->get('idJournee');

        try {
            if (!($competiteur = $this->competiteurRepository->find($idCompetiteur))) throw new Exception('Membre inexistant', 1234);
            if (!($dispoJoueur = $this->disponibiliteRepository->find($idDisponibilite))) throw new Exception('Disponibilité inexistante', 1234);

            $dispoJoueur->setDisponibilite($disponibiliteBoolean);

            /** On supprime le joueur des compositions d'équipe de la journée actuelle s'il est indisponible */
            if (!$disponibiliteBoolean){
                $nbMaxJoueurs = $this->rencontreRepository->getNbJoueursMaxJournee($dispoJoueur->getIdJournee()->getIdJournee())['nbMaxJoueurs'];
                $invalidCompos = $this->rencontreRepository->getSelectedWhenIndispo($competiteur->getIdCompetiteur(), $dispoJoueur->getIdJournee()->getIdJournee(), $nbMaxJoueurs, $dispoJoueur->getIdChampionnat()->getIdChampionnat());
                $utilController->deleteInvalidSelectedPlayers($invalidCompos, $nbMaxJoueurs, $competiteur->getIdCompetiteur());

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

        return new JsonResponse($this->render('ajax/backoffice/disponibilite.html.twig', [
            'idJournee' => $idJournee,
            'idDisponibilite' => $idDisponibilite,
            'idCompetiteur' => $idCompetiteur,
            'disponibiliteBoolean' => $disponibiliteBoolean
        ])->getContent());
    }

    /**
     * @Route("/backoffice/disponibilites/delete", name="backoffice.disponibilite.delete", methods={"POST"})
     * @param Request $request
     * @param UtilController $utilController
     * @return Response
     */
    public function delete(Request $request, UtilController $utilController):Response
    {
        /** On récupère les paramètres */
        $idDisponibilite = $request->request->get('idDisponibilite');
        $idCompetiteur = $request->request->get('idCompetiteur');
        $idJournee = $request->request->get('idJournee');

        try {
            if (!($competiteur = $this->competiteurRepository->find($idCompetiteur))) throw new Exception('Membre inexistant', 1234);
            if (!($dispoJoueur = $this->disponibiliteRepository->find($idDisponibilite))) throw new Exception('Disponibilité inexistante', 1234);

            /** On supprime le joueur des compositions d'équipe de la journée actuelle */
            $nbMaxJoueurs = $this->rencontreRepository->getNbJoueursMaxJournee($dispoJoueur->getIdJournee()->getIdJournee())['nbMaxJoueurs'];
            $invalidCompos = $this->rencontreRepository->getSelectedWhenIndispo($competiteur->getIdCompetiteur(), $dispoJoueur->getIdJournee()->getIdJournee(), $nbMaxJoueurs, $dispoJoueur->getIdChampionnat()->getIdChampionnat());
            $utilController->deleteInvalidSelectedPlayers($invalidCompos, $nbMaxJoueurs, $competiteur->getIdCompetiteur());

            foreach ($invalidCompos as $compo){
                /** Si le joueur devient indisponible et qu'il est sélectionné, on re-trie la composition d'équipe */
                $compo['compo']->sortComposition();
            }

            $this->em->remove($dispoJoueur);

            $this->em->flush();
        } catch (Exception $e) {
            $response = new Response(json_encode($e->getCode() == 1234 ? $e->getMessage() : "Une erreur s'est produite"), 400);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        return new JsonResponse($this->render('ajax/backoffice/disponibilite.html.twig', [
            'idJournee' => $idJournee,
            'idDisponibilite' => $idDisponibilite,
            'idCompetiteur' => $idCompetiteur,
            'disponibiliteBoolean' => null
        ])->getContent());
    }
}
