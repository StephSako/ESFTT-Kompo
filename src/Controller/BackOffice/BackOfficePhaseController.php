<?php

namespace App\Controller\BackOffice;

use App\Form\BackOfficePhaseDepartementaleType;
use App\Form\BackOfficePhaseParisType;
use App\Repository\PhaseDepartementaleRepository;
use App\Repository\PhaseParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficePhaseController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var PhaseDepartementaleRepository
     */
    private $phaseDepartementaleRepository;
    /**
     * @var PhaseParisRepository
     */
    private $phaseParisRepository;

    /**
     * BackOfficeController constructor.
     * @param PhaseDepartementaleRepository $phaseDepartementaleRepository
     * @param PhaseParisRepository $phaseParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(PhaseParisRepository $phaseParisRepository,
                                PhaseDepartementaleRepository $phaseDepartementaleRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->phaseParisRepository = $phaseParisRepository;
        $this->phaseDepartementaleRepository = $phaseDepartementaleRepository;
    }

    /**
     * @Route("/backoffice/rencontres", name="back_office.rencontres")
     * @return Response
     */
    public function indexPhase()
    {
        return $this->render('back_office/phase/index.html.twig', [
            'rencontreDepartementales' => $this->phaseDepartementaleRepository->findAll(),
            'rencontreParis' => $this->phaseParisRepository->findAll()
        ]);
    }

    /**
     * @Route("/backoffice/rencontre/edit/{type}/{idRencontre}", name="backoffice.rencontre.edit")
     * @param $idRencontre
     * @param $type
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function editRencontre($type, $idRencontre, Request $request)
    {
        $rencontre = $form = null;
        if ($type == 'departementale'){
            $rencontre = $this->phaseDepartementaleRepository->find($idRencontre);
            $form = $this->createForm(BackOfficePhaseDepartementaleType::class, $rencontre);
        }
        else if ($type == 'paris'){
            $rencontre = $this->phaseParisRepository->find($idRencontre);
            $form = $this->createForm(BackOfficePhaseParisType::class, $rencontre);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->em->flush();
            $this->addFlash('success', 'Recontre modifiée avec succès !');
            return $this->redirectToRoute('back_office.rencontres');
        }

        return $this->render('back_office/phase/edit.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'date' => $rencontre->getIdJournee()->getDate(),
            'idJournee' => $rencontre->getIdJournee()->getIdJournee(),
            'idEquipe' => $rencontre->getIdEquipe()->getIdEquipe()
        ]);
    }
}
