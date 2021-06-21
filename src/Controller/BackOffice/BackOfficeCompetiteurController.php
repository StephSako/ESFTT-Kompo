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
use Vich\UploaderBundle\Handler\UploadHandler;

class BackOfficeCompetiteurController extends AbstractController
{
    private $em;
    private $competiteurRepository;
    private $rencontreRepository;
    private $disponibiliteRepository;
    private $divisionRepository;
    private $uploadHandler;

    /**
     * BackOfficeController constructor.
     * @param CompetiteurRepository $competiteurRepository
     * @param EntityManagerInterface $em
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param DivisionRepository $divisionRepository
     * @param UploadHandler $uploadHandler
     * @param RencontreRepository $rencontreRepository
     */
    public function __construct(CompetiteurRepository $competiteurRepository,
                                EntityManagerInterface $em,
                                DisponibiliteRepository $disponibiliteRepository,
                                DivisionRepository $divisionRepository,
                                UploadHandler $uploadHandler,
                                RencontreRepository $rencontreRepository)
    {
        $this->em = $em;
        $this->competiteurRepository = $competiteurRepository;
        $this->rencontreRepository = $rencontreRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->divisionRepository = $divisionRepository;
        $this->uploadHandler = $uploadHandler;
    }

    /**
     * @Route("/backoffice/competiteurs", name="backoffice.competiteurs")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('backoffice/competiteur/index.html.twig', [
            'competiteurs' => $this->competiteurRepository->findBy([], ['nom' => 'ASC', 'prenom' => 'ASC'])
        ]);
    }

    private function throwExceptionBOAccount(Exception $e, Competiteur $competiteur){
        if ($e->getPrevious()->getCode() == "23000"){
            if (str_contains($e->getPrevious()->getMessage(), 'licence')) $this->addFlash('fail', 'La licence \'' . $competiteur->getLicence() . '\' est déjà attribuée');
            else if (str_contains($e->getPrevious()->getMessage(), 'username')) $this->addFlash('fail', 'Le pseudo \'' . $competiteur->getUsername() . '\' est déjà attribué');
            else if (str_contains($e->getPrevious()->getMessage(), 'CHK_mail_mandatory')) $this->addFlash('fail', 'Au moins une adresse email doit être renseignée');
            else if (str_contains($e->getPrevious()->getMessage(), 'CHK_mail')) $this->addFlash('fail', 'Les deux adresses email doivent être différentes');
            else if (str_contains($e->getPrevious()->getMessage(), 'CHK_phone_number')) $this->addFlash('fail', 'Les deux numéros de téléphone doivent être différents');
            else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
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
                    $this->addFlash('success', 'Compétiteur créé');
                    return $this->redirectToRoute('backoffice.competiteurs');
                } catch(Exception $e){
                    $this->throwExceptionBOAccount($e, $competiteur);
                    return $this->render('backoffice/competiteur/new.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
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
        if (!($competiteur = $this->competiteurRepository->find($idCompetiteur))) {
            $this->addFlash('fail', 'Compétiteur inexistant');
            return $this->redirectToRoute('backoffice.competiteurs');
        }
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) $form = $this->createForm(BackOfficeCompetiteurAdminType::class, $competiteur);
        else $form = $this->createForm(BackOfficeCompetiteurCapitaineType::class, $competiteur);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
                /** Un joueur devenant 'visiteur' est désélectionné de toutes les compositions de chaque championnat ... **/
                if ($competiteur->isVisitor()){
                    for ($i = 0; $i < $this->divisionRepository->getNbJoueursMax()["nbMaxJoueurs"]; $i++) {
                        $this->rencontreRepository->setDeletedCompetiteurToNull($competiteur->getIdCompetiteur(), $i);
                    }

                    /** ... et ses disponiblités sont supprimées */
                    $this->disponibiliteRepository->setDeleteDisposVisiteur($competiteur->getIdCompetiteur());
                }

                if ($competiteur->isAdmin()) {
                    $competiteur->setIsCapitaine(true);
                    $competiteur->setVisitor(false);
                } else if ($competiteur->isVisitor()) {
                    $competiteur->setIsCapitaine(false);
                    $competiteur->setIsAdmin(false);
                }
                $competiteur->setNom(mb_convert_case($competiteur->getNom(), MB_CASE_UPPER, "UTF-8"));
                $competiteur->setPrenom(mb_convert_case($competiteur->getPrenom(), MB_CASE_TITLE, "UTF-8"));
                $this->em->flush();
                $this->addFlash('success', 'Compétiteur modifié');
                return $this->redirectToRoute('backoffice.competiteurs');
            } catch(Exception $e){
                $this->throwExceptionBOAccount($e, $competiteur);
            }
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
    public function updatePassword(Competiteur $competiteur, Request $request, UserPasswordEncoderInterface $encoder){
        $form = $this->createForm(BackOfficeCompetiteurCapitaineType::class, $competiteur);
        $form->handleRequest($request);

        if (strlen($request->request->get('new_password')) && strlen($request->request->get('new_password_validate'))) {
            if ($request->request->get('new_password') == $request->request->get('new_password_validate')) {
                $password = $encoder->encodePassword($competiteur, $request->get('new_password'));
                $competiteur->setPassword($password);

                $this->em->flush();
                $this->addFlash('success', 'Mot de passe de l\'utilisateur modifié');
                return $this->redirectToRoute('backoffice.competiteurs');
            } else $this->addFlash('fail', 'Champs du nouveau mot de passe différents');
        } else $this->addFlash('fail', 'Remplissez tous les champs');

        return $this->redirectToRoute('backoffice.competiteur.edit', [
            'idCompetiteur' => $competiteur->getIdCompetiteur()
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
            for ($i = 0; $i < $this->divisionRepository->getNbJoueursMax()["nbMaxJoueurs"]; $i++) {
                $this->rencontreRepository->setDeletedCompetiteurToNull($competiteur->getIdCompetiteur(), $i);
            }

            $this->em->remove($competiteur);
            $this->em->flush();
            $this->addFlash('success', 'Compétiteur supprimé');
        } else $this->addFlash('error', 'Le joueur n\'a pas pu être supprimé');

        return $this->redirectToRoute('backoffice.competiteurs');
    }

    /**
     * @Route("/backoffice/competiteur/delete/avatar/{id}", name="backoffice.competiteur.delete.avatar")
     * @param Competiteur $competiteur
     * @return Response
     */
    public function deleteAvatar(Competiteur $competiteur): Response
    {
        $this->uploadHandler->remove($competiteur, 'imageFile');
        $competiteur->setAvatar(null);
        $competiteur->setImageFile(null);

        $this->em->flush();
        $this->addFlash('success', 'Avatar supprimé');
        return $this->redirectToRoute('backoffice.competiteur.edit', [
            'idCompetiteur' => $competiteur->getIdCompetiteur()
        ]);
    }
}
