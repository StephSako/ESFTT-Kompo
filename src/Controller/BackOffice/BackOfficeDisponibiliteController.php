<?php

namespace App\Controller\BackOffice;

use App\Controller\InvalidSelectionController;
use App\Entity\Disponibilite;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteRepository;
use App\Repository\DivisionRepository;
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
    private $divisionRepository;
    private $championnatRepository;

    /**
     * BackOfficeController constructor.
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param JourneeRepository $journeeRepository
     * @param EntityManagerInterface $em
     * @param DivisionRepository $divisionRepository
     * @param ChampionnatRepository $championnatRepository
     * @param RencontreRepository $rencontreRepository
     */
    public function __construct(DisponibiliteRepository $disponibiliteRepository,
                                CompetiteurRepository $competiteurRepository,
                                JourneeRepository $journeeRepository,
                                EntityManagerInterface $em,
                                DivisionRepository $divisionRepository,
                                ChampionnatRepository $championnatRepository,
                                RencontreRepository $rencontreRepository)
    {
        $this->em = $em;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->journeeRepository = $journeeRepository;
        $this->rencontreRepository = $rencontreRepository;
        $this->divisionRepository = $divisionRepository;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/backoffice/disponibilites", name="backoffice.disponibilites")
     * @return Response
     */
    public function indexDisponibilites(): Response
    {
        return $this->render('backoffice/disponibilites/index.html.twig', [
            'disponibilites' => $this->competiteurRepository->findAllDisponibilites()
        ]);
    }

    /**
     * @Route("/backoffice/disponibilites/new/{idCompetiteur}/{journee}/{type}/{dispo}", name="backoffice.disponibilite.new")
     * @param int $journee
     * @param int $type
     * @param int $dispo
     * @param $idCompetiteur
     * @return Response
     * @throws Exception
     */
    public function new(int $journee, int $type, int $dispo, $idCompetiteur):Response
    {
        if (!($competiteur = $this->competiteurRepository->find($idCompetiteur))) throw new Exception('Ce compétiteur est inexistant', 500);
        if (!($championnat = $this->championnatRepository->find($type))) throw new Exception('Ce championnat est inexistant', 500);

        if (sizeof($this->disponibiliteRepository->findBy(['idCompetiteur' => $competiteur, 'idJournee' => $journee, 'idChampionnat' => $type])) == 0) {
            if (!($journee = $this->journeeRepository->find($journee))) throw new Exception('Cette journée est inexistante', 500);
            $disponibilite = new Disponibilite($competiteur, $journee, $dispo, $championnat);

            $this->em->persist($disponibilite);
            $this->em->flush();
            $this->addFlash('success', 'Disponibilité signalée avec succès !');
        } else $this->addFlash('warning', 'Disponibilité déjà renseignée pour cette journée !');

        return $this->redirectToRoute('backoffice.disponibilites');
    }

    /**
     * @Route("/backoffice/disponibilites/update/{idCompetiteur}/{type}/{disposJoueur}/{dispo}", name="backoffice.disponibilite.update")
     * @param int $type
     * @param int $idCompetiteur
     * @param int $disposJoueur
     * @param bool $dispo
     * @param InvalidSelectionController $invalidSelectionController
     * @return Response
     * @throws Exception
     */
    public function update(int $type, int $idCompetiteur, int $disposJoueur, bool $dispo, InvalidSelectionController $invalidSelectionController) : Response
    {
        if (!($competiteur = $this->competiteurRepository->find($idCompetiteur))) throw new Exception('Ce compétiteur est inexistante', 500);
        if (!$this->championnatRepository->find($type)) throw new Exception('Ce championnat est inexistant', 500);

        if (!($disposJoueur = $this->disponibiliteRepository->find($disposJoueur))) throw new Exception('Cette disponibilité est inexistante', 500);
        $disposJoueur->setDisponibilite($dispo);

        /** On supprime le joueur des compositions d'équipe de la journée actuelle s'il est indisponible */
        if (!$dispo){
            $nbMaxJoueurs = $this->divisionRepository->getMaxNbJoueursChamp($type);
            $invalidSelectionController->deleteInvalidSelectedPlayers($this->rencontreRepository->getSelectedWhenIndispo($competiteur->getIdCompetiteur(), $disposJoueur->getIdJournee()->getIdJournee(), $nbMaxJoueurs, $type), $nbMaxJoueurs);
        }

        $this->em->flush();
        $this->addFlash('success', 'Disponibilité modifiée avec succès !');

        return $this->redirectToRoute('backoffice.disponibilites');
    }
}
