<?php

namespace App\Controller\BackOffice;

use App\Controller\HomeController;
use App\Controller\InvalidSelectionController;
use App\Form\BackOfficeRencontreDepartementaleType;
use App\Form\BackOfficeRencontreParisType;
use App\Repository\RencontreDepartementaleRepository;
use App\Repository\RencontreParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeRencontreController extends AbstractController
{
    private $em;
    private $rencontreDepartementaleRepository;
    private $rencontreParisRepository;

    /**
     * BackOfficeController constructor.
     * @param RencontreDepartementaleRepository $rencontreDepartementaleRepository
     * @param RencontreParisRepository $rencontreParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(RencontreParisRepository $rencontreParisRepository,
                                RencontreDepartementaleRepository $rencontreDepartementaleRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreParisRepository = $rencontreParisRepository;
        $this->rencontreDepartementaleRepository = $rencontreDepartementaleRepository;
    }

    /**
     * @Route("/backoffice/rencontres", name="back_office.rencontres")
     * @return Response
     */
    public function indexRencontre()
    {
        return $this->render('back_office/rencontre/index.html.twig', [
            'rencontreDepartementales' => $this->rencontreDepartementaleRepository->getOrderedRencontres(),
            'rencontreParis' => $this->rencontreParisRepository->getOrderedRencontres()
        ]);
    }

    /**
     * @Route("/backoffice/rencontre/edit/{type}/{idRencontre}", name="backoffice.rencontre.edit")
     * @param $type
     * @param $idRencontre
     * @param Request $request
     * @param HomeController $homeController
     * @return Response
     */
    public function editRencontre($type, $idRencontre, Request $request, HomeController $homeController)
    {
        $domicile = null;
        if ($type == 'departementale'){
            if (!($rencontre = $this->rencontreDepartementaleRepository->find($idRencontre))) throw $this->createNotFoundException('Rencontre inexistante');
            $form = $this->createForm(BackOfficeRencontreDepartementaleType::class, $rencontre);
            $domicile = ($rencontre->getDomicile() ? "D" : "E");
        }
        else if ($type == 'paris'){
            if (!($rencontre = $this->rencontreParisRepository->find($idRencontre))) throw $this->createNotFoundException('Rencontre inexistante');
            $form = $this->createForm(BackOfficeRencontreParisType::class, $rencontre);
            $domicile = ($rencontre->getDomicile() ? "D" : "E");
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            // On récupère la valeur du switch du template
            $rencontre->setDomicile(($request->get('lieu_rencontre') == 'on' ? 0 : 1 ));

            // Si la rencontre n'est pas ou plus reportée, la date redevient celle de la journée associée
            if (!$rencontre->isReporte()) $rencontre->setDateReport($rencontre->getIdJournee()->getDate());

            // Si la rencontre est exemptée ou annulée, la composition est vidée
            if ($rencontre->isExempt()) $homeController->emptyComposition($type, $rencontre->getId(), false);

            $this->em->flush();
            $this->addFlash('success', 'Rencontre modifiée avec succès !');
            return $this->redirectToRoute('back_office.rencontres');
        }

        return $this->render('back_office/rencontre/edit.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'domicile' => $domicile,
            'date' => $rencontre->getIdJournee()->getDate(),
            'idJournee' => $rencontre->getIdJournee()->getIdJournee(),
            'idEquipe' => $rencontre->getIdEquipe()->getIdEquipe()
        ]);
    }
}
