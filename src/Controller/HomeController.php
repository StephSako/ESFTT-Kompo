<?php

namespace App\Controller;

use App\Entity\FirstPhase;
use App\Form\FirstPhaseType;
use App\Repository\CompetiteurRepository;
use App\Repository\FirstPhaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Phase_1Controller
 * @package App\Controller
 */
class HomeController extends AbstractController
{

    /**
     * @var FirstPhaseRepository
     */
    private $phase_1_Repository;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var CompetiteurRepository
     */
    private $competiteurRepository;

    /**
     * @param CompetiteurRepository $competiteurRepository
     * @param FirstPhaseRepository $phase_1_Repository
     * @param EntityManagerInterface $em
     */
    public function __construct(CompetiteurRepository $competiteurRepository, FirstPhaseRepository $phase_1_Repository, EntityManagerInterface $em)
    {
        $this->phase_1_Repository = $phase_1_Repository;
        $this->em = $em;
        $this->competiteurRepository = $competiteurRepository;
    }

    /**
     * @Route("/", name="index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('journee/show.html.twig', [
            'journee' => 'Index'
        ]);
    }

    /**
     * @param FirstPhase $id
     * @return Response
     * @Route("/journee/{id}", name="journee.show")
     */
    public function journeeShow($id)
    {
        $journee = $this->phase_1_Repository->findJournee($id);
        $competiteurs = $this->competiteurRepository->findBy([], ['nom' => 'ASC']);
        dump($journee);
        return $this->render('journee/show.html.twig', [
            'journee' => $journee,
            'competiteurs' => $competiteurs
        ]);
    }

    /**
     * @Route("/journee/edit/{id}", name="journee.edit")
     * @param FirstPhase $compo
     * @param Request $request
     * @return Response
     */
    public function edit(FirstPhase $compo, Request $request) : Response
    {
        $form = $this->createForm(FirstPhaseType::class, $compo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->em->flush();
            $this->addFlash('success', 'Composition modifiée avec succès !');
            return $this->redirectToRoute('journee.show', [
                'id' => $compo->getIdJournee()->getNJournee()
            ]); // TODO Issue id de la journee
        }

        return $this->render('journee/compo.create.html.twig', [
            'compo' => $compo,
            'form' => $form->createView()
        ]);
    }
}
