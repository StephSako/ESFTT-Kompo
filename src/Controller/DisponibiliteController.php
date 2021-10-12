<?php

namespace App\Controller;

use App\Entity\Disponibilite;
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

class DisponibiliteController extends AbstractController
{
    private $em;
    private $journeeRepository;
    private $disponibiliteRepository;
    private $rencontreRepository;
    private $invalidSelectionController;
    private $competiteurRepository;

    /**
     * @param EntityManagerInterface $em
     * @param JourneeRepository $journeeRepository
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param InvalidSelectionController $invalidSelectionController
     * @param RencontreRepository $rencontreRepository
     */
    public function __construct(EntityManagerInterface $em,
                                JourneeRepository $journeeRepository,
                                DisponibiliteRepository $disponibiliteRepository,
                                CompetiteurRepository $competiteurRepository,
                                InvalidSelectionController $invalidSelectionController,
                                RencontreRepository $rencontreRepository)
    {
        $this->em = $em;
        $this->journeeRepository = $journeeRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->rencontreRepository = $rencontreRepository;
        $this->invalidSelectionController = $invalidSelectionController;
        $this->competiteurRepository = $competiteurRepository;
    }

    /**
     * @Route("/journee/disponibilite/new/{journee}/{dispo}", name="journee.disponibilite.new")
     * @param int $journee
     * @param bool $dispo
     * @return Response
     * @throws Exception
     */
    public function newDraft(int $journee, bool $dispo):Response
    {
        if (!($journee = $this->journeeRepository->find($journee))) {
            $this->addFlash('fail', 'Journée inexistante');
            return $this->redirectToRoute('index');
        }

        try {
            $disponibilite = new Disponibilite($this->getUser(), $journee, $dispo, $journee->getIdChampionnat());

            $this->em->persist($disponibilite);
            $this->em->flush();
            $this->addFlash('success', 'Disponibilité enregistrée');
        } catch (Exception $e) {
            $this->addFlash('warning', 'Disponibilité déjà renseignée pour cette journée');
        }

        return $this->redirectToRoute('journee.show',
            [
                'type' => $journee->getIdChampionnat()->getIdChampionnat(),
                'id' => $journee->getIdJournee()
            ]
        );
    }

    /**
     * @Route("/journee/disponibilite/update/{dispoJoueur}/{dispo}", name="journee.disponibilite.update")
     * @param int $dispoJoueur
     * @param bool $dispo
     * @return Response
     * @throws Exception
     */
    public function update(int $dispoJoueur, bool $dispo) : Response //TODO Merge avec new
    {
        if (!($dispoJoueur = $this->disponibiliteRepository->find($dispoJoueur))) {
            $this->addFlash('fail', 'Disponibilité inexistante');
            return $this->redirectToRoute('index');
        }

        $dispoJoueur->setDisponibilite($dispo);
        $journee = $dispoJoueur->getIdJournee()->getIdJournee();

        /** On supprime le joueur des compositions d'équipe de la journée actuelle s'il est indisponible */
        if (!$dispo){
            $nbMaxJoueurs = $this->rencontreRepository->getNbJoueursMaxJournee($journee)['nbMaxJoueurs'];
            $this->invalidSelectionController->deleteInvalidSelectedPlayers($this->rencontreRepository->getSelectedWhenIndispo($this->getUser()->getIdCompetiteur(), $journee, $nbMaxJoueurs, $dispoJoueur->getIdChampionnat()->getIdChampionnat()), $nbMaxJoueurs);
        }

        $this->em->flush();
        $this->addFlash('success', 'Disponibilité modifiée');

        return $this->redirectToRoute('journee.show',
            [
                'type' => $dispoJoueur->getIdChampionnat()->getIdChampionnat(),
                'id' => $journee
            ]
        );
    }

    /**
     * @Route("/disponibilites/new", name="disponibilite.new", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request):Response
    {
        /** On récupère les paramètres */
        $journee = $request->request->get('journee');
        $dispo = $request->request->get('dispo');
        $idCompetiteur = $request->request->get('idCompetiteur');

        /** Message d'erreur */
        $error = false;

        $idDisponibilite = null;

        if (!($journee = $this->journeeRepository->find($journee))) $error = 'Journée inexistante';
        if (!($competiteur = $this->competiteurRepository->find($idCompetiteur))) $error = 'Compétiteur inexistant';

        try {
            $disponibilite = new Disponibilite($competiteur, $journee, $dispo, $journee->getIdChampionnat());

            $this->em->persist($disponibilite);
            $this->em->flush();
            $idDisponibilite = $disponibilite->getIdDisponibilite();
            $message = 'Disponibilité enregistrée';
        } catch (Exception $e) {
            $message = 'Disponibilité déjà renseignée pour cette journée';
            $error = true;
        }

        return new JsonResponse($this->render('ajax/backoffice/dispos/defined.html.twig', [
            'error' => $error,
            'message' => $message,
            'idDisponibilite' => $idDisponibilite,
            'idCompetiteur' => $idCompetiteur,
            'disponibilite' => $dispo,
        ])->getContent());
    }
}
