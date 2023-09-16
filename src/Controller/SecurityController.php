<?php

namespace App\Controller;

use App\Form\CompetiteurType;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteRepository;
use App\Repository\RencontreRepository;
use App\Repository\SettingsRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Vich\UploaderBundle\Handler\UploadHandler;

class SecurityController extends AbstractController
{
    private $em;
    private $championnatRepository;
    private $utils;
    private $uploadHandler;
    private $encoder;
    private $competiteurRepository;
    private $disponibiliteRepository;
    private $settingsRepository;
    private $rencontreRepository;
    private $logger;

    /**
     * SecurityController constructor.
     * @param CompetiteurRepository $competiteurRepository
     * @param ChampionnatRepository $championnatRepository
     * @param EntityManagerInterface $em
     * @param SettingsRepository $settingsRepository
     * @param AuthenticationUtils $utils
     * @param RencontreRepository $rencontreRepository
     * @param UploadHandler $uploadHandler
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param LoggerInterface $logger
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(CompetiteurRepository        $competiteurRepository,
                                ChampionnatRepository        $championnatRepository,
                                EntityManagerInterface       $em,
                                SettingsRepository           $settingsRepository,
                                AuthenticationUtils          $utils,
                                RencontreRepository          $rencontreRepository,
                                UploadHandler                $uploadHandler,
                                DisponibiliteRepository      $disponibiliteRepository,
                                LoggerInterface              $logger,
                                UserPasswordEncoderInterface $encoder)
    {
        $this->em = $em;
        $this->championnatRepository = $championnatRepository;
        $this->utils = $utils;
        $this->uploadHandler = $uploadHandler;
        $this->encoder = $encoder;
        $this->competiteurRepository = $competiteurRepository;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->settingsRepository = $settingsRepository;
        $this->rencontreRepository = $rencontreRepository;
        $this->logger = $logger;
    }

    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function login(AuthenticationUtils $utils): Response
    {
        if ($this->getUser() != null) return $this->redirectToRoute('index');
        else {
            return $this->render('account/login.html.twig', [
                'error' => $utils->getLastAuthenticationError()
            ]);
        }
    }

    /**
     * @Route("/compte", name="account")
     * @param Request $request
     * @param UtilController $utilController
     * @return RedirectResponse|Response
     */
    public function edit(Request $request, UtilController $utilController)
    {
        $checkIsBackOffice = $utilController->keepBackOfficeNavbar('account', [], $request->query->get('backoffice'));
        if ($checkIsBackOffice['issue']) return $checkIsBackOffice['redirect'];
        else $isBackoffice = $request->query->get('backoffice') == 'true';

        $championnat = $disposJoueurFormatted = $journees = $journeesWithReportedRencontres = null;
        $allChampionnats = $this->championnatRepository->getAllChampionnats();
        $equipesAssociees = $this->getUser()->getTableEquipesAssociees($allChampionnats);

        if (!$isBackoffice) {
            if (!$this->get('session')->get('type')) $championnat = $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat();
            else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat());

            $journees = ($championnat ? $championnat->getJournees()->toArray() : []);

            // Disponibilités du joueur
            $id = $championnat->getIdChampionnat();
            $disposJoueur = $this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idChampionnat' => $id]);
            if ($this->getUser()->isCompetiteur()) {
                $disposJoueurFormatted = [];
                foreach ($disposJoueur as $dispo) {
                    $disposJoueurFormatted[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
                }
            }

            $journeesWithReportedRencontres = $this->rencontreRepository->getJourneesWithReportedRencontres($championnat->getIdChampionnat())['ids'];
        }
        $user = $this->getUser();
        $form = $this->createForm(CompetiteurType::class, $user, [
            'dateNaissanceRequired' => $this->getUser()->getDateNaissance() == null
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    if ($user->getDateNaissance() > new DateTime()) $this->addFlash('fail', 'Date de naissance dans le futur');
                    else {
                        $user->setNom($user->getNom());
                        $user->setPrenom($user->getPrenom());
                        $this->em->flush();
                        $this->addFlash('success', 'Informations modifiées');
                        return $this->redirectToRoute('account');
                    }
                } catch (Exception $e) {
                    if ($e->getPrevious()->getCode() == "23000") {
                        if (str_contains($e->getPrevious()->getMessage(), 'username')) $this->addFlash('fail', "Le pseudo '" . $user->getUsername() . "' est déjà attribué");
                        else if (str_contains($e->getPrevious()->getMessage(), 'CHK_mail_mandatory')) $this->addFlash('fail', 'Au moins une adresse e-mail doit être renseignée');
                        else if (str_contains($e->getPrevious()->getMessage(), 'CHK_mail')) $this->addFlash('fail', 'Les deux adresses e-mail doivent être différentes');
                        else if (str_contains($e->getPrevious()->getMessage(), 'CHK_phone_number')) $this->addFlash('fail', 'Les deux numéros de téléphone doivent être différents');
                        else $this->addFlash('fail', "Le formulaire n'est pas valide");
                    } else $this->addFlash('fail', "Le formulaire n'est pas valide");
                }
            } else $this->addFlash('fail', $utilController->getFormDeepErrors($form));
        }

        return $this->render('account/edit.html.twig', [
            'type' => 'general',
            'urlImage' => $user->getAvatar(),
            'anneeCertificatMedical' => $user->getAnneeCertificatMedical(),
            'age' => $user->getAge(),
            'path' => 'account.update.password',
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'disposJoueur' => $disposJoueurFormatted,
            'journees' => $journees,
            'journeesWithReportedRencontres' => $journeesWithReportedRencontres,
            'equipesAssociees' => $equipesAssociees,
            'form' => $form->createView(),
            'isBackOffice' => $isBackoffice
        ]);
    }

    /**
     * @Route("/compte/update-password", name="account.update.password")
     * @param Request $request
     * @return Response
     */
    public function updatePassword(Request $request): Response
    {
        $user = $this->getUser();
        $formCompetiteur = $this->createForm(CompetiteurType::class, $user);
        $formCompetiteur->handleRequest($request);

        if (strlen($request->request->get('new_password')) && strlen($request->request->get('new_password_validate')) && strlen($request->request->get('actual_password'))) {
            if ($this->encoder->isPasswordValid($user, $request->request->get('actual_password'))) {
                if ($request->request->get('new_password') == $request->request->get('new_password_validate')) {
                    $user->setPassword($this->encoder->encodePassword($user, $request->get('new_password')));
                    $this->em->flush();
                    $this->addFlash('success', 'Mot de passe modifié');
                } else $this->addFlash('fail', 'Champs du nouveau mot de passe différents');
            } else $this->addFlash('fail', 'Mot de passe actuel incorrect');
        } else $this->addFlash('fail', 'Remplissez tous les champs');

        return $this->redirectToRoute('account');
    }

    /**
     * @Route("/compte/delete/avatar", name="account.delete.avatar")
     * @return Response
     */
    public function deleteAvatar(): Response
    {
        if ($this->getUser() != null) {
            $this->uploadHandler->remove($this->getUser(), 'imageFile');
            $this->getUser()->setAvatar(null);
            $this->getUser()->setImageFile(null);

            $this->em->flush();
            $this->addFlash('success', 'Avatar supprimé');
        } else {
            return $this->render('account/login.html.twig', [
                'lastUsername' => $this->utils->getLastUsername(),
                'error' => $this->utils->getLastAuthenticationError()
            ]);
        }
        return $this->redirectToRoute('account');
    }

    /**
     * @Route("/login/contact/forgotten-password", name="contact.reset.password", methods={"POST"})
     * @param Request $request
     * @param UtilController $utilController
     * @param ContactController $contactController
     * @return Response
     * @throws Exception
     */
    public function contactResetPassword(Request $request, UtilController $utilController, ContactController $contactController): Response
    {
        if ($this->getUser() != null) return $this->redirectToRoute('index');
        else {
            $mail = $request->request->get('mail');
            $username = $request->request->get('username');
            $competiteur = $this->competiteurRepository->findJoueurResetPassword($username, $mail);

            if (!$competiteur) {
                $response = new Response(json_encode(['message' => 'Ce pseudo et cet e-mail ne sont pas associés', 'success' => false]));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $data = $this->settingsRepository->find('mail-mdp-oublie')->getContent();
            $resetPasswordLink = $utilController->generateGeneratePasswordLink($competiteur->getIdCompetiteur(), 'PT' . $this->getParameter('time_reset_password_hour') . 'H');
            $str_replacers = [
                'old' => ['[#lien_reset_password#]', '[#time_reset_password_hour#]'],
                'new' => ["ce <a href=\"$resetPasswordLink\">lien</a>", $this->getParameter('time_reset_password_hour')]
            ];

            $competiteur->setIsPasswordResetting(true);
            $this->em->flush();

            return $contactController->sendMail(
                [new Address($mail, $competiteur->getNom() . ' ' . $competiteur->getPrenom())],
                true,
                'Kompo - Réinitialisation de votre mot de passe',
                $data,
                $str_replacers);
        }
    }

    /**
     * @Route("/login/forgotten-password", name="login.forgotten.password")
     * @return Response
     */
    public function forgottenPassword(): Response
    {
        if ($this->getUser() != null) return $this->redirectToRoute('index');
        else return $this->render('account/forgotten_password.html.twig');
    }

    /**
     * @Route("/login/reset-password/{token}", name="login.reset.password")
     * @param Request $request
     * @param string $token
     * @param UtilController $utilController
     * @return Response
     * @throws Exception
     */
    public function resetPassword(Request $request, UtilController $utilController, string $token): Response
    {
        if ($this->getUser() != null) return $this->redirectToRoute('index');
        else {
            $tokenDecoded = $utilController->decryptToken($token);
            $idCompetiteur = $tokenDecoded['idCompetiteur'];
            $competiteur = $this->competiteurRepository->find($idCompetiteur);
            $dateValid = $tokenDecoded['dateValidation'];

            /** On vérifie que le lien de réinitialisation du mot de passe soit toujours actif **/
            if ($dateValid <= (new DateTime())->getTimestamp()) {
                $competiteur->setIsPasswordResetting(false);
                $this->em->flush();
                $this->logger->error("RESET PASSWORD TEMPS IMPARTI : " . $idCompetiteur . ' - ' . $competiteur->getNom() . ' - ' . $competiteur->getPrenom());
                throw new Exception("Ce lien n'est plus actif", 500);
            } /** Si le mot de passe a déjà été changé et que l'user est toujours dans les délais */
            else if (!$competiteur->isPasswordResetting()) throw new Exception('Le mot de passe a déjà été changé', 500);

            /** Formulaire soumis **/
            if ($request->request->get('new_password') && $request->request->get('new_password_validate')) {
                if ($request->request->get('new_password') == $request->request->get('new_password_validate')) {
                    $competiteur
                        ->setIsPasswordResetting(false)
                        ->setPassword($this->encoder->encodePassword($competiteur, $request->get('new_password_validate')));
                    $this->em->flush();
                    $this->addFlash('success', 'Mot de passe modifié');
                    return $this->redirectToRoute('login');
                } else $this->addFlash('fail', 'Champs du nouveau mot de passe différents');
            }

            return $this->render('account/reset_password.html.twig', [
                'token' => $token
            ]);
        }
    }
}
