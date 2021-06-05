<?php

namespace App\Controller;

use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\JourneeRepository;
use Doctrine\ORM\EntityManagerInterface;
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
use Twig\Environment;

class ContactController extends AbstractController
{
    private $journeeRepository;
    private $competiteurRepository;
    private $championnatRepository;
    private $mailer;
    private $environment;
    private $em;

    /**
     * ContactController constructor.
     * @param JourneeRepository $journeeRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param EntityManagerInterface $em
     * @param ChampionnatRepository $championnatRepository
     * @param MailerInterface $mailer
     * @param Environment $environment
     */
    public function __construct(JourneeRepository $journeeRepository,
                                CompetiteurRepository $competiteurRepository,
                                EntityManagerInterface $em,
                                ChampionnatRepository $championnatRepository,
                                MailerInterface $mailer,
                                Environment $environment)
    {
        $this->em = $em;
        $this->journeeRepository = $journeeRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->mailer = $mailer;
        $this->environment = $environment;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/contact", name="contact")
     * @throws Exception
     */
    public function index(): Response
    {
        if ($this->get('session')->get('type')){
            if (!($championnat = $this->championnatRepository->find($this->get('session')->get('type')))) throw new Exception('Ce championnat est inexistant', 500);
        } else $championnat = $this->championnatRepository->getFirstChampionnatAvailable();

        $journees = $this->journeeRepository->findAllDates($championnat->getIdChampionnat());
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
        $addressReceiver = new Address($request->request->get('mailReceiver'), $request->request->get('nomReceiver'));
        $addressSender = new Address($request->request->get('mailSender'), $this->getUser()->getNom() . ' ' . $this->getUser()->getPrenom());
        $sujet = $request->request->get('sujet');
        $message = $request->request->get('message');
        $importance = $request->request->get('importance');

        // maildev --web 1080 --smtp 1025 --hide-extensions STARTTLS
        $email = (new TemplatedEmail())
            ->from($addressSender)
            ->to($addressReceiver)
            ->priority(boolval($importance) ? Email::PRIORITY_HIGH : Email::PRIORITY_NORMAL)
            ->subject($sujet)
            ->htmlTemplate('macros/email.html.twig')
            ->context([
                'message' => $message
            ]);

        try {
            $this->mailer->send($email);
            $json = json_encode(['message' => 'Votre mail a été envoyé !']);
        } catch (TransportExceptionInterface $e) {
            $json = json_encode(['message' => 'Le mail n\'a pas pu être envoyé !']);
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
            ->htmlTemplate('macros/email.html.twig')
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
