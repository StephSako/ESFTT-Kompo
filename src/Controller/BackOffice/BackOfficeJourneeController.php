<?php

namespace App\Controller\BackOffice;

use App\Form\JourneeDepartementaleType;
use App\Form\JourneeParisType;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\JourneeParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeJourneeController extends AbstractController
{
    private $em;
    private $journeeDepartementaleRepository;
    private $journeeParisRepository;

    /**
     * BackOfficeController constructor.
     * @param JourneeParisRepository $journeeParisRepository
     * @param JourneeDepartementaleRepository $journeeDepartementaleRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeParisRepository $journeeParisRepository,
                                JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
    }

    /**
     * @Route("/backoffice/phase", name="back_office.phase")
     * @return Response
     */
    public function indexJournee()
    {
        return $this->render('back_office/journee/index.html.twig', [
            'journeeDepartementales' => $this->journeeDepartementaleRepository->findAll(),
            'journeeParis' => $this->journeeParisRepository->findAll()
        ]);
    }

    /**
     * @Route("/backoffice/phase/edit/{type}/journee/{idJournee}", name="backoffice.phase.edit")
     * @param $idJournee
     * @param $type
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function editJournee($type, $idJournee, Request $request)
    {
        $form = null;
        if ($type == 'departementale'){
            if ($idJournee < 1 || $idJournee > count($this->journeeDepartementaleRepository->findAll())) throw $this->createNotFoundException('Journée inexistante');

            $journee = $this->journeeDepartementaleRepository->find($idJournee);
            $form = $this->createForm(JourneeDepartementaleType::class, $journee);
        }
        else if ($type == 'paris'){
            if ($idJournee < 1 || $idJournee > count($this->journeeParisRepository->findAll())) throw $this->createNotFoundException('Journée inexistante');

            $journee = $this->journeeParisRepository->find($idJournee);
            $form = $this->createForm(JourneeParisType::class, $journee);
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->em->flush();
            $this->addFlash('success', 'Journée de la phase modifiée avec succès !');
            return $this->redirectToRoute('back_office.phase');
        }

        return $this->render('back_office/journee/edit.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'idJournee' => $idJournee
        ]);
    }
}
