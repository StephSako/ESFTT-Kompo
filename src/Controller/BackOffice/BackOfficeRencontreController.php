<?php

namespace App\Controller\BackOffice;

use App\Form\BackOfficeRencontreType;
use App\Repository\RencontreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeRencontreController extends AbstractController
{
    private $em;
    private $rencontreRepository;

    /**
     * BackOfficeController constructor.
     * @param RencontreRepository $rencontreRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(RencontreRepository $rencontreRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreRepository = $rencontreRepository;
    }

    /**
     * @Route("/backoffice/rencontres", name="backoffice.rencontres")
     * @return Response
     */
    public function indexRencontre(): Response
    {
        return $this->render('backoffice/rencontre/index.html.twig', [
            'rencontres' => $this->rencontreRepository->getOrderedRencontres()
        ]);
    }

    /**
     * @Route("/backoffice/rencontre/edit/{idRencontre}", name="backoffice.rencontre.edit", requirements={"idRencontre"="\d+"})
     * @param int $idRencontre
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function editRencontre(int $idRencontre, Request $request): Response
    {
        if (!($rencontre = $this->rencontreRepository->find($idRencontre))) throw new Exception('Cette rencontre est inexistante', 500);
        $form = $this->createForm(BackOfficeRencontreType::class, $rencontre);

        $form->handleRequest($request);
        $domicile = ($rencontre->getDomicile() ? "D" : "E");

        if ($form->isSubmitted()){
            if ($form->isValid()){
                try {
                    /** On récupère la valeur du switch du template **/
                    $rencontre->setDomicile(($request->get('lieu_rencontre') == 'on' ? 0 : 1 ));

                    /** Si la rencontre n'est pas ou plus reportée, la date redevient celle de la journée associée **/
                    if (!$rencontre->isReporte()) $rencontre->setDateReport($rencontre->getIdJournee()->getDateJournee());

                    $rencontre->setAdversaire(mb_convert_case($rencontre->getAdversaire(), MB_CASE_TITLE, "UTF-8"));
                    $this->em->flush();
                    $this->addFlash('success', 'Rencontre modifiée avec succès !');
                    return $this->redirectToRoute('backoffice.rencontres');
                } catch(Exception $e){
                    if ($e->getPrevious()->getCode() == "23000") $this->addFlash('fail', 'L\'adversaire \'' . $rencontre->getAdversaire() . '\' est déjà attribué');
                    else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                }
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('backoffice/rencontre/edit.html.twig', [
            'form' => $form->createView(),
            'domicile' => $domicile,
            'rencontre' => $rencontre
        ]);
    }
}
