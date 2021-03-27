<?php

namespace App\Controller\BackOffice;

use App\Entity\Division;
use App\Form\DivisionFormType;
use App\Repository\DivisionRepository;
use App\Repository\EquipeDepartementaleRepository;
use App\Repository\EquipeParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeDivisionController extends AbstractController
{
    private $em;
    private $divisionRepository;
    private $equipeDepartementaleRepository;
    private $equipeParisRepository;

    /**
     * BackOfficeController constructor.
     * @param DivisionRepository $divisionRepository
     * @param EntityManagerInterface $em
     * @param EquipeDepartementaleRepository $equipeDepartementaleRepository
     * @param EquipeParisRepository $equipeParisRepository
     */
    public function __construct(DivisionRepository $divisionRepository,
                                EntityManagerInterface $em,
                                EquipeDepartementaleRepository $equipeDepartementaleRepository,
                                EquipeParisRepository $equipeParisRepository)
    {
        $this->em = $em;
        $this->divisionRepository = $divisionRepository;
        $this->equipeDepartementaleRepository = $equipeDepartementaleRepository;
        $this->equipeParisRepository = $equipeParisRepository;
    }

    /**
     * @Route("/backoffice/divisions", name="backoffice.divisions")
     * @return Response
     */
    public function indexDivisions(): Response
    {
        return $this->render('backoffice/division/index.html.twig', [
            'divisions' => $this->divisionRepository->findBy([], ['nbJoueursChampParis' => 'DESC', 'shortName' => 'ASC'])
        ]);
    }

    /**
     * @Route("/backoffice/division/new", name="backoffice.division.new")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $division = new Division();
        $form = $this->createForm(DivisionFormType::class, $division);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()){
                $division->setLongName(ucwords(strtolower($division->getLongName())));
                $division->setShortName(strtoupper($division->getShortName()));
                $this->em->persist($division);
                $this->em->flush();
                $this->addFlash('success', 'Division créée avec succès !');
                return $this->redirectToRoute('backoffice.divisions');
            } else {
                $this->addFlash('fail', 'Une erreur est survenue ...');
            }
        }

        return $this->render('backoffice/division/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/division/edit/{idDivision}", name="backoffice.division.edit")
     * @param int $idDivision
     * @param Request $request
     * @return Response
     */
    public function edit(int $idDivision, Request $request): Response
    {
        if (!($division = $this->divisionRepository->find($idDivision))) throw $this->createNotFoundException('Division inexistante');
        $form = $this->createForm(DivisionFormType::class, $division);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $division->setLongName(ucwords(strtolower($division->getLongName())));
            $division->setShortName(strtoupper($division->getShortName()));
            $this->em->flush();
            $this->addFlash('success', 'Division modifiée avec succès !');
            return $this->redirectToRoute('backoffice.divisions');
        }

        return $this->render('backoffice/division/edit.html.twig', [
            'division' => $division,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/division/delete/{idDivision}", name="backoffice.division.delete", methods="DELETE")
     * @param int $idDivision
     * @param Request $request
     * @return Response
     */
    public function delete(int $idDivision, Request $request): Response
    {
        if (!($division = $this->divisionRepository->find($idDivision))) throw $this->createNotFoundException('Division inexistante');

        if ($this->isCsrfTokenValid('delete' . $division->getIdDivision(), $request->get('_token'))) {
            $this->equipeDepartementaleRepository->setDeletedDivisionToNull($idDivision);
            $this->equipeParisRepository->setDeletedDivisionToNull($idDivision);
            $this->em->remove($division);
            $this->em->flush();
            $this->addFlash('success', 'Division supprimée avec succès !');
        } else $this->addFlash('error', 'La division n\'a pas pu être supprimée');

        return $this->render('backoffice/division/index.html.twig', [
            'divisions' => $this->divisionRepository->findAll()
        ]);
    }
}
