<?php

namespace App\Controller\BackOffice;

use App\Entity\Competiteur;
use App\Form\BackOfficeCompetiteurAdminType;
use App\Form\BackOfficeCompetiteurCapitaineType;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteRepository;
use App\Repository\DivisionRepository;
use App\Repository\RencontreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
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
    private $rencontreRepository;
    private $disponibiliteRepository;
    private $divisionRepository;

    /**
     * BackOfficeController constructor.
     * @param CompetiteurRepository $competiteurRepository
     * @param EntityManagerInterface $em
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param DivisionRepository $divisionRepository
     * @param RencontreRepository $rencontreRepository
     */
    public function __construct(CompetiteurRepository $competiteurRepository,
                                EntityManagerInterface $em,
                                DisponibiliteRepository $disponibiliteRepository,
                                DivisionRepository $divisionRepository,
                                RencontreRepository $rencontreRepository)
    {
        $this->em = $em;
        $this->competiteurRepository = $competiteurRepository;
        $this->rencontreRepository = $rencontreRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->divisionRepository = $divisionRepository;
    }

    /**
     * @Route("/backoffice/competiteurs", name="backoffice.competiteurs")
     * @return Response
     */
    public function indexCompetiteurs(): Response
    {
        return $this->render('backoffice/competiteur/index.html.twig', [
            'competiteurs' => $this->competiteurRepository->findBy([], ['nom' => 'ASC', 'prenom' => 'ASC'])
        ]);
    }

    private function throwExceptionBOAccount(Exception $e, Competiteur $competiteur){
        if ($e->getPrevious()->getCode() == "23000"){
            if (str_contains($e->getMessage(), 'licence')) $this->addFlash('fail', 'La licence \'' . $competiteur->getLicence() . '\' est déjà attribuée');
            if (str_contains($e->getMessage(), 'username')) $this->addFlash('fail', 'Le pseudo \'' . $competiteur->getUsername() . '\' est déjà attribué');
            if (str_contains($e->getMessage(), 'CHK_mail')) $this->addFlash('fail', 'Les deux adresses emails doivent être différentes');
            if (str_contains($e->getMessage(), 'CHK_phone_number')) $this->addFlash('fail', 'Les deux numéros de téléphone doivent être différents');
        }
        else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
    }

    /**
     * @Route("/backoffice/competiteur/new", name="backoffice.competiteur.new")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $competiteur = new Competiteur();
        if (in_array("ROLE_ADMIN", $this->getUser()->getRoles())) $form = $this->createForm(BackOfficeCompetiteurAdminType::class, $competiteur);
        else $form = $this->createForm(BackOfficeCompetiteurCapitaineType::class, $competiteur);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()){
                try {
                    $competiteur->setNom(mb_convert_case($competiteur->getNom(), MB_CASE_UPPER, "UTF-8"));
                    $competiteur->setPrenom(mb_convert_case($competiteur->getPrenom(), MB_CASE_TITLE, "UTF-8"));
                    $this->em->persist($competiteur);
                    $this->em->flush();
                    $this->addFlash('success', 'Compétiteur créé avec succès !');
                    return $this->redirectToRoute('backoffice.competiteurs');
                } catch(Exception $e){
                    $this->throwExceptionBOAccount($e);
                    return $this->render('backoffice/competiteur/new.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
            } else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('backoffice/competiteur/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/competiteur/{idCompetiteur}", name="backoffice.competiteur.edit", requirements={"idCompetiteur"="\d+"})
     * @param int $idCompetiteur
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function edit(int $idCompetiteur, Request $request): Response
    {
        if (!($competiteur = $this->competiteurRepository->find($idCompetiteur))) throw new Exception('Ce compétiteur est inexistant', 500);
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) $form = $this->createForm(BackOfficeCompetiteurAdminType::class, $competiteur);
        else $form = $this->createForm(BackOfficeCompetiteurCapitaineType::class, $competiteur);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()){
                try {
                    /** Un joueur devenant 'visiteur' est désélectionné de toutes les compositions de chaque championnat ... **/
                    if ($competiteur->isVisitor()){
                        for ($i = 0; $i < $this->divisionRepository->getNbJoueursMax()["nbMaxJoueurs"]; $i++) {
                            $this->rencontreRepository->setDeletedCompetiteurToNull($competiteur->getIdCompetiteur(), $i);
                        }

                        /** ... et ses disponiblités sont supprimées */
                        $this->disponibiliteRepository->setDeleteDisposVisiteur($competiteur->getIdCompetiteur());
                    }

                    if ($competiteur->isAdmin()){
                        $competiteur->setIsCapitaine(true);
                        $competiteur->setVisitor(false);
                    }
                    else if ($competiteur->isVisitor()){
                        $competiteur->setIsCapitaine(false);
                        $competiteur->setIsAdmin(false);
                    }
                    $competiteur->setNom(mb_convert_case($competiteur->getNom(), MB_CASE_UPPER, "UTF-8"));
                    $competiteur->setPrenom(mb_convert_case($competiteur->getPrenom(), MB_CASE_TITLE, "UTF-8"));
                    $this->em->flush();
                    $this->addFlash('success', 'Compétiteur modifié avec succès !');
                    return $this->redirectToRoute('backoffice.competiteurs');
                } catch(Exception $e){ $this->throwExceptionBOAccount($e); }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('account/edit.html.twig', [
            'type' => 'backoffice',
            'urlImage' => $competiteur->getAvatar(),
            'path' => 'backoffice.password.edit',
            'competiteurId' => $idCompetiteur,
            'competiteurIsVisitor' => $competiteur->isVisitor(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/update_password/{id}", name="backoffice.password.edit", requirements={"id"="\d+"})
     * @param Competiteur $competiteur
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return RedirectResponse|Response
     */
    public function updateCompetiteurPassword(Competiteur $competiteur, Request $request, UserPasswordEncoderInterface $encoder){
        $form = $this->createForm(BackOfficeCompetiteurCapitaineType::class, $competiteur);
        $form->handleRequest($request);

        if ($request->request->get('new_password') == $request->request->get('new_password_validate')) {
            $password = $encoder->encodePassword($competiteur, $request->get('new_password'));
            $competiteur->setPassword($password);

            $this->em->flush();
            $this->addFlash('success', 'Mot de passe de l\'utilisateur modifié !');
            return $this->redirectToRoute('backoffice.competiteurs');
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
     * @Route("/backoffice/competiteur/delete/{id}", name="backoffice.competiteur.delete", methods="DELETE", requirements={"id"="\d+"})
     * @param Competiteur $competiteur
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function delete(Competiteur $competiteur, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $competiteur->getIdCompetiteur(), $request->get('_token'))) {

            /** On set à NULL ses sélections dans les compositions d'équipe */
            for ($i = 0; $i < $this->divisionRepository->getNbJoueursMax(); $i+=1) {
                $this->rencontreRepository->setDeletedCompetiteurToNull($competiteur->getIdCompetiteur(), $i);
            }

            $this->em->remove($competiteur);
            $this->em->flush();
            $this->addFlash('success', 'Compétiteur supprimé avec succès !');
        } else $this->addFlash('error', 'Le joueur n\'a pas pu être supprimé');

        return $this->redirectToRoute('backoffice.competiteurs');
    }
}
