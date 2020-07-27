<?php

namespace App\Controller\BackOffice;

use App\Entity\Competiteur;
use App\Form\BackofficeCompetiteurType;
use App\Repository\CompetiteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class BackOfficeCompetiteurController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var CompetiteurRepository
     */
    private $competiteurRepository;

    /**
     * BackOfficeController constructor.
     * @param CompetiteurRepository $competiteurRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(CompetiteurRepository $competiteurRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->competiteurRepository = $competiteurRepository;
    }

    /**
     * @Route("/backoffice/competiteurs", name="back_office.competiteurs")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function indexCompetiteurs(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $competiteur = new Competiteur();
        $form = $this->createForm(BackofficeCompetiteurType::class, $competiteur);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            dump($competiteur);
            if ($form->isValid()){
                $this->em->persist($competiteur);
                $this->em->flush();
                $this->addFlash('success', 'Compétiteur créé avec succès !');
                return $this->redirectToRoute('back_office.competiteurs');
            } else {
                dump($form->getErrors());
                $this->addFlash('fail', 'Une erreur est survenue ...');
            }
        }

        return $this->render('back_office/competiteurs.html.twig', [
            'competiteurs' => $this->competiteurRepository->findBy([], ['nom' => 'ASC']),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/competiteur/{id}", name="backoffice.account.edit")
     * @param Competiteur $competiteur
     * @param Request $request
     * @return Response
     */
    public function editCompetiteurAccount(Competiteur $competiteur, Request $request)
    {
        $form = $this->createForm(BackofficeCompetiteurType::class, $competiteur);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()){
                $this->em->flush();
                $this->addFlash('success', 'Utilisateur modifié avec succès !');
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
}
