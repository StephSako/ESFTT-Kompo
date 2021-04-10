<?php

namespace App\Controller\BackOffice;

use App\Entity\Division;
use App\Form\DivisionFormType;
use App\Repository\DivisionRepository;
use App\Repository\EquipeRepository;
use App\Repository\RencontreRepository;
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
    private $rencontreRepository;

    /**
     * BackOfficeController constructor.
     * @param DivisionRepository $divisionRepository
     * @param EntityManagerInterface $em
     * @param EquipeRepository $equipeRepository
     * @param RencontreRepository $rencontreRepository
     */
    public function __construct(DivisionRepository $divisionRepository,
                                EntityManagerInterface $em,
                                EquipeRepository $equipeRepository,
                                RencontreRepository $rencontreRepository)
    {
        $this->em = $em;
        $this->divisionRepository = $divisionRepository;
        $this->equipeRepository = $equipeRepository;
        $this->rencontreRepository = $rencontreRepository;
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
                    return $this->render('backoffice/division/edit.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
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
                    else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    return $this->render('backoffice/division/edit.html.twig', [
                        'division' => $division,
                        'form' => $form->createView()
                    ]);
                }
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
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
     * @throws Exception
     */
    public function delete(int $idDivision, Request $request): Response
    {
        if (!($division = $this->divisionRepository->find($idDivision))) throw new Exception('Cette division est inexistante', 500);

        if ($this->isCsrfTokenValid('delete' . $division->getIdDivision(), $request->get('_token'))) {

            /** On vide les compos des équipes affiliées à la division qui va être supprimée **/
            $compos = $this->rencontreDepartementaleRepository->getRencontresForDivision($idDivision);
            foreach ($compos as $compo){
                for ($i = 0; $i < $compo->getIdEquipe()->getIdDivision()->getNbJoueursChampDepartementale(); $i++){
                    $compo->setIdJoueurN($i, null);
                }
            }

            $compos = $this->rencontreParisRepository->getRencontresForDivision($idDivision);
            foreach ($compos as $compo){
                for ($i = 0; $i < $compo->getIdEquipe()->getIdDivision()->getNbJoueursChampParis(); $i++){
                    $compo->setIdJoueurN($i, null);
                }
            }

            /** On met la division des équipes affiliées à NULL **/
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
