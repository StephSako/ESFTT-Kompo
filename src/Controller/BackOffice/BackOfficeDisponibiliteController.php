<?php

namespace App\Controller\BackOffice;

use App\Controller\InvalidSelectionController;
use App\Entity\DisponibiliteDepartementale;
use App\Entity\DisponibiliteParis;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\JourneeParisRepository;
use App\Repository\RencontreDepartementaleRepository;
use App\Repository\RencontreParisRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeDisponibiliteController extends AbstractController
{
    private $em;
    private $disponibiliteDepartementaleRepository;
    private $disponibiliteParisRepository;
    private $competiteurRepository;
    private $journeeDepartementaleRepository;
    private $journeeParisRepository;
    private $rencontreDepartementaleRepository;
    private $rencontreParisRepository;

    /**
     * BackOfficeController constructor.
     * @param DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param JourneeDepartementaleRepository $journeeDepartementaleRepository
     * @param JourneeParisRepository $journeeParisRepository
     * @param EntityManagerInterface $em
     * @param RencontreDepartementaleRepository $rencontreDepartementaleRepository
     * @param RencontreParisRepository $rencontreParisRepository
     */
    public function __construct(DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                CompetiteurRepository $competiteurRepository,
                                JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                JourneeParisRepository $journeeParisRepository,
                                EntityManagerInterface $em,
                                RencontreDepartementaleRepository $rencontreDepartementaleRepository,
                                RencontreParisRepository $rencontreParisRepository)
    {
        $this->em = $em;
        $this->disponibiliteDepartementaleRepository = $disponibiliteDepartementaleRepository;
        $this->disponibiliteParisRepository = $disponibiliteParisRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->rencontreDepartementaleRepository = $rencontreDepartementaleRepository;
        $this->rencontreParisRepository = $rencontreParisRepository;
    }

    /**
     * @Route("/backoffice/disponibilites", name="back_office.disponibilites")
     * @return Response
     * @throws DBALException
     */
    public function indexDisponibilites()
    {
        return $this->render('back_office/disponibilites/index.html.twig', [
            'disponibiliteDepartementales' => $this->competiteurRepository->findAllDispos("departementale"),
            'disponibiliteParis' => $this->competiteurRepository->findAllDispos("paris")
        ]);
    }

    /**
     * @Route("/backoffice/disponibilites/new/{idCompetiteur}/{journee}/{type}/{dispo}", name="backoffice.disponibilite.new")
     * @param $journee
     * @param string $type
     * @param int $dispo
     * @param $idCompetiteur
     * @return Response
     */
    public function new($journee, $type, $dispo, $idCompetiteur):Response
    {
        $competiteur = $this->competiteurRepository->find($idCompetiteur);

        if ($type) {
            if ($type == 'departementale') {
                if (sizeof($this->disponibiliteDepartementaleRepository->findBy(['idCompetiteur' => $competiteur, 'idJournee' => $journee])) == 0) {
                    $disponibilite = new DisponibiliteDepartementale($competiteur, $this->journeeDepartementaleRepository->find($journee), $dispo);

                    $this->em->persist($disponibilite);
                    $this->em->flush();
                    $this->addFlash('success', 'Disponibilité signalée avec succès !');
                } else $this->addFlash('warning', 'Disponibilité déjà renseignée pour cette journée !');
            } else if ($type == 'paris') {
                if (sizeof($this->disponibiliteParisRepository->findBy(['idCompetiteur' => $competiteur, 'idJournee' => $journee])) == 0) {
                    $disponibilite = new DisponibiliteParis($competiteur, $this->journeeParisRepository->find($journee), $dispo);

                    $this->em->persist($disponibilite);
                    $this->em->flush();
                    $this->addFlash('success', 'Disponibilité signalée avec succès !');
                } else $this->addFlash('warning', 'Disponibilité déjà renseignée pour cette journée !');
            } else $this->addFlash('fail', 'Cette compétition n\'existe pas !');
        } else $this->addFlash('fail', 'Compétition non renseignée !');

        return $this->redirectToRoute('back_office.disponibilites');
    }

    /**
     * @Route("/backoffice/disponibilites/update/{type}/{disposJoueur}/{dispo}", name="backoffice.disponibilite.update")
     * @param string $type
     * @param $disposJoueur
     * @param bool $dispo
     * @param InvalidSelectionController $invalidSelectionController
     * @return Response
     */
    public function update(string $type, $disposJoueur, bool $dispo, InvalidSelectionController $invalidSelectionController) : Response
    {
        if ($type || $type != 'departementale' || $type != 'paris') {
            if ($type == 'departementale') {
                $disposJoueur = $this->disponibiliteDepartementaleRepository->find($disposJoueur);
                $disposJoueur->setDisponibiliteDepartementale($dispo);

                /** On supprime le joueur des compositions d'équipe de la journée actuelle s'il est indisponible */
                if (!$dispo) $invalidSelectionController->deleteInvalidSelectedPlayer($this->rencontreDepartementaleRepository->getSelectedWhenIndispo($this->getUser(), $disposJoueur->getIdJournee()), 'departementale');

                $this->em->flush();
                $this->addFlash('success', 'Disponibilité modifiée avec succès !');
            }
            else if ($type == 'paris') {
                $disposJoueur = $this->disponibiliteParisRepository->find($disposJoueur);
                $disposJoueur->setDisponibiliteParis($dispo);

                if (!$dispo) $invalidSelectionController->deleteInvalidSelectedPlayer($this->rencontreParisRepository->getSelectedWhenIndispo($this->getUser(), $disposJoueur->getIdJournee()), 'paris');

                $this->em->flush();
                $this->addFlash('success', 'Disponibilité modifiée avec succès !');
            } else $this->addFlash('fail', 'Cette compétition n\'existe pas !');
        } else $this->addFlash('fail', 'Compétition non renseignée !');

        return $this->redirectToRoute('back_office.disponibilites');
    }
}
