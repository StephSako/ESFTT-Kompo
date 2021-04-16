<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use App\Repository\ChampionnatRepository;
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
    private $championnatRepository;

    /**
     * @param EntityManagerInterface $em
     * @param JourneeRepository $journeeRepository
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param ChampionnatRepository $championnatRepository
     * @param RencontreRepository $rencontreRepository
     */
    public function __construct(EntityManagerInterface $em,
                                JourneeRepository $journeeRepository,
                                DisponibiliteRepository $disponibiliteRepository,
                                ChampionnatRepository $championnatRepository,
                                RencontreRepository $rencontreRepository)
    {
        $this->em = $em;
        $this->journeeRepository = $journeeRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->rencontreRepository = $rencontreRepository;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/journee/disponibilite/new/{journee}/{type}/{dispo}", name="journee.disponibilite.new")
     * @param int $journee
     * @param int $type
     * @param int $dispo
     * @return Response
     * @throws Exception
     */
    public function new(int $journee, int $type, int $dispo):Response
    {
        if ((!$championnat = $this->championnatRepository->find($type))) throw new Exception('Ce championnat est inexistant', 500);
        $competiteur = $this->getUser();

        if (!($journee = $this->journeeRepository->find($journee))) throw new Exception('Cette journée est inexistante', 500);
        if (sizeof($this->disponibiliteRepository->findBy(['idCompetiteur' => $competiteur, 'idJournee' => $journee])) == 0) {
            $disponibilite = new Disponibilite($competiteur, $journee, $dispo, $championnat);

            $this->em->persist($disponibilite);
            $this->em->flush();
            $this->addFlash('success', 'Disponibilité signalée avec succès !');
        } else $this->addFlash('warning', 'Disponibilité déjà renseignée pour cette journée !');

        return $this->redirectToRoute('journee.show',
            [
                'type' => $type,
                'id' => $journee->getIdJournee()
            ]
        );
    }

    /**
     * @Route("/journee/disponibilite/update/{type}/{dispoJoueur}/{dispo}", name="journee.disponibilite.update")
     * @param int $type
     * @param int $dispoJoueur
     * @param bool $dispo
     * @param InvalidSelectionController $invalidSelectionController
     * @return Response
     * @throws Exception
     */
    public function update(int $type, int $dispoJoueur, bool $dispo, InvalidSelectionController $invalidSelectionController) : Response
    {
        if (!$this->championnatRepository->find($type)) throw new Exception('Ce championnat est inexistant', 500);
        if (!($disposJoueur = $this->disponibiliteRepository->find($dispoJoueur))) throw new Exception('Cette disponibilité est inexistante', 500);

        $disposJoueur->setDisponibilite($dispo);
        $journee = $disposJoueur->getIdJournee()->getIdJournee();

        /** On supprime le joueur des compositions d'équipe de la journée actuelle s'il est indisponible */
        if (!$dispo) $invalidSelectionController->deleteInvalidSelectedPlayers($this->rencontreRepository->getSelectedWhenIndispo($this->getUser()->getIdCompetiteur(), $journee, $this->getParameter('nb_max_joueurs'), $type), $this->getParameter('nb_max_joueurs'));

        $this->em->flush();
        $this->addFlash('success', 'Disponibilité modifiée avec succès !');

        return $this->redirectToRoute('journee.show',
            [
                'type' => $type,
                'id' => $journee
            ]
        );
    }
}
