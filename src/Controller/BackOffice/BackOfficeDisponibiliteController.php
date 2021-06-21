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
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('backoffice/disponibilites/index.html.twig', [
            'disponibilites' => $this->competiteurRepository->findAllDisponibilites($this->championnatRepository->findAll())
        ]);
    }

    /**
     * @Route("/backoffice/disponibilites/new/{idCompetiteur}/{journee}/{dispo}", name="backoffice.disponibilite.new")
     * @param int $journee
     * @param int $dispo
     * @param int $idCompetiteur
     * @return Response
     * @throws Exception
     */
    public function new(int $journee, int $dispo, int $idCompetiteur):Response
    {
        if (!($journee = $this->journeeRepository->find($journee))) {
            $this->addFlash('fail', 'Journée inexistante');
            return $this->redirectToRoute('backoffice.disponibilites');
        }
        if (!($competiteur = $this->competiteurRepository->find($idCompetiteur))) {
            $this->addFlash('fail', 'Compétiteur inexistant');
            return $this->redirectToRoute('backoffice.disponibilites');
        }

        //TODO Optimize & test
        if (sizeof($this->disponibiliteRepository->findBy(['idCompetiteur' => $competiteur, 'idJournee' => $journee, 'idChampionnat' => $journee->getIdChampionnat()->getIdChampionnat()])) == 0) {
            $disponibilite = new Disponibilite($competiteur, $journee, $dispo, $journee->getIdChampionnat());

            $this->em->persist($disponibilite);
            $this->em->flush();
            $this->addFlash('success', 'Disponibilité enregistrée');
        } else $this->addFlash('warning', 'Disponibilité déjà renseignée pour cette journée');

        return $this->redirectToRoute('backoffice.disponibilites');

        /*
         $json = json_encode([
            'message' => $message,
            'data' => $dispo ? 1 : 0
        ]);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        */
    }

    /**
     * @Route("/backoffice/disponibilites/update/{idCompetiteur}/{idDispo}/{dispo}", name="backoffice.disponibilite.update")
     * @param int $idCompetiteur
     * @param int $idDispo
     * @param bool $dispo
     * @param InvalidSelectionController $invalidSelectionController
     * @return Response
     * @throws Exception
     */
    public function update(int $idCompetiteur, int $idDispo, bool $dispo, InvalidSelectionController $invalidSelectionController) : Response
    {
        if (!($competiteur = $this->competiteurRepository->find($idCompetiteur))) {
            $this->addFlash('fail', 'Compétiteur inexistant');
            return $this->redirectToRoute('backoffice.disponibilites');
        }

        if (!($dispoJoueur = $this->disponibiliteRepository->find($idDispo))) {
            $this->addFlash('fail', 'Disponibilité inexistante');
            return $this->redirectToRoute('backoffice.disponibilites');
        }
        $dispoJoueur->setDisponibilite($dispo);

        /** On supprime le joueur des compositions d'équipe de la journée actuelle s'il est indisponible */
        //TODO Faire à la main sans requête
        if (!$dispo){
            $nbMaxJoueurs = $this->rencontreRepository->getNbJoueursMaxJournee($dispoJoueur->getIdJournee()->getIdJournee())['nbMaxJoueurs'];
            $invalidSelectionController->deleteInvalidSelectedPlayers($this->rencontreRepository->getSelectedWhenIndispo($competiteur->getIdCompetiteur(), $dispoJoueur->getIdJournee()->getIdJournee(), $nbMaxJoueurs, $dispoJoueur->getIdChampionnat()->getIdChampionnat()), $nbMaxJoueurs);
        }

        $this->em->flush();
        $this->addFlash('success', 'Disponibilité modifiée');

        return $this->redirectToRoute('backoffice.disponibilites');

        /*
         $json = json_encode([
            'message' => $message,
            'data' => $dispo ? 1 : 0
        ]);
        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        */
    }
}
