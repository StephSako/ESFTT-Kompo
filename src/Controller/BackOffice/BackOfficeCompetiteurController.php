<?php

namespace App\Controller\BackOffice;

use App\Controller\InvalidSelectionController;
use App\Entity\Competiteur;
use App\Form\BackofficeCompetiteurType;
use App\Repository\CompetiteurRepository;
use App\Repository\RencontreDepartementaleRepository;
use App\Repository\RencontreParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class BackOfficeCompetiteurController extends AbstractController
{
    private $em;
    private $competiteurRepository;
    private $rencontreDepartementaleRepository;
    private $rencontreParisRepository;

    /**
     * BackOfficeController constructor.
     * @param CompetiteurRepository $competiteurRepository
     * @param RencontreDepartementaleRepository $rencontreDepartementaleRepository
     * @param RencontreParisRepository $rencontreParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(CompetiteurRepository $competiteurRepository,
                                EntityManagerInterface $em,
                                RencontreDepartementaleRepository $rencontreDepartementaleRepository,
                                RencontreParisRepository $rencontreParisRepository)
    {
        $this->em = $em;
        $this->competiteurRepository = $competiteurRepository;
        $this->rencontreDepartementaleRepository = $rencontreDepartementaleRepository;
        $this->rencontreParisRepository = $rencontreParisRepository;
    }

    /**
     * @Route("/backoffice/competiteurs", name="back_office.competiteurs")
     * @return Response
     */
    public function indexCompetiteurs(): Response
    {
        return $this->render('back_office/competiteur/index.html.twig', [
            'competiteurs' => $this->competiteurRepository->findBy([], ['nom' => 'ASC'])
        ]);
    }

    /**
     * @Route("/backoffice/competiteur/new", name="back_office.competiteur.new")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $competiteur = new Competiteur();
        $form = $this->createForm(BackofficeCompetiteurType::class, $competiteur);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()){
                $this->em->persist($competiteur);
                $this->em->flush();
                $this->addFlash('success', 'Compétiteur créé avec succès !');
                return $this->redirectToRoute('back_office.competiteurs');
            } else {
                $this->addFlash('fail', 'Une erreur est survenue ...');
            }
        }

        return $this->render('back_office/competiteur/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/competiteur/{idCompetiteur}", name="backoffice.competiteur.edit")
     * @param int $idCompetiteur
     * @param Request $request
     * @return Response
     */
    public function edit(int $idCompetiteur, Request $request): Response
    {
        if (!($competiteur = $this->competiteurRepository->find($idCompetiteur))) throw $this->createNotFoundException('Compétiteur inexistant');
        $form = $this->createForm(BackofficeCompetiteurType::class, $competiteur);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()){
                $this->em->flush();
                $this->addFlash('success', 'Compétiteur modifié avec succès !');
                return $this->redirectToRoute('back_office.competiteurs');
            }
            else {
                $this->addFlash('fail', 'Une erreur est survenue ...');
            }
        }

        return $this->render('account/edit.html.twig', [
            'type' => 'backoffice',
            'urlImage' => $competiteur->getAvatar(),
            'path' => 'backoffice.password.edit',
            'competiteur' => $competiteur,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/update_password/{id}", name="backoffice.password.edit")
     * @param Competiteur $competiteur
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return RedirectResponse|Response
     */
    public function updateCompetiteurPassword(Competiteur $competiteur, Request $request, UserPasswordEncoderInterface $encoder){
        $form = $this->createForm(BackofficeCompetiteurType::class, $competiteur);
        $form->handleRequest($request);

        if ($request->request->get('new_password') == $request->request->get('new_password_validate')) {
            $password = $encoder->encodePassword($competiteur, $request->get('new_password'));
            $competiteur->setPassword($password);

            $this->em->flush();
            $this->addFlash('success', 'Mot de passe de l\'utilisateur modifié !');
            return $this->redirectToRoute('back_office.competiteurs');
        }
        else {
            $this->addFlash('fail', 'Les mots de passe ne correspond pas');
        }

        return $this->render('account/edit.html.twig', [
            'competiteur' => $competiteur,
            'path' => 'backoffice.password.edit',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/competiteur/delete/{id}", name="backoffice.competiteur.delete", methods="DELETE")
     * @param Competiteur $competiteur
     * @param Request $request
     * @param InvalidSelectionController $invalidSelectionController
     * @return Response
     */
    public function delete(Competiteur $competiteur, Request $request, InvalidSelectionController $invalidSelectionController): Response
    {
        if ($this->isCsrfTokenValid('delete' . $competiteur->getIdCompetiteur(), $request->get('_token'))) {
            $invalidSelectionController->disengageDeletedPlayerInComposition($this->rencontreDepartementaleRepository->findAll(), $competiteur->getIdCompetiteur(), 'departementale');
            $invalidSelectionController->disengageDeletedPlayerInComposition($this->rencontreParisRepository->findAll(), $competiteur->getIdCompetiteur(), 'paris');
            $this->em->remove($competiteur);
            $this->em->flush();
            $this->addFlash('success', 'Compétiteur supprimé avec succès !');
        } else $this->addFlash('error', 'Le joueur n\'a pas pu être supprimé');

        return $this->render('back_office/competiteur/index.html.twig', [
            'competiteurs' => $this->competiteurRepository->findBy([], ['nom' => 'ASC'])
        ]);
    }
}
