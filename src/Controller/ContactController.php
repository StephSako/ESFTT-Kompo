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
        $competiteurs = $this->competiteurRepository->findBy([], ['nom' => 'ASC', 'prenom' => 'ASC']);

        return $this->render('contact/index.html.twig', [
            'competiteurs' => $competiteurs,
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'journees' => $journees
        ]);
    }

    /**
     * @Route("/contact/message", name="contact.email")
     * @param Request $request
     * @return Response
     */
    public function contact(Request $request): Response
    {
        return $this->sendMail(
            new Address($request->request->get('mailReceiver'), $request->request->get('nomReceiver')),
            new Address($request->request->get('mailSender'), $this->getUser()->getNom() . ' ' . $this->getUser()->getPrenom()),
            boolval($request->request->get('importance')),
            $request->request->get('sujet'),
            $request->request->get('message'),
            'mail_templating/contact.html.twig');
    }

    /**
     * @Route("/login/contact/forgotten_password", name="contact.reset.password")
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
                new Address('esf.la.frette.tennis.de.table@gmail.com', 'Kompo - ESFTT'),
                new Address($mail, $nom),
                true,
                'Réinitialisation de votre mot de passe',
                base64_encode($encryption),
                'mail_templating/forgotten_password.html.twig');
        }
    }

    /**
     * @param Address $addressSender
     * @param Address $addressReceiver
     * @param bool $importance
     * @param string $sujet
     * @param string|null $message
     * @param string $template
     * @return Response
     */
    public function sendMail(Address $addressSender, Address $addressReceiver, bool $importance, string $sujet, ?string $message, string $template): Response
    {
        // maildev --web 1080 --smtp 1025 --hide-extensions STARTTLS
        $email = (new TemplatedEmail())
            ->from($addressSender)
            ->to($addressReceiver)
            ->priority($importance ? Email::PRIORITY_HIGHEST : Email::PRIORITY_NORMAL)
            ->subject($sujet)
            ->htmlTemplate($template)
            ->context(['message' => $message]);

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










    // TODO Alerter les joueurs de leur sélection depuis journee.index
    /*
     * @Route("/notifySelectedPlayers/{type}/{idCompo}", name="notify.selectedPlayers")
     * @param $type
     * @param $idCompo
     * @param ContactNotification $contactNotification
     * @param Request $request
     * @return Response
     */
    /*public function notifySelectedPlayersAction($type, $idCompo, ContactNotification $contactNotification, Request $request): Response
    {
        $titre = $request->request->get('titre');
        $message = $request->request->get('message');

        $compo = null;
        if ($type == 'departementale') {
            $compo = $this->rencontreDepartementaleRepository->find($idCompo);
            $json = json_encode(['message' => $contactNotification->notify((new Contact())->setTitre($titre)->setMessage($message)->setCompetiteurs($compo->getListSelectedPlayers()), $this->getUser())]);
        }
        else if ($type == 'paris') {
            $compo = $this->rencontreParisRepository->find($idCompo);
            $contactNotification->notify((new Contact())->setTitre($titre)->setMessage($message)->setCompetiteurs($compo->getListSelectedPlayers()), $this->getUser()->getIdCompetiteur());
            $json = json_encode(['message' => $contactNotification->notify((new Contact())->setTitre($titre)->setMessage($message)->setCompetiteurs($compo->getListSelectedPlayers()), $this->getUser())]);
        }
        else $json = json_encode(['message' => 'Championnat inexistant ...']);

        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }*/

    /*
     * @param Contact $contact
     * @param Competiteur $redacteur
     * @return string
     */
    /*public function notify(Contact $contact, Competiteur $redacteur) {

        if ($redacteur->isContactableMail() && $redacteur->getMail() && $redacteur->getMail() != "") $from = new Address($redacteur->getMail(), $redacteur->getNom());
        else if($redacteur->isContactableMail2() && $redacteur->getMail2() && $redacteur->getMail2() != "") $from = new Address($redacteur->getMail2(), $redacteur->getNom());
        else $from = new Address('stephen.sakovitch@orange.fr', 'SAKOVITCH Stephen');

        $to = [];

        foreach ($contact->getCompetiteurs() as $player) {
            if ($player && $player->getIdCompetiteur() !== $redacteur->getIdCompetiteur()) {
                if ($player->isContactableMail() && $player->getMail() && $player->getMail() != "") array_push($to, new Address($player->getMail(), $player->getNom()));
                if ($player->isContactableMail2() && $player->getMail2() && $player->getMail2() != "") array_push($to, new Address($player->getMail2(), $player->getNom() . '_2'));
            }
        }
        if (empty($to)) return 'Le mail n\'a pas été envoyé car il n\'y a que vous dans l\'équipe';

        // maildev --web 1080 --smtp 1025 --hide-extensions STARTTLS
        $email = (new TemplatedEmail())
            ->from($from)
            ->cc(...$to)
            ->priority(Email::PRIORITY_HIGH)
            ->subject($contact->getTitre())
            ->htmlTemplate('mail_templating/contact.html.twig')
            ->context([
                'title' => $contact->getTitre(),
                'message' => $contact->getMessage()
            ]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            return 'Le mail n\'a pas pu être envoyé';
        }

        return 'Les joueurs sont prévenus !';
    }*/
}
