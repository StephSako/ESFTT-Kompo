<?php

namespace App\Controller\BackOffice;

use App\Controller\ContactController;
use App\Controller\UtilController;
use App\Entity\Competiteur;
use App\Form\CompetiteurType;
use App\Form\SettingsType;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteRepository;
use App\Repository\DivisionRepository;
use App\Repository\RencontreRepository;
use App\Repository\SettingsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use FFTTApi\FFTTApi;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
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
    private $encoder;
    private $settingsRepository;
    private $championnatRepository;

    /**
     * BackOfficeController constructor.
     * @param CompetiteurRepository $competiteurRepository
     * @param EntityManagerInterface $em
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param DivisionRepository $divisionRepository
     * @param UploadHandler $uploadHandler
     * @param UserPasswordEncoderInterface $encoder
     * @param ChampionnatRepository $championnatRepository
     * @param RencontreRepository $rencontreRepository
     * @param SettingsRepository $settingsRepository
     */
    public function __construct(CompetiteurRepository $competiteurRepository,
                                EntityManagerInterface $em,
                                DisponibiliteRepository $disponibiliteRepository,
                                DivisionRepository $divisionRepository,
                                UploadHandler $uploadHandler,
                                UserPasswordEncoderInterface $encoder,
                                ChampionnatRepository $championnatRepository,
                                RencontreRepository $rencontreRepository,
                                SettingsRepository $settingsRepository)
    {
        $this->em = $em;
        $this->competiteurRepository = $competiteurRepository;
        $this->rencontreRepository = $rencontreRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->divisionRepository = $divisionRepository;
        $this->uploadHandler = $uploadHandler;
        $this->encoder = $encoder;
        $this->settingsRepository = $settingsRepository;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/backoffice/competiteurs", name="backoffice.competiteurs")
     * @param ContactController $contactController
     * @return Response
     */
    public function index(ContactController $contactController): Response
    {
        $joueurs = $this->competiteurRepository->findBy(['isArchive' => false], ['nom' => 'ASC', 'prenom' => 'ASC']);
        $joueursArchives = $this->competiteurRepository->findBy(['isArchive' => true], ['nom' => 'ASC', 'prenom' => 'ASC']);

        $onlyOneAdmin = count(array_filter($joueurs, function ($joueur) {
           return $joueur->isAdmin();
        })) == 1;

        $joueursInvalidCertifMedic = array_filter($joueurs, function($joueur) {
            return $joueur->isCertifMedicalInvalid()['status'];
        });

        /** Joueurs sans licence définie */
        $countJoueursWithoutLicence = count(array_filter($joueurs, function ($joueur) {
            return !$joueur->getLicence();
        }));
        $joueursWithoutLicence = [
            'count' => $countJoueursWithoutLicence,
            'message' => $countJoueursWithoutLicence ? 'Il y a <b>' . $countJoueursWithoutLicence . '</b> joueur' . ($countJoueursWithoutLicence > 1 ? 's' : '') . ' dont la licence n\'est pas définie' : ''
        ];

        /** Compétiteurs sans classement officiel défini */
        $countCompetiteursWithoutClassement = count(array_filter($joueurs, function ($joueur) {
            return !$joueur->getClassementOfficiel() && $joueur->isCompetiteur();
        }));
        $competiteursWithoutClassement = [
            'count' => $countCompetiteursWithoutClassement,
            'message' => $countCompetiteursWithoutClassement ? ($countJoueursWithoutLicence ? ' et ' : 'Il y a ' ) . '<b>' . $countCompetiteursWithoutClassement . '</b> compétiteur' . ($countCompetiteursWithoutClassement > 1 ? 's' : '') . ' dont le classement officiel n\'est pas défini' : ''
        ];

        return $this->render('backoffice/competiteur/index.html.twig', [
            'joueurs' => $joueurs,
            'joueursArchives' => $joueursArchives,
            'joueursInvalidCertifMedic' => $joueursInvalidCertifMedic,
            'contactsJoueursInvalidCertifMedic' => $contactController->returnPlayersContact($joueursInvalidCertifMedic),
            'onlyOneAdmin' => $onlyOneAdmin,
            'joueursWithoutLicence' => $joueursWithoutLicence,
            'competiteursWithoutClassement' => $competiteursWithoutClassement
        ]);
    }

    /**
     * @param Exception $e
     * @param Competiteur $competiteur
     * @return void
     */
    private function throwExceptionBOAccount(Exception $e, Competiteur $competiteur){
        if ($e->getPrevious() && $e->getPrevious()->getCode() == "23000"){
            if (str_contains($e->getPrevious()->getMessage(), 'licence')) $this->addFlash('fail', 'La licence \'' . $competiteur->getLicence() . '\' est déjà attribuée');
            else if (str_contains($e->getPrevious()->getMessage(), 'username')) $this->addFlash('fail', 'Le pseudo \'' . $competiteur->getUsername() . '\' est déjà attribué');
            else if (str_contains($e->getPrevious()->getMessage(), 'CHK_mail_mandatory')) $this->addFlash('fail', 'Au moins une adresse e-mail doit être renseignée');
            else if (str_contains($e->getPrevious()->getMessage(), 'CHK_mail')) $this->addFlash('fail', 'Les deux adresses e-mail doivent être différentes');
            else if (str_contains($e->getPrevious()->getMessage(), 'CHK_phone_number')) $this->addFlash('fail', 'Les deux numéros de téléphone doivent être différents');
            else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        } else if ($e->getCode() == '1234') $this->addFlash('fail', $e->getMessage());
        else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
    }

    /**
     * @Route("/backoffice/competiteur/new", name="backoffice.competiteur.new")
     * @param Request $request
     * @param ContactController $contactController
     * @param UtilController $utilController
     * @return Response
     */
    public function new(Request $request, ContactController $contactController, UtilController $utilController): Response
    {
        $competiteur = new Competiteur();
        $competiteur
            ->setIsPasswordResetting(true)
            ->setPassword($this->encoder->encodePassword($competiteur, $this->getParameter('default_password')))
            ->setDateNaissance(null)
            ->setClassementOfficiel($this->getParameter('default_nb_points'))
            ->setIsCompetiteur(true);

        $form = $this->createForm(CompetiteurType::class, $competiteur, [
            'capitaineAccess' => $this->getUser()->isCapitaine(),
            'adminAccess' => $this->getUser()->isAdmin(),
            'dateNaissanceRequired' => false,
            'isCertificatInvalid' => true,
            'createMode' => true,
            'displayCode' => false
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()){
                try {

                    /** On vérifie l'existence de la licence */
                    if (strlen($competiteur->getLicence()) && !$competiteur->isArchive()) {
                        try {
                            $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
                            $api->getJoueurDetailsByLicence($competiteur->getLicence(), $this->getParameter('club_id'));
                        } catch (Exception $e) {
                            throw new Exception('Le joueur avec la licence \'' . $competiteur->getLicence() . '\' n\'existe pas à ' . mb_convert_case($this->getParameter('club_name'), MB_CASE_TITLE, "UTF-8"), '1234');
                        }
                    }

                    /** Une adresse e-mail minimum requise */
                    if (!($competiteur->getMail() ?? $competiteur->getMail2())) throw new Exception('Au moins une adresse e-mail doit être renseignée', 1234);

                    /** On vérifie que le(s) rôle(s) du membre sont cohérents */
                    $this->checkRoles($competiteur);

                    $competiteur->setNom($competiteur->getNom());
                    $competiteur->setPrenom($competiteur->getPrenom());
                    $competiteur->setContactableMail((bool)$competiteur->getMail());
                    $competiteur->setContactableMail2((bool)$competiteur->getMail2());
                    $competiteur->setContactablePhoneNumber((bool)$competiteur->getPhoneNumber());
                    $competiteur->setContactablePhoneNumber2((bool)$competiteur->getPhoneNumber2());

                    $this->em->persist($competiteur);
                    $this->em->flush();

                    /** On envoie un e-mail de bienvenue */
                    if (!$competiteur->isArchive()) $this->sendWelcomeMail($utilController, $contactController, $competiteur, true);

                    $this->addFlash('success', 'Membre créé');
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
     * @param UtilController $utilController
     * @param ContactController $contactController
     * @param Competiteur $competiteur
     * @param bool $isCreation
     * @return void
     * @throws Exception
     */
    public function sendWelcomeMail(UtilController $utilController, ContactController $contactController, Competiteur $competiteur, bool $isCreation): void {
        try {
            /** On envoie un mail spécifique aux loisirs si loisir, sinon le mail général */
            $role = $competiteur->isLoisir() ? '-loisirs' : '';
            $data = $this->settingsRepository->find('mail-bienvenue' . $role)->getContent();

            $initPasswordLink = $utilController->generateGeneratePasswordLink($competiteur->getIdCompetiteur(), 'P' . $this->getParameter('time_init_password_day') . 'D');
            $str_replacers = [
                'old' => ["[#init_password_link#]", "[#pseudo#]", "[#time_init_password_day#]", "[#prenom#]", "[#club_name#]", "[#roles#]"],
                'new' => [
                    "ce <a href=\"$initPasswordLink\">lien</a>",
                    $competiteur->getUsername(),
                    $this->getParameter('time_init_password_day'),
                    $competiteur->getPrenom(),
                    mb_convert_case($this->getParameter('club_name'), MB_CASE_TITLE, "UTF-8"),
                    mb_convert_case($competiteur->getRolesFormatted(), MB_CASE_LOWER, "UTF-8")
                ]
            ];

            /** On contacte les administrateurs à la création **/
            if ($isCreation) {
                $adminsCopy = array_map(function ($joueur) {
                    return new Address($joueur->getFirstContactableMail(), $joueur->getPrenom() . ' ' . $joueur->getNom());
                }, $contactController->returnPlayersContact($this->competiteurRepository->findJoueursByRole('Admin', null))['mail']['contactables']);
            } else $adminsCopy = null;

            $contactController->sendMail(
                [new Address($competiteur->getMail() ?? $competiteur->getMail2(), $competiteur->getNom() . ' ' . $competiteur->getPrenom())],
                true,
                'Bienvenue sur Kompo ' . $competiteur->getPrenom() . ' !',
                $data,
                $str_replacers,
                false,
                $adminsCopy);

            if ($isCreation) $this->addFlash('success', 'E-mail de bienvenue envoyé');
        } catch (Exception $e) {
            if ($isCreation) $this->addFlash('fail', 'E-mail de bienvenue non envoyé');
            else throw new Exception('E-mail de bienvenue non renvoyé', '1234');
        }
    }

    /**
     * @Route("/backoffice/competiteur/resend-welcome-mail", name="backoffice.competiteur.resend-welcome-mail", requirements={"idCompetiteur"="\d+"})
     * @param Request $request
     * @param UtilController $utilController
     * @param ContactController $contactController
     * @return Response
     */
    public function resendWelcomeMail(Request $request, UtilController $utilController, ContactController $contactController): Response
    {
        try {
            if (!($competiteur = $this->competiteurRepository->find($request->request->get('idCompetiteur')))) {
                $this->addFlash('fail', 'Membre inexistant');
                return $this->redirectToRoute('backoffice.competiteurs');
            }

            $this->sendWelcomeMail($utilController, $contactController, $competiteur, false);

            $json = json_encode(['message' => 'E-mail de bienvenue renvoyé à ' . $competiteur->getPrenom(), 'success' => true]);
        } catch (Exception $e) {
            $json = json_encode(['message' => $e->getCode() == 1234 ? $e->getMessage() : "Une erreur s'est produite", 'success' => false]);
        }

        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/backoffice/competiteur/{idCompetiteur}", name="backoffice.competiteur.edit", requirements={"idCompetiteur"="\d+"})
     * @param int $idCompetiteur
     * @param Request $request
     * @param UtilController $utilController
     * @return Response
     * @throws Exception
     */
    public function edit(int $idCompetiteur, Request $request, UtilController $utilController): Response
    {
        if (!($competiteur = $this->competiteurRepository->find($idCompetiteur))) {
            $this->addFlash('fail', 'Membre inexistant');
            return $this->redirectToRoute('backoffice.competiteurs');
        }

        $usernameEditable = !(($this->getUser()->isCapitaine() && !$this->getUser()->isAdmin()) && $competiteur->isAdmin() && $this->getUser()->getIdCompetiteur() != $competiteur->getIdCompetiteur());
        $form = $this->createForm(CompetiteurType::class, $competiteur, [
            'isCertificatInvalid' => (!$competiteur->getAge() || $competiteur->getAge() >= 18) && $competiteur->getAnneeCertificatMedical() == null && !$competiteur->isArchive(),
            'capitaineAccess' => $this->getUser()->isCapitaine(),
            'adminAccess' => $this->getUser()->isAdmin(),
            'isArchived' => $competiteur->isArchive(),
            'dateNaissanceRequired' => $competiteur->getDateNaissance() != null,
            'usernameEditable' => $usernameEditable,
            'displayCode' => $this->getUser()->isAdmin() && !$competiteur->isArchive()
        ]);
        $form->handleRequest($request);

        /** Variable permettant de controler le fait qu'il doit y avoir minimum un administrateur restant */
        $onlyOneAdmin = count($this->competiteurRepository->findJoueursByRole('Admin', null)) == 1;

        if ($form->isSubmitted()) {
            if ($form->isValid()){
                try {
                    /** On vérifie qu'il y aie au minimum un administrateur restant parmi les membres actifs si l'utilisateur admin actuel est le dernier administrateur souhaitant ne plus l'être */
                    if ($onlyOneAdmin && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                        $competiteur->setIsArchive(false);
                        $competiteur->setIsAdmin(true);
                        $this->addFlash('fail', 'Un administrateur minimum requis');
                    } else {
                        /** On vérifie l'existence de la licence */
                        if (strlen($competiteur->getLicence())) {
                            try {
                                $api = new FFTTApi($this->getParameter('fftt_api_login'), $this->getParameter('fftt_api_password'));
                                $api->getJoueurDetailsByLicence($competiteur->getLicence(), $this->getParameter('club_id'));
                            } catch (Exception $e) {
                                throw new Exception('Le joueur avec la licence \'' . $competiteur->getLicence() . '\' n\'existe pas à ' . mb_convert_case($this->getParameter('club_name'), MB_CASE_TITLE, "UTF-8"), '1234');
                            }
                        }

                        /** On vérifie que le(s) rôle(s) du membre sont cohérents */
                        $this->checkRoles($competiteur);

                        /** Un joueur devenant non-compétiteur est désélectionné de toutes les compositions de chaque championnat des journées ultèrieures à aujourd'hui ... **/
                        if (!$competiteur->isCompetiteur()){
                            $rencontres = $this->rencontreRepository->getSelectionInChampCompos($competiteur->getIdCompetiteur(), $this->divisionRepository->getNbJoueursMax()["nbMaxJoueurs"], true);

                            /** On supprime le joueur des compos d'équipe ... */
                            $this->deletePlayerInSelections($rencontres, $competiteur->getIdCompetiteur());

                            /** ... on trie les compos qui ont un tri automatique ... */
                            $rencontresToSort = array_filter($rencontres, function($rencontre) {
                                return $rencontre->getIdChampionnat()->isCompoSorted();
                            });
                            foreach ($rencontresToSort as $selectionToSort) {
                                $this->em->refresh($selectionToSort);
                                $selectionToSort->sortComposition();
                            }

                            /** ... et ses disponibilités sont supprimées */
                            $this->disponibiliteRepository->setDeleteDispos($competiteur->getIdCompetiteur());
                        }

                        if ($competiteur->isArchive()) $competiteur->setAnneeCertificatMedical(null);
                        $competiteur->setNom($competiteur->getNom());
                        $competiteur->setPrenom($competiteur->getPrenom());

                        $isCurrentAdminTheEditedUser = $competiteur->getIdCompetiteur() === $this->getUser()->getIdCompetiteur();
                        /** On met ses rôles à jour dans le token de session pour ne pas être déconnecté */
                        if ($isCurrentAdminTheEditedUser) {
                            $this->get('security.token_storage')->setToken(new UsernamePasswordToken($competiteur, null, 'main', $competiteur->getRoles()));
                        }

                        $this->em->flush();
                        $this->addFlash('success', 'Membre modifié');

                        /** Si l'user n'est plus admin ni capitaine suite à la modification de ses rôles, ... */
                        if ($isCurrentAdminTheEditedUser && !in_array('ROLE_ADMIN', $this->getUser()->getRoles()) && !in_array('ROLE_CAPITAINE', $this->getUser()->getRoles())) {
                            if ($competiteur->isArchive()) {
                                /** ... et qu'il devient archivé, on le déconnecte et on le redirige vers la page de connexion */
                                $this->get('security.token_storage')->setToken();
                                $this->get('session')->invalidate();
                                return $this->redirectToRoute('logout');
                            }
                            else {
                                /** ... on le redirige vers la page d'accueil */
                                return $this->redirectToRoute('index.type', [
                                    'type' => $this->get('session')->get('type') ? $this->championnatRepository->find($this->get('session')->get('type'))->getIdChampionnat() : $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat()->getIdChampionnat()
                                ]);
                            }
                        }

                        return $this->redirectToRoute('backoffice.competiteurs');
                    }
                } catch(Exception $e){
                    $competiteur->setIsArchive(false);
                    $this->throwExceptionBOAccount($e, $competiteur);
                }
            } else {
                $competiteur->setIsArchive(false);
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('account/edit.html.twig', [
            'type' => 'backoffice',
            'urlImage' => $competiteur->getAvatar(),
            'anneeCertificatMedical' => $competiteur->getAnneeCertificatMedical(),
            'age' => $competiteur->getAge(),
            'categorieAge' => $competiteur->getCategorieAgeLabel(),
            'isCritFed' => $competiteur->isCritFed(),
            'path' => 'backoffice.password.edit',
            'isArchived' => $competiteur->isArchive(),
            'isLoisir' => $competiteur->isLoisir(),
            'isAdmin' => $competiteur->isAdmin(),
            'onlyOneAdmin' => $onlyOneAdmin,
            'competiteurId' => $competiteur->getIdCompetiteur(),
            'usernameEditable' => $usernameEditable,
            'form' => $form->createView(),
            'cheating' => $competiteur->isCheating(),
            'winner' => $competiteur->isWinner(),
            'fullWinner' => $competiteur->isFullWinner(),
            'profileCompletion' => $competiteur->profileCompletion()
        ]);
    }

    /**
     * @Route("/backoffice/update_password/{id}", name="backoffice.password.edit", requirements={"id"="\d+"})
     * @param Competiteur $competiteur
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function updatePassword(Competiteur $competiteur, Request $request){
        $form = $this->createForm(CompetiteurType::class, $competiteur, [
            'capitaineAccess' => true
        ]);
        $form->handleRequest($request);

        if (strlen($request->request->get('new_password')) && strlen($request->request->get('new_password_validate'))) {
            if ($request->request->get('new_password') == $request->request->get('new_password_validate')) {
                $password = $this->encoder->encodePassword($competiteur, $request->get('new_password'));
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
            $rencontres = $this->rencontreRepository->getSelectionInChampCompos($competiteur->getIdCompetiteur(), $this->divisionRepository->getNbJoueursMax()["nbMaxJoueurs"], false);

            /** On supprime le joueur des compos d'équipe ... */
            $this->deletePlayerInSelections($rencontres, $competiteur->getIdCompetiteur());

            /** ... on trie les compos qui ont un tri automatique pour les futures journées uniquement */
            $rencontresToSort = array_filter($rencontres, function($rencontre) {
                return $rencontre->getIdChampionnat()->isCompoSorted() && $rencontre->getIdJournee()->getDateJournee() >= new DateTime();
            });
            foreach ($rencontresToSort as $selectionToSort) {
                $this->em->refresh($selectionToSort);
                $selectionToSort->sortComposition();
            }

            $idDeleted = $competiteur->getIdCompetiteur();
            $currentUserId = $this->getUser()->getIdCompetiteur();

            $this->em->remove($competiteur);
            $this->em->flush();
            $this->addFlash('success', 'Membre supprimé');

            if ($idDeleted == $currentUserId) {
                $this->get('security.token_storage')->setToken();
                $this->get('session')->invalidate();
                return $this->redirectToRoute('logout');
            }
        } else $this->addFlash('error', 'Le membre n\'a pas pu être supprimé');

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

    /**
     * @Route("/backoffice/competiteur/renouveler/certificat/{competiteur}", name="backoffice.competiteur.renouveler.certificat", methods={"POST"})
     * @param Competiteur $competiteur
     * @return Response
     */
    public function renouvelerCertificat(Competiteur $competiteur): Response
    {
        try {
            $competiteur->renouvelerAnneeCertificatMedical();
            $json = json_encode(['status' => true, 'message' => $competiteur->isCertifMedicalInvalid()['shortMessage']]);
            $this->em->flush();
        } catch (Exception $e) {
            $json = json_encode(['status' => false, 'message' => 'Une erreur est survenue']);
        }

        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getDataForPDF(): array
    {
        $competiteursList = [];
        $competiteurs = $this->competiteurRepository->findBy([], ['nom' => 'ASC', 'prenom' => 'ASC']);

        foreach ($competiteurs as $user) {
            $competiteursList[] = $user->serializeToPDF();
        }
        return $competiteursList;
    }

    /**
     * @Route("/backoffice/competiteurs/export-excel", name="backoffice.competiteurs.export.excel")
     * @return Response
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    public function exportCompetiteursExcel(): Response
    {
        $dataCompetiteurs = $this->getDataForPDF();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        /** On set les noms des colonnes */
        $headers = ['Licence', 'Nom', 'Prénom', 'Date de naissance', 'Points officiels', 'Classement', 'Critérium fédéral', 'Catégorie', 'Certificat médical', 'E-mail n°1', 'E-mail n°2', 'Téléphone n°1', 'Téléphone n°2', 'Rôles'];
        for ($col = 'A', $i = 0; $col !== 'O'; $col++, $i++) {
            $sheet->setCellValue($col . '1', $headers[$i]);
        }

        /** On set le style des headers */
        $sheet->getStyle('A1:N1')->applyFromArray(
            array(
                'fill' => array(
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => array('argb' => 'FF4F81BD')
                ),
                'font'  => array(
                    'bold'  =>  true,
                    'color' => array('rgb' => 'FFFFFF' )
                )
            )
        );

        $sheet->fromArray($dataCompetiteurs,'', 'A2', true);
        /** On resize automatiquement les colonnes */
        for($col = 'A'; $col !== 'O'; $col++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        /** On envoie le fichier en téléchargement */
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="competiteurs.xlsx"');
        $response->headers->set('Cache-Control','max-age=0');
        return $response;
    }

    /**
     * @param Competiteur $competiteur
     * @throws Exception
     */
    public function checkRoles(Competiteur $competiteur){
        if (!count($competiteur->getRoles())) throw new Exception('Le joueur doit avoir au moins un rôle', 1234);
        if ((($competiteur->isCompetiteur() || $competiteur->isCapitaine()) && ($competiteur->isLoisir() || $competiteur->isArchive())) ||
            (!$competiteur->isCompetiteur() && $competiteur->isCritFed()) ||
            ($competiteur->isArchive() && ($competiteur->isCritFed() || $competiteur->isLoisir() || $competiteur->isCompetiteur() || $competiteur->isAdmin() || $competiteur->isCapitaine() || $competiteur->isEntraineur())) ||
            ($competiteur->isLoisir() && ($competiteur->isCritFed() || $competiteur->isCompetiteur() || $competiteur->isArchive() || $competiteur->isCapitaine()))){
            throw new Exception('Les rôles sont incohérents', 1234);
        }
    }

    /**
     * @Route("/backoffice/competiteurs/mail/edit/{type}", name="backoffice.mail.edit")
     * @param Request $request
     * @param string $type
     * @return Response
     */
    public function editMailContent(Request $request, string $type): Response
    {
        $setting = $this->settingsRepository->find($type);
        if (!$setting) {
            $this->addFlash('fail', 'Page du mail inexistant');
            return $this->redirectToRoute('backoffice.competiteurs');
        }

        $data = $setting->getContent();
        $title = $setting->getTitle();

        // On stylise les variables dans l'éditeur
        preg_match_all('/\[\#(.*?)\#\]/', $data, $matches);
        $str_replacers = ['old' => [], 'new' => []];

        foreach ($matches[0] as $value) {
            $str_replacers['old'][] = $value;
            $str_replacers['new'][] = "<span class='editor_variable_highlighted'>$value</span>";
        }

        $data = str_replace($str_replacers['old'], $str_replacers['new'], $data);

        $typeBDDed = str_replace('-', '_', $type);
        $form = $this->createForm(SettingsType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                /** Si le nombre de variable augmente, on renvoie une erreur */
                preg_match_all('/\[\#(.*?)\#\]/', $form->getData()->getContent(), $input); // Regex depuis l'éditeur WYSIWYG

                if (count($matches[0]) != count($input[0])) $this->addFlash('fail', 'Il y a une différence de ' . abs((count($input[0]) - count($matches[0]))) . ' variable' . (abs((count($input[0]) - count($matches[0]))) > 1 ? 's' : ''));
                else {
                    $this->em->flush();
                    $this->addFlash('success', 'Contenu de l\'e-mail modifié');
                    return $this->redirectToRoute('backoffice.competiteurs');
                }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('backoffice/competiteur/mailContentEditor.hml.twig', [
            'form' => $this->getUser()->isAdmin() ? $form->createView() : null,
            'HTMLContent' => $data,
            'variables' => $matches[0],
            'title' => $title,
            'typeBDDed' => $typeBDDed
        ]);
    }

    /**
     * On supprime le joueur des compos d'équipe des journées ultèrieures à aujourd'hui inclus
     * @param array $rencontres
     * @param int $idCompetiteur
     * @return void
     * @throws NonUniqueResultException
     */
    public function deletePlayerInSelections(array $rencontres, int $idCompetiteur): void {
        /** On supprime le joueur des compos d'équipe ... */
        foreach ($rencontres as $rencontre) {
            for ($i = 0; $i < $this->divisionRepository->getNbJoueursMax()["nbMaxJoueurs"]; $i++) {
                if ($rencontre->getIdJoueurN($i) && $rencontre->getIdJoueurN($i)->getIdCompetiteur() == $idCompetiteur){
                    $rencontre->setIdJoueurN($i, null);
                    $this->em->flush();
                }
            }
        }
    }

    /**
     * @Route("/backoffice/competiteurs/mail/certif-medic-perim", name="backoffice.alert.certif-medic-perim")
     * @param ContactController $contactController
     * @return Response
     */
    public function alertCertifMedicPerimes(ContactController $contactController): Response
    {
        $mails = array_map(function ($address) {
                return new Address($address);
            }, explode(',', $contactController->returnPlayersContact(
                array_filter($this->competiteurRepository->findBy(['isArchive' => false], ['nom' => 'ASC', 'prenom' => 'ASC']), function ($joueur) {
            return $joueur->isCertifMedicalInvalid()['status'];
        }))['mail']['toString']));

        $message = $this->settingsRepository->find('mail-certif-medic-perim')->getContent();

        $str_replacers = [
            'old' => ['[#annee_saison#]'],
            'new' => [(new DateTime())->format('Y') . '/' . (intval((new DateTime())->format('Y'))+1)]
        ];

        try {
            $contactController->sendMail(
                $mails,
                true,
                'Kompo - Certificat médical à renouveler',
                $message,
                $str_replacers,
                true);
            $this->addFlash('success', "L'alerte a été envoyée");
        } catch (Exception $e) {
            $this->addFlash('fail', "L'alerte n'a pas pu être envoyée");
        }
        return $this->redirectToRoute('backoffice.competiteurs');
    }
}
