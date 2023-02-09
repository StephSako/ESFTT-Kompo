<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use App\Repository\DisponibiliteRepository;
use App\Repository\JourneeRepository;
use App\Repository\RencontreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
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
                'idJournee' => $journee->getIdJournee()
            ]
        );
    }

    /**
     * @Route("/journee/disponibilite/update/{dispoJoueur}/{dispo}", name="journee.disponibilite.update")
     * @param int $dispoJoueur
     * @param bool $dispo
     * @param UtilController $utilController
     * @return Response
     * @throws NonUniqueResultException
     */
    public function update(int $dispoJoueur, bool $dispo, UtilController $utilController) : Response
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
            $invalidCompos = $this->rencontreRepository->getSelectedWhenIndispo($this->getUser()->getIdCompetiteur(), $journee, $nbMaxJoueurs, $dispoJoueur->getIdChampionnat()->getIdChampionnat());
            $utilController->deleteInvalidSelectedPlayers($invalidCompos, $nbMaxJoueurs, $this->getUser()->getIdCompetiteur());

            foreach ($invalidCompos as $compo){
                /** Si le joueur devient indisponible et qu'il est sélectionné, on re-trie la composition d'équipe */
                $compo['compo']->sortComposition();
            }
        }

        $this->em->flush();
        $this->addFlash('success', 'Disponibilité modifiée');

        return $this->redirectToRoute('journee.show',
            [
                'type' => $dispoJoueur->getIdChampionnat()->getIdChampionnat(),
                'idJournee' => $journee
            ]
        );
    }
}
