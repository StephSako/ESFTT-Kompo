<?php

namespace App\Controller\BackOffice;

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
    private EntityManagerInterface $em;
    private RencontreDepartementaleRepository $rencontreDepartementaleRepository;
    private RencontreParisRepository $rencontreParisRepository;

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
            'rencontreDepartementales' => $this->rencontreDepartementaleRepository->findBy([], ['idEquipe' => 'ASC']),
            'rencontreParis' => $this->rencontreParisRepository->findBy([], ['idEquipe' => 'ASC'])
        ]);
    }

    /**
     * @Route("/backoffice/rencontre/edit/{type}/{idRencontre}", name="backoffice.rencontre.edit")
     * @param $idRencontre
     * @param $type
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function editRencontre($type, $idRencontre, Request $request)
    {
        $rencontre = $form = null;
        if ($type == 'departementale'){
            $rencontre = $this->rencontreDepartementaleRepository->find($idRencontre);
            $form = $this->createForm(BackOfficeRencontreDepartementaleType::class, $rencontre);
        }
        else if ($type == 'paris'){
            $rencontre = $this->rencontreParisRepository->find($idRencontre);
            $form = $this->createForm(BackOfficeRencontreParisType::class, $rencontre);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->em->flush();
            $this->addFlash('success', 'Recontre modifiée avec succès !');
            return $this->redirectToRoute('back_office.rencontres');
        }

        return $this->render('back_office/rencontre/edit.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'date' => $rencontre->getIdJournee()->getDate(),
            'idJournee' => $rencontre->getIdJournee()->getIdJournee(),
            'idEquipe' => $rencontre->getIdEquipe()->getIdEquipe()
        ]);
    }
}
