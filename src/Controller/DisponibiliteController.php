<?php

namespace App\Controller;

use App\Entity\DisponibiliteDepartementale;
use App\Entity\DisponibiliteParis;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\JourneeParisRepository;
use App\Repository\RencontreDepartementaleRepository;
use App\Repository\RencontreParisRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * @param EntityManagerInterface $em
     * @param JourneeParisRepository $journeeParisRepository
     * @param JourneeDepartementaleRepository $journeeDepartementaleRepository
     * @param DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     * @param RencontreDepartementaleRepository $rencontreDepartementaleRepository
     * @param RencontreParisRepository $rencontreParisRepository
     */
    public function __construct(EntityManagerInterface $em,
                                JourneeParisRepository $journeeParisRepository,
                                JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                RencontreDepartementaleRepository $rencontreDepartementaleRepository,
                                RencontreParisRepository $rencontreParisRepository)
    {
        $this->em = $em;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->disponibiliteDepartementaleRepository = $disponibiliteDepartementaleRepository;
        $this->disponibiliteParisRepository = $disponibiliteParisRepository;
        $this->rencontreDepartementaleRepository = $rencontreDepartementaleRepository;
        $this->rencontreParisRepository = $rencontreParisRepository;
    }

    /**
     * @Route("/journee/disponibilite/new/{journee}/{type}/{dispo}", name="journee.disponibilite.new")
     * @param $journee
     * @param string $type
     * @param int $dispo
     * @return Response
     */
    public function new($journee, string $type, int $dispo):Response
    {
        $competiteur = $this->getUser();

        if ($type) {
            if ($type == 'departementale') {
                $journee = $this->journeeDepartementaleRepository->find($journee);
                if (sizeof($this->disponibiliteDepartementaleRepository->findBy(['idCompetiteur' => $competiteur, 'idJournee' => $journee])) == 0) {
                    $disponibilite = new DisponibiliteDepartementale($competiteur, $this->journeeDepartementaleRepository->find($journee), $dispo);

                    $this->em->persist($disponibilite);
                    $this->em->flush();
                    $this->addFlash('success', 'Disponibilité signalée avec succès !');
                } else $this->addFlash('warning', 'Disponibilité déjà renseignée pour cette journée !');
            } else if ($type == 'paris') {
                $journee = $this->journeeParisRepository->find($journee);
                if (sizeof($this->disponibiliteParisRepository->findBy(['idCompetiteur' => $competiteur, 'idJournee' => $journee])) == 0) {
                    $disponibilite = new DisponibiliteParis($competiteur, $this->journeeParisRepository->find($journee), $dispo);

                    $this->em->persist($disponibilite);
                    $this->em->flush();
                    $this->addFlash('success', 'Disponibilité signalée avec succès !');
                } else $this->addFlash('warning', 'Disponibilité déjà renseignée pour cette journée !');
            } else $this->addFlash('fail', 'Cette compétition n\'existe pas !');
        } else $this->addFlash('fail', 'Compétition non renseignée !');

        return $this->redirectToRoute('journee.show',
            array(
                'type' => $type,
                'id' => $journee->getIdJournee()
            )
        );
    }

    /**
     * @Route("/journee/disponibilite/update/{type}/{disposJoueur}/{dispo}", name="journee.disponibilite.update")
     * @param string $type
     * @param $disposJoueur
     * @param bool $dispo
     * @param InvalidSelectionController $invalidSelectionController
     * @return Response
     */
    public function update(string $type, $disposJoueur, bool $dispo, InvalidSelectionController $invalidSelectionController) : Response
    {
        $journee =1;
        if ($type) {
            if ($type == 'departementale'){
                $disposJoueur = $this->disponibiliteDepartementaleRepository->find($disposJoueur);
                $disposJoueur->setDisponibiliteDepartementale($dispo);
                $journee = $disposJoueur->getIdJournee()->getIdJournee();

                /** On supprime le joueur des compositions d'équipe de la journée actuelle s'il est indisponible */
                if (!$dispo) $invalidSelectionController->deleteInvalidSelectedPlayer($this->rencontreDepartementaleRepository->getSelectedWhenIndispo($this->getUser(), $journee), 'departementale');

                $this->em->flush();
                $this->addFlash('success', 'Disponibilité modifiée avec succès !');
            }
            else if ($type == 'paris'){
                $disposJoueur = $this->disponibiliteParisRepository->find($disposJoueur);
                $disposJoueur->setDisponibiliteParis($dispo);
                $journee = $disposJoueur->getIdJournee()->getIdJournee();

                if (!$dispo) $invalidSelectionController->deleteInvalidSelectedPlayer($this->rencontreParisRepository->getSelectedWhenIndispo($this->getUser(), $journee), 'paris');

                $this->em->flush();
                $this->addFlash('success', 'Disponibilité modifiée avec succès !');
            } else $this->addFlash('fail', 'Cette compétition n\'existe pas !');
        } else $this->addFlash('fail', 'Compétition non renseignée !');


        return $this->redirectToRoute('journee.show',
            array(
                'type' => $type,
                'id' => $journee
            )
        );
    }
}
