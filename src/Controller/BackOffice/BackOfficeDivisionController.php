<?php

namespace App\Controller\BackOffice;

use App\Entity\Division;
use App\Form\DivisionFormType;
use App\Repository\DivisionRepository;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeDivisionController extends AbstractController
{
    private $em;
    private $divisionRepository;
    private $equipeRepository;

    /**
     * BackOfficeController constructor.
     * @param DivisionRepository $divisionRepository
     * @param EntityManagerInterface $em
     * @param EquipeRepository $equipeRepository
     */
    public function __construct(DivisionRepository $divisionRepository,
                                EntityManagerInterface $em,
                                EquipeRepository $equipeRepository)
    {
        $this->em = $em;
        $this->divisionRepository = $divisionRepository;
        $this->equipeRepository = $equipeRepository;
    }

    /**
     * @Route("/backoffice/divisions", name="backoffice.divisions")
     * @return Response
     */
    public function indexDivisions(): Response
    {
        return $this->render('backoffice/division/index.html.twig', [
            'divisions' => $this->divisionRepository->getAllDivisions()
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
                try {
                    $division->setLongName(ucwords(strtolower($division->getLongName())));
                    $division->setShortName(strtoupper($division->getShortName()));
                    $this->em->persist($division);
                    $this->em->flush();
                    $this->addFlash('success', 'Division créée avec succès !');
                    return $this->redirectToRoute('backoffice.divisions');
                } catch(Exception $e){
                    if ($e->getPrevious()->getCode() == "23000"){
                        if (str_contains($e->getMessage(), 'short_name')) $this->addFlash('fail', 'Le diminutif \'' . $division->getShortName() . '\' est déjà attribué');
                        if (str_contains($e->getMessage(), 'long_name')) $this->addFlash('fail', 'Le nom \'' . $division->getLongName() . '\' est déjà attribué');
                    }
                    else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                }
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('backoffice/new.html.twig', [
            'form' => $form->createView(),
            'title' => 'divisions',
            'macro' => 'division'
        ]);
    }

    /**
     * @Route("/backoffice/division/edit/{idDivision}", name="backoffice.division.edit")
     * @param int $idDivision
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function edit(int $idDivision, Request $request): Response
    {
        if (!($division = $this->divisionRepository->find($idDivision))) throw new Exception('Cette division est inexistante', 500);
        $form = $this->createForm(DivisionFormType::class, $division);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $division->setLongName(ucwords(strtolower($division->getLongName())));
                    $division->setShortName(strtoupper($division->getShortName()));
                    $this->em->flush();
                    $this->addFlash('success', 'Division modifiée avec succès !');
                    return $this->redirectToRoute('backoffice.divisions');
                } catch(Exception $e){
                    if ($e->getPrevious()->getCode() == "23000"){
                        if (str_contains($e->getMessage(), 'short_name')) $this->addFlash('fail', 'Le diminutif \'' . $division->getShortName() . '\' est déjà attribué');
                        if (str_contains($e->getMessage(), 'long_name')) $this->addFlash('fail', 'Le nom \'' . $division->getLongName() . '\' est déjà attribué');
                    }
                }
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('backoffice/edit.html.twig', [
            'division' => $division,
            'form' => $form->createView(),
            'title' => 'Modifier la division',
            'macro' => 'division',
            'textForm' => 'Modifier'
        ]);
    }

    /**
     * @Route("/backoffice/division/delete/{idDivision}", name="backoffice.division.delete", methods="DELETE")
     * @param int $idDivision
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function delete(int $idDivision, Request $request): Response
    {
        if (!($division = $this->divisionRepository->find($idDivision))) throw new Exception('Cette division est inexistante', 500);

        if ($this->isCsrfTokenValid('delete' . $division->getIdDivision(), $request->get('_token'))) {

            /** On vide les compositions des équipes affiliées à la division supprimée car une équipe sans division n'est pas editable **/
            foreach ($division->getEquipes()->toArray() as $equipes){
                foreach ($equipes->getRencontres() as $compo){
                    for ($i = 0; $i < $compo->getIdEquipe()->getIdDivision()->getNbJoueurs(); $i++){
                        $compo->setIdJoueurN($i, null);
                    }
                }
            }

            /** On met la division des équipes affiliées à NULL **/
            $this->equipeRepository->setDeletedDivisionToNull($idDivision);

            $this->em->remove($division);
            $this->em->flush();
            $this->addFlash('success', 'Division supprimée avec succès !');
        } else $this->addFlash('error', 'La division n\'a pas pu être supprimée');

        return $this->redirectToRoute('backoffice.divisions');
    }
}
