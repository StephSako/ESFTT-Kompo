<?php

namespace App\Controller;

use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\SettingsRepository;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    private $competiteurRepository;
    private $championnatRepository;
    private $settingsRepository;
    private $mailer;
    private $utilController;

    /**
     * ContactController constructor.
     * @param CompetiteurRepository $competiteurRepository
     * @param ChampionnatRepository $championnatRepository
     * @param MailerInterface $mailer
     * @param UtilController $utilController
     * @param SettingsRepository $settingsRepository
     */
    public function __construct(CompetiteurRepository $competiteurRepository,
                                ChampionnatRepository $championnatRepository,
                                MailerInterface $mailer,
                                UtilController $utilController,
                                SettingsRepository $settingsRepository)
    {
        $this->competiteurRepository = $competiteurRepository;
        $this->championnatRepository = $championnatRepository;
        $this->settingsRepository = $settingsRepository;
        $this->mailer = $mailer;
        $this->utilController = $utilController;
    }

    /**
     * @Route("/contact", name="contact")
     * @throws Exception
     */
    public function index(): Response
    {
        if (!$this->get('session')->get('type')) $championnat = $this->utilController->nextJourneeToPlayAllChamps()->getIdChampionnat();
        else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $this->utilController->nextJourneeToPlayAllChamps()->getIdChampionnat());

        $journees = ($championnat ? $championnat->getJournees()->toArray() : []);
        $allChampionnats = $this->championnatRepository->findAll();
        $competiteurs = $this->competiteurRepository->findBy(['isArchive' => false], ['nom' => 'ASC', 'prenom' => 'ASC',]);

        $idRedacteur = $this->getUser()->getIdCompetiteur();
        $joueurs = [];
        $joueurs['tous'] = $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole(null, $idRedacteur));
        $joueurs['loisirs'] = $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole('Loisir', $idRedacteur));
        $joueurs['competiteurs'] = $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole('Competiteur', $idRedacteur));
        $joueurs['crit_fed'] = $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole('CritFed', $idRedacteur));
        $joueurs['capitaines'] = $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole('Capitaine', $idRedacteur));
        $joueurs['entraineurs'] = $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole('Entraineur', $idRedacteur));
        $joueurs['administrateurs'] = $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole('Admin', $idRedacteur));

        return $this->render('contact/index.html.twig', [
            'competiteurs' => $competiteurs,
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'journees' => $journees,
            'joueurs' => $joueurs
        ]);
    }

    /**
     * Formatte les joueurs contactables par rôle
     * @param array $joueurs
     * @return array
     */
    public function returnPlayersContact(array $joueurs): array
    {
        $mails = [];
        $contactablesMails = [];
        $notContactablesMails = [];
        foreach ($joueurs as $joueur) {
            if ($joueur->getFirstContactableMail()){
                $contactablesMails[] = $joueur;
                $mails[] = $joueur->getFirstContactableMail();
            } else $notContactablesMails[] = $joueur;
        }
        $response['mail']['toString'] = implode(',', $mails);
        $response['mail']['contactables'] = $contactablesMails;
        $response['mail']['notContactables'] = $notContactablesMails;

        $phoneNumbers = [];
        $contactablesPhoneNumbers = [];
        $notContactablesPhoneNumbers = [];
        foreach ($joueurs as $joueur) {
            if ($joueur->getFirstContactablePhoneNumber()){
                $contactablesPhoneNumbers[] = $joueur;
                $phoneNumbers[] = $joueur->getFirstContactablePhoneNumber();
            } else $notContactablesPhoneNumbers[] = $joueur;
        }
        $response['sms']['toString'] = implode(',', $phoneNumbers);
        $response['sms']['contactables'] = $contactablesPhoneNumbers;
        $response['sms']['notContactables'] = $notContactablesPhoneNumbers;

        return $response;
    }

    /**
     * @Route("/login/contact/forgotten_password", name="contact.reset.password", methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function contactResetPassword(Request $request): Response
    {
        if ($this->getUser() != null) return $this->redirectToRoute('index');
        else {
            $mail = $request->request->get('mail');
            $username = $request->request->get('username');
            $nom = $this->competiteurRepository->findJoueurResetPassword($username, $mail);

            if (!$nom){
                $response = new Response(json_encode(['message' => 'Ce pseudo et ce mail ne sont pas associés', 'success' => false]));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $settings = $this->settingsRepository->find(1);
            try {
                $data = $settings->getInfosType('mail-mdp-oublie');
            } catch (Exception $e) {
                throw $this->createNotFoundException($e->getMessage());
            }

            $resetPasswordLink = $this->utilController->generateGeneratePasswordLink($request->request->get('username'), 'PT' . $this->getParameter('time_reset_password_hour'). 'H');
            $str_replacers = [
                'old' => ['[#lien_reset_password#]', '[#time_reset_password_hour#]'],
                'new' => ["ce <a href=\"$resetPasswordLink\">lien</a>", $this->getParameter('time_reset_password_hour')]
            ];

            return $this->sendMail(
                [new Address($mail, $nom)],
                true,
                'Kompo - Réinitialisation de votre mot de passe',
                $data,
                $str_replacers);
        }
    }

    /**
     * @param Address[] $addressReceiver
     * @param bool $importance
     * @param string $sujet
     * @param string $htmlContent
     * @param array|null $str_replacers
     * @param bool|null $isPrivate
     * @return Response
     */
    public function sendMail(array $addressReceiver, bool $importance, string $sujet, string $htmlContent, ?array $str_replacers, ?bool $isPrivate = false): Response
    {
        // maildev --web 1080 --smtp 1025 --hide-extensions STARTTLS
        if ($str_replacers) $htmlContent = str_replace($str_replacers['old'], $str_replacers['new'], $htmlContent);
        $email = (new TemplatedEmail())
            ->from(new Address($this->getParameter('club_email'), 'Kompo - ' . $this->getParameter('club_diminutif')))
            ->priority($importance ? Email::PRIORITY_HIGHEST : Email::PRIORITY_NORMAL)
            ->subject($sujet)
            ->html($htmlContent);

        if ($isPrivate) $email->bcc(...$addressReceiver);
        else $email->to(...$addressReceiver);

        try {
            $this->mailer->send($email);
            $json = json_encode(['message' => 'Le mail a été envoyé !', 'success' => true]);
        } catch (TransportExceptionInterface $e) {
            $json = json_encode(['message' => 'Le mail n\'a pas pu être envoyé !', 'success' => false]);
        }

        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
