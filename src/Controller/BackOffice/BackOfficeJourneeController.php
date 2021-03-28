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
     * @Route("/backoffice/journees", name="backoffice.journees")
     * @return Response
     */
    public function indexJournee(): Response
    {
        return $this->render('backoffice/journee/index.html.twig', [
            'journeeDepartementales' => $this->journeeDepartementaleRepository->findAll(),
            'journeeParis' => $this->journeeParisRepository->findAll()
        ]);
    }

    /**
     * @Route("/backoffice/journee/edit/{type}/journee/{idJournee}", name="backoffice.journee.edit")
     * @param $idJournee
     * @param $type
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function editJournee($type, $idJournee, Request $request): Response
    {
        $form = null;
        if ($type == 'departementale'){
            if (!($journee = $this->journeeDepartementaleRepository->find($idJournee))) throw new Exception('Cette journée est inexistanté', 500);
            $form = $this->createForm(JourneeDepartementaleType::class, $journee);
        }
        else if ($type == 'paris'){
            if (!($journee = $this->journeeParisRepository->find($idJournee))) throw new Exception('Cette journée est inexistante', 500);
            $form = $this->createForm(JourneeParisType::class, $journee);
        }
        else throw new Exception('Ce championnat est inexistant', 500);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->em->flush();
            $this->addFlash('success', 'Journée modifiée avec succès !');
            return $this->redirectToRoute('backoffice.journees');
        }

        return $this->render('backoffice/journee/edit.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'idJournee' => $idJournee
        ]);
    }
}
