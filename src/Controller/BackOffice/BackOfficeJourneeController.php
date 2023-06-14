<?php

namespace App\Controller\BackOffice;

use App\Controller\UtilController;
use App\Form\JourneeType;
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
    private $utilController;

    /**
     * BackOfficeController constructor.
     * @param JourneeRepository $journeeRepository
     * @param UtilController $utilController
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeRepository $journeeRepository,
                                UtilController $utilController,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->journeeRepository = $journeeRepository;
        $this->utilController = $utilController;
    }

    /**
     * @Route("/backoffice/journees", name="backoffice.journees")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->render('backoffice/journee/index.html.twig', [
            'journees' => $this->journeeRepository->getAllJournees(),
            'active' => $request->query->get('active')
        ]);
    }

    /**
     * @Route("/backoffice/journee/edit/journee/{idJournee}", name="backoffice.journee.edit", requirements={"idJournee"="\d+"})
     * @param int $idJournee
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function edit(int $idJournee, Request $request): Response
    {
        if (!($journee = $this->journeeRepository->find($idJournee))) {
            $this->addFlash('fail', 'Journée inexistante');
            return $this->redirectToRoute('backoffice.journees');
        }

        $form = $this->createForm(JourneeType::class, $journee);
        $form->handleRequest($request);
        $journees = $journee->getIdChampionnat()->getJournees()->toArray();
        $posJournee = array_keys(array_filter($journees, function($journeeChamp) use ($journee) {
            return $journeeChamp->getDateJournee() == $journee->getDateJournee();
        }))[0]+=1;

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $journee->setLastUpdate($this->utilController->getAdminUpdateLog('Modifiée par '));
                    $this->em->flush();

                    /** On reset les dates de report des Rencontres non décalées à la même date de la Journée */
                    $rencontresToResetDatereport = array_filter($journee->getRencontres()->toArray(), function($r) {
                        return !$r->isReporte();
                    });
                    if ($rencontresToResetDatereport) {
                        foreach ($rencontresToResetDatereport as $rencontre) {
                            $rencontre->setDateReport($journee->getDateJournee());
                        }
                        $this->em->flush();
                    }

                    $this->addFlash('success', 'Journée modifiée');
                    return $this->redirectToRoute('backoffice.journees', [
                        'active' => $journee->getIdChampionnat()->getIdChampionnat()
                    ]);
                } catch (Exception $e) {
                    if ($e->getPrevious()->getCode() == "23000") {
                        if (str_contains($e->getPrevious()->getMessage(), 'date_journee')) $this->addFlash('fail', 'La date ' . ($journee->getDateJournee())->format('d/m/Y') . ' est déjà attribuée');
                        else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('backoffice/journee/edit.html.twig', [
            'form' => $form->createView(),
            'iJournee' => $posJournee
        ]);
    }
}
