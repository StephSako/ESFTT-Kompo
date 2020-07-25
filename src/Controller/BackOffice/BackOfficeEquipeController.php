<?php

namespace App\Controller\BackOffice;

use App\Form\EquipeDepartementaleType;
use App\Form\EquipeParisType;
use App\Repository\EquipeDepartementaleRepository;
use App\Repository\EquipeParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeEquipeController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var EquipeDepartementaleRepository
     */
    private $equipeDepartementaleRepository;
    /**
     * @var EquipeParisRepository
     */
    private $equipeParisRepository;

    /**
     * BackOfficeController constructor.
     * @param EquipeDepartementaleRepository $equipeDepartementaleRepository
     * @param EquipeParisRepository $equipeParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(EquipeDepartementaleRepository $equipeDepartementaleRepository,
                                EquipeParisRepository $equipeParisRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->equipeDepartementaleRepository = $equipeDepartementaleRepository;
        $this->equipeParisRepository = $equipeParisRepository;
    }

    /**
     * @Route("/backoffice/equipes", name="back_office.equipes")
     * @return Response
     */
    public function indexEquipes()
    {
        return $this->render('back_office/equipes/equipes.html.twig', [
            'equipesDepartementales' => $this->equipeDepartementaleRepository->findAll(),
            'equipesParis' => $this->equipeParisRepository->findAll(),
        ]);
    }

    /**
     * @Route("/backoffice/equipe/{type}/{id}", name="backoffice.equipe.edit")
     * @param $id
     * @param $type
     * @param Request $request
     * @return Response
     */
    public function editEquipeAccount($type, $id, Request $request)
    {
        $form = null;
        if ($type == 'departementale'){
            $equipe = $this->equipeDepartementaleRepository->find($id);
            $form = $this->createForm(EquipeDepartementaleType::class, $equipe);
        }
        else if ($type == 'paris'){
            $equipe = $this->equipeParisRepository->find($id);
            $form = $this->createForm(EquipeParisType::class, $equipe);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->em->flush();
            $this->addFlash('success', 'Equipe modifiée avec succès !');
            return $this->redirectToRoute('back_office.equipes');
        }

        return $this->render('back_office/equipes/edit.html.twig', [
            'type' => 'backoffice',
            'equipe' => $equipe,
            'form' => $form->createView()
        ]);
    }
}
