<?php

namespace App\Controller\BackOffice;

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

    /**
     * BackOfficeController constructor.
     * @param JourneeRepository $journeeRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeRepository $journeeRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->journeeRepository = $journeeRepository;
    }

    /**
     * @Route("/backoffice/journees", name="backoffice.journees")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('backoffice/journee/index.html.twig', [
            'journees' => $this->journeeRepository->getAllJournees()
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
        if (!($journee = $this->journeeRepository->find($idJournee))) throw new Exception('Cette journée est inexistante', 500);
        $form = $this->createForm(JourneeType::class, $journee);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    /** On ne peut pas mélanger les dates */
                    $journees = $journee->getIdChampionnat()->getJournees()->toArray();
                    $posJournee = array_keys(array_filter($journees, function($journeeChamp) use ($journee) {
                        return $journeeChamp->getDateJournee() == $journee->getDateJournee();
                    }));
                    $posJournee = end($posJournee);

                    $nbJourneesBefore = count(array_filter($journees, function($journeeChamp) use ($journee) {
                        return $journeeChamp->getDateJournee() < $journee->getDateJournee();
                    }));

                    if ($posJournee > $nbJourneesBefore) $this->addFlash('fail', 'La date ne peut pas être postèrieure ou égale à celles des journées précédentes');
                    else if ($posJournee < $nbJourneesBefore) $this->addFlash('fail', 'La date ne peut pas être ultèrieure ou égale à celles des journées suivantes');
                    else {
                        $this->em->flush();
                        $this->addFlash('success', 'Journée modifiée avec succès !');
                        return $this->redirectToRoute('backoffice.journees');
                    }
                } catch (Exception $e) {
                    if ($e->getPrevious()->getCode() == "23000") {
                        if (str_contains($e->getPrevious()->getMessage(), 'date_journee')) $this->addFlash('fail', 'La date ' . ($journee->getDateJournee())->format('d/m/Y') . ' est déjà attribuée');
                        else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('backoffice/edit.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier la journée',
            'macro' => 'journee',
            'textForm' => $idJournee
        ]);
    }
}
