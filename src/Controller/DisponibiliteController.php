<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use App\Repository\DisponibiliteRepository;
use App\Repository\JourneeRepository;
use App\Repository\RencontreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DisponibiliteController extends AbstractController
{
    private $em;
    private $journeeRepository;
    private $disponibiliteRepository;
    private $rencontreRepository;

    /**
     * @param EntityManagerInterface $em
     * @param JourneeRepository $journeeRepository
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param RencontreRepository $rencontreRepository
     */
    public function __construct(EntityManagerInterface $em,
                                JourneeRepository $journeeRepository,
                                DisponibiliteRepository $disponibiliteRepository,
                                RencontreRepository $rencontreRepository)
    {
        $this->em = $em;
        $this->journeeRepository = $journeeRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->rencontreRepository = $rencontreRepository;
    }

    /**
     * @Route("/journee/disponibilite/new/{journee}/{dispo}", name="journee.disponibilite.new")
     * @param int $journee
     * @param bool $dispo
     * @return Response
     * @throws Exception
     */
    public function new(int $journee, bool $dispo):Response
    {
        if (!($journee = $this->journeeRepository->find($journee))) throw new Exception('Cette journée est inexistante', 500);

        if (sizeof($this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser(), 'idJournee' => $journee])) == 0) {
            $disponibilite = new Disponibilite($this->getUser(), $journee, $dispo, $journee->getIdChampionnat());

            $this->em->persist($disponibilite);
            $this->em->flush();
            $this->addFlash('success', 'Disponibilité signalée avec succès !');
        } else $this->addFlash('warning', 'Disponibilité déjà renseignée pour cette journée !');

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
     * @param InvalidSelectionController $invalidSelectionController
     * @return Response
     * @throws Exception
     */
    public function update(int $dispoJoueur, bool $dispo, InvalidSelectionController $invalidSelectionController) : Response
    {
        if (!($dispoJoueur = $this->disponibiliteRepository->find($dispoJoueur))) throw new Exception('Cette disponibilité est inexistante', 500);

        $dispoJoueur->setDisponibilite($dispo);
        $journee = $dispoJoueur->getIdJournee()->getIdJournee();

        /** On supprime le joueur des compositions d'équipe de la journée actuelle s'il est indisponible */
        //TODO Faire à la main sans requête
        if (!$dispo){
            $nbMaxJoueurs = $this->rencontreRepository->getNbJoueursMaxJournee($journee)['nbMaxJoueurs'];
            $invalidSelectionController->deleteInvalidSelectedPlayers($this->rencontreRepository->getSelectedWhenIndispo($this->getUser()->getIdCompetiteur(), $journee, $nbMaxJoueurs, $dispoJoueur->getIdChampionnat()->getIdChampionnat()), $nbMaxJoueurs);
        }

        $this->em->flush();
        $this->addFlash('success', 'Disponibilité modifiée avec succès !');

        return $this->redirectToRoute('journee.show',
            [
                'type' => $dispoJoueur->getIdChampionnat()->getIdChampionnat(),
                'id' => $journee
            ]
        );
    }
}
