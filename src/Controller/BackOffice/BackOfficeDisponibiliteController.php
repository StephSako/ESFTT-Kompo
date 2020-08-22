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
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var DisponibiliteDepartementaleRepository
     */
    private $disponibiliteDepartementaleRepository;
    /**
     * @var DisponibiliteParisRepository
     */
    private $disponibiliteParisRepository;
    /**
     * @var CompetiteurRepository
     */
    private $competiteurRepository;
    /**
     * @var JourneeDepartementaleRepository
     */
    private $journeeDepartementaleRepository;
    /**
     * @var JourneeParisRepository
     */
    private $journeeParisRepository;

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
        if ($type == 'departementale'){
            $dispo = new DisponibiliteDepartementale($this->competiteurRepository->find($idCompetiteur), $this->journeeDepartementaleRepository->find($journee), $dispo);
        }
        else if ($type == 'paris'){
            $dispo = new DisponibiliteParis($this->competiteurRepository->find($idCompetiteur), $this->journeeParisRepository->find($journee), $dispo);
        }

        $this->em->persist($dispo);
        $this->em->flush();
        $this->addFlash('success', 'Disponibilité créée avec succès !');

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
        if ($type == 'departementale'){
            $disposJoueur = $this->disponibiliteDepartementaleRepository->find($disposJoueur);
            $disposJoueur->setDisponibiliteDepartementale($dispo);
        }
        else if ($type == 'paris'){
            $disposJoueur = $this->disponibiliteParisRepository->find($disposJoueur);
            $disposJoueur->setDisponibiliteParis($dispo);
        }

        $this->em->flush();
        $this->addFlash('success', 'Disponibilité modifiée avec succès !');

        return $this->redirectToRoute('back_office.disponibilites');
    }
}
