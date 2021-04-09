<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use App\Entity\DisponibiliteParis;
use App\Repository\DisponibiliteRepository;
use App\Repository\DisponibiliteParisRepository;
use App\Repository\DivisionRepository;
use App\Repository\JourneeRepository;
use App\Repository\JourneeParisRepository;
use App\Repository\RencontreDepartementaleRepository;
use App\Repository\RencontreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DisponibiliteController extends AbstractController
{
    private $em;
    private $journeeParisRepository;
    private $journeeDepartementaleRepository;
    private $disponibiliteDepartementaleRepository;
    private $disponibiliteParisRepository;
    private $rencontreDepartementaleRepository;
    private $rencontreParisRepository;
    private $divisionRepository;

    /**
     * @param EntityManagerInterface $em
     * @param JourneeParisRepository $journeeParisRepository
     * @param JourneeRepository $journeeDepartementaleRepository
     * @param DisponibiliteRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     * @param RencontreDepartementaleRepository $rencontreDepartementaleRepository
     * @param DivisionRepository $divisionRepository
     * @param RencontreRepository $rencontreParisRepository
     */
    public function __construct(EntityManagerInterface $em,
                                JourneeParisRepository $journeeParisRepository,
                                JourneeRepository $journeeDepartementaleRepository,
                                DisponibiliteRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                RencontreDepartementaleRepository $rencontreDepartementaleRepository,
                                DivisionRepository $divisionRepository,
                                RencontreRepository $rencontreParisRepository)
    {
        $this->em = $em;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->disponibiliteDepartementaleRepository = $disponibiliteDepartementaleRepository;
        $this->disponibiliteParisRepository = $disponibiliteParisRepository;
        $this->rencontreDepartementaleRepository = $rencontreDepartementaleRepository;
        $this->rencontreParisRepository = $rencontreParisRepository;
        $this->divisionRepository = $divisionRepository;
    }

    /**
     * @Route("/journee/disponibilite/new/{journee}/{type}/{dispo}", name="journee.disponibilite.new")
     * @param int $journee
     * @param string $type
     * @param int $dispo
     * @return Response
     * @throws Exception
     */
    public function new(int $journee, string $type, int $dispo):Response
    {
        $competiteur = $this->getUser();

        if ($type) {
            if ($type == 'departementale') {
                if (!($journee = $this->journeeDepartementaleRepository->find($journee))) throw new Exception('Cette journée est inexistante', 500);
                if (sizeof($this->disponibiliteDepartementaleRepository->findBy(['idCompetiteur' => $competiteur, 'idJournee' => $journee])) == 0) {
                    $disponibilite = new Disponibilite($competiteur, $journee, $dispo);

                    $this->em->persist($disponibilite);
                    $this->em->flush();
                    $this->addFlash('success', 'Disponibilité signalée avec succès !');
                } else $this->addFlash('warning', 'Disponibilité déjà renseignée pour cette journée !');
            } else if ($type == 'paris') {
                if (!($journee = $this->journeeParisRepository->find($journee))) throw new Exception('Cette journée est inexistante', 500);
                if (sizeof($this->disponibiliteParisRepository->findBy(['idCompetiteur' => $competiteur, 'idJournee' => $journee])) == 0) {
                    $disponibilite = new DisponibiliteParis($competiteur, $journee, $dispo);

                    $this->em->persist($disponibilite);
                    $this->em->flush();
                    $this->addFlash('success', 'Disponibilité signalée avec succès !');
                } else $this->addFlash('warning', 'Disponibilité déjà renseignée pour cette journée !');
            } else $this->addFlash('fail', 'Cette compétition n\'existe pas !');
        } else $this->addFlash('fail', 'Compétition non renseignée !');

        return $this->redirectToRoute('journee.show',
            [
                'type' => $type,
                'id' => $journee->getIdJournee()
            ]
        );
    }

    /**
     * @Route("/journee/disponibilite/update/{type}/{dispoJoueur}/{dispo}", name="journee.disponibilite.update")
     * @param string $type
     * @param int $dispoJoueur
     * @param bool $dispo
     * @param InvalidSelectionController $invalidSelectionController
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function update(string $type, int $dispoJoueur, bool $dispo, InvalidSelectionController $invalidSelectionController) : Response
    {
        $journee = 1;
        if ($type == 'departementale'){
            if (!($disposJoueur = $this->disponibiliteDepartementaleRepository->find($dispoJoueur))) throw new Exception('Cette disponibilité est inexistante', 500);
            $disposJoueur->setDisponibiliteDepartementale($dispo);
            $journee = $disposJoueur->getIdJournee()->getIdJournee();

            /** On supprime le joueur des compositions d'équipe de la journée actuelle s'il est indisponible */
            if (!$dispo){
                $nbMaxJoueurs = $this->divisionRepository->getMaxNbJoueursChamp($type);
                $invalidSelectionController->deleteInvalidSelectedPlayers($this->rencontreDepartementaleRepository->getSelectedWhenIndispo($this->getUser()->getIdCompetiteur(), $journee, $nbMaxJoueurs), $nbMaxJoueurs);
            }

            $this->em->flush();
            $this->addFlash('success', 'Disponibilité modifiée avec succès !');
        }
        else if ($type == 'paris'){
            if (!($disposJoueur = $this->disponibiliteParisRepository->find($dispoJoueur))) throw new Exception('Cette disponiblité est inexistante', 500);
            $disposJoueur->setDisponibiliteParis($dispo);
            $journee = $disposJoueur->getIdJournee()->getIdJournee();

            if (!$dispo){
                $nbMaxJoueurs = $this->divisionRepository->getMaxNbJoueursChamp($type);
                $invalidSelectionController->deleteInvalidSelectedPlayers($this->rencontreParisRepository->getSelectedWhenIndispo($this->getUser()->getIdCompetiteur(), $journee, $nbMaxJoueurs), $nbMaxJoueurs);
            }

            $this->em->flush();
            $this->addFlash('success', 'Disponibilité modifiée avec succès !');
        } else $this->addFlash('fail', 'Cette compétition n\'existe pas !');

        return $this->redirectToRoute('journee.show',
            [
                'type' => $type,
                'id' => $journee
            ]
        );
    }
}
