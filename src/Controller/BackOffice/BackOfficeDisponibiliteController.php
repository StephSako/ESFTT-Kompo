<?php

namespace App\Controller\BackOffice;

use App\Entity\DisponibiliteDepartementale;
use App\Entity\DisponibiliteParis;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\JourneeParisRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeDisponibiliteController extends AbstractController
{
    private EntityManagerInterface $em;
    private DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository;
    private DisponibiliteParisRepository $disponibiliteParisRepository;
    private CompetiteurRepository $competiteurRepository;
    private JourneeDepartementaleRepository $journeeDepartementaleRepository;
    private JourneeParisRepository $journeeParisRepository;

    /**
     * BackOfficeController constructor.
     * @param DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param JourneeDepartementaleRepository $journeeDepartementaleRepository
     * @param JourneeParisRepository $journeeParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                CompetiteurRepository $competiteurRepository,
                                JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                JourneeParisRepository $journeeParisRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->disponibiliteDepartementaleRepository = $disponibiliteDepartementaleRepository;
        $this->disponibiliteParisRepository = $disponibiliteParisRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->journeeParisRepository = $journeeParisRepository;
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
     * @return Response
     */
    public function update($type, $disposJoueur, bool $dispo) : Response
    {
        if ($type || $type != 'departementale' || $type != 'paris') {
            if ($type == 'departementale') {
                $disposJoueur = $this->disponibiliteDepartementaleRepository->find($disposJoueur);
                $disposJoueur->setDisponibiliteDepartementale($dispo);
                $this->em->flush();
                $this->addFlash('success', 'Disponibilité modifiée avec succès !');
            } else if ($type == 'paris') {
                $disposJoueur = $this->disponibiliteParisRepository->find($disposJoueur);
                $disposJoueur->setDisponibiliteParis($dispo);
                $this->em->flush();
                $this->addFlash('success', 'Disponibilité modifiée avec succès !');
            } else $this->addFlash('fail', 'Cette compétition n\'existe pas !');
        } else $this->addFlash('fail', 'Compétition non renseignée !');

        return $this->redirectToRoute('back_office.disponibilites');
    }
}
