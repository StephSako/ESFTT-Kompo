<?php

namespace App\Controller;

use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use App\Repository\EquipeDepartementaleRepository;
use App\Repository\EquipeParisRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\JourneeParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var JourneeDepartementaleRepository
     */
    private $journeeDepartementaleRepository;
    /**
     * @var JourneeParisRepository
     */
    private $journeeParisRepository;
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
     * @var EquipeDepartementaleRepository
     */
    private $equipeDepartementaleRepository;
    /**
     * @var EquipeParisRepository
     */
    private $equipeParisRepository;

    /**
     * BackOfficeController constructor.
     * @param JourneeDepartementaleRepository $journeeDepartementaleRepository
     * @param JourneeParisRepository $journeeParisRepository
     * @param DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param EquipeDepartementaleRepository $equipeDepartementaleRepository
     * @param EquipeParisRepository $equipeParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                JourneeParisRepository $journeeParisRepository,
                                DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                CompetiteurRepository $competiteurRepository,
                                EquipeDepartementaleRepository $equipeDepartementaleRepository,
                                EquipeParisRepository $equipeParisRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->disponibiliteDepartementaleRepository = $disponibiliteDepartementaleRepository;
        $this->disponibiliteParisRepository = $disponibiliteParisRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->equipeDepartementaleRepository = $equipeDepartementaleRepository;
        $this->equipeParisRepository = $equipeParisRepository;
    }

    /**
     * @Route("/backoffice", name="back_office")
     * @return Response
     */
    public function index()
    {
        $disponibiliteDepartementales = $this->disponibiliteDepartementaleRepository->findAllDispos();
        $disponibiliteParis = $this->disponibiliteParisRepository->findAllDispos();
        $competiteurs = $this->competiteurRepository->findBy([], ['nom' => 'ASC']);
        $equipeDepartementales = $this->equipeDepartementaleRepository->findAll();
        $equipesParis = $this->equipeParisRepository->findAll();
        $journeesDepartementales = $this->journeeDepartementaleRepository->findAll();
        $journeesParis = $this->journeeParisRepository->findAll();

        return $this->render('back_office/index.html.twig', [
            'controller_name' => 'BackOfficeController',
            'disponibiliteDepartementales' => $disponibiliteDepartementales,
            'disponibiliteParis' => $disponibiliteParis,
            'competiteurs' => $competiteurs,
            'equipesDepartementales' => $equipeDepartementales,
            'equipesParis' => $equipesParis,
            'journeesDepartementales' => $journeesDepartementales,
            'journeesParis' => $journeesParis
        ]);
    }

    /**
     * @Route("/backoffice/competiteur/{id}", name="competiteur.edit")
     * @return Response
     */
    public function editCompetiteur()
    {

    }
}
