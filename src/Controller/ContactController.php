<?php

namespace App\Controller;

use App\Repository\CompetiteurRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\JourneeParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    private $journeeParisRepository;
    private $journeeDepartementaleRepository;
    private $competiteurRepository;

    /**
     * ContactController constructor.
     * @param JourneeDepartementaleRepository $journeeParisRepository
     * @param JourneeParisRepository $journeeDepartementaleRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeDepartementaleRepository $journeeParisRepository,
                                JourneeParisRepository $journeeDepartementaleRepository,
                                CompetiteurRepository $competiteurRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->competiteurRepository = $competiteurRepository;
    }

    /**
     * @Route("/contact", name="contact")
     * @throws Exception
     */
    public function index(): Response
    {
        $type = ($this->get('session')->get('type') != null ? $this->get('session')->get('type') : 'departementale');
        if ($type == 'departementale') $journees = $this->journeeDepartementaleRepository->findAll();
        else if ($type == 'paris') $journees = $this->journeeParisRepository->findAll();
        else throw new Exception('Ce championnat est inexistant', 500);

        $competiteurs = $this->competiteurRepository->findBy([], ['nom' => 'ASC', 'prenom' => 'ASC']);

        return $this->render('contact/index.html.twig', [
            'competiteurs' => $competiteurs,
            'journees' => $journees
        ]);
    }
}
