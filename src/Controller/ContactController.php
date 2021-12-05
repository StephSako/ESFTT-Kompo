<?php

namespace App\Controller;

use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use DateInterval;
use DateTime;
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
    private $mailer;

    /**
     * ContactController constructor.
     * @param CompetiteurRepository $competiteurRepository
     * @param ChampionnatRepository $championnatRepository
     * @param MailerInterface $mailer
     */
    public function __construct(CompetiteurRepository $competiteurRepository,
                                ChampionnatRepository $championnatRepository,
                                MailerInterface $mailer)
    {
        $this->competiteurRepository = $competiteurRepository;
        $this->mailer = $mailer;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/contact", name="contact")
     * @throws Exception
     */
    public function index(): Response
    {
        if (!$this->get('session')->get('type')) $championnat = $this->championnatRepository->getFirstChampionnatAvailable();
        else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $this->championnatRepository->getFirstChampionnatAvailable());

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

            $token = json_encode(
                [
                    'username' => $request->request->get('username'),
                    'dateValidation' => (new DateTime())->add(new DateInterval('PT2H'))->getTimestamp()
                ]);
            $encryption_iv = hex2bin($this->getParameter('encryption_iv'));
            $encryption_key = openssl_digest(php_uname(), 'MD5', TRUE);
            $encryption = openssl_encrypt($token, "BF-CBC", $encryption_key, 0, $encryption_iv);

            return $this->sendMail(
                new Address($mail, $nom),
                true,
                'Réinitialisation de votre mot de passe',
                base64_encode($encryption),
                'mail_templating/forgotten_password.html.twig',
                []);
        }
    }

    /**
     * @param Address $addressReceiver
     * @param bool $importance
     * @param string $sujet
     * @param string|null $message
     * @param string $template
     * @param array $options
     * @return Response
     */
    public function sendMail(Address $addressReceiver, bool $importance, string $sujet, ?string $message, string $template, array $options): Response
    {
        // maildev --web 1080 --smtp 1025 --hide-extensions STARTTLS
        $email = (new TemplatedEmail())
            ->from(new Address($this->getParameter('club_email'), 'Kompo - ' . $this->getParameter('club_diminutif')))
            ->to($addressReceiver)
            ->priority($importance ? Email::PRIORITY_HIGHEST : Email::PRIORITY_NORMAL)
            ->subject($sujet)
            ->htmlTemplate($template)
            ->context(['message' => $message, 'options' => $options]);

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
