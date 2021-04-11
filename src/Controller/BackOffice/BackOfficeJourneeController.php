<?php

namespace App\Controller\BackOffice;

use App\Form\JourneeType;
use App\Repository\ChampionnatRepository;
use App\Repository\JourneeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeJourneeController extends AbstractController
{
    private $em;
    private $journeeRepository;
    private $championnatRepository;

    /**
     * BackOfficeController constructor.
     * @param JourneeRepository $journeeRepository
     * @param ChampionnatRepository $championnatRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeRepository $journeeRepository,
                                ChampionnatRepository $championnatRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->journeeRepository = $journeeRepository;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/backoffice/journees", name="backoffice.journees")
     * @return Response
     */
    public function indexJournee(): Response
    {
        return $this->render('backoffice/journee/index.html.twig', [
            // TODO Classer selon les championnats
            'journees' => $this->journeeRepository->findAll()
        ]);
    }

    /**
     * @Route("/backoffice/journee/edit/{type}/journee/{idJournee}", name="backoffice.journee.edit")
     * @param $idJournee
     * @param int $type
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function editJournee(int $type, $idJournee, Request $request): Response
    {
        if ((!$championnat = $this->championnatRepository->find($type))) throw new Exception('Ce championnat est inexistant', 500);
        if (!($journee = $this->journeeRepository->find($idJournee))) throw new Exception('Cette journée est inexistanté', 500);
        $form = $this->createForm(JourneeType::class, $journee);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->flush();
                $this->addFlash('success', 'Journée modifiée avec succès !');
                return $this->redirectToRoute('backoffice.journees');
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('backoffice/journee/edit.html.twig', [
            'form' => $form->createView(),
            'type' => $championnat->getNom(),
            'idJournee' => $idJournee
        ]);
    }
}
