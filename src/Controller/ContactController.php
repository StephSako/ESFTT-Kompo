<?php

namespace App\Controller;

use App\Repository\CompetiteurRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\JourneeParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ContactController extends AbstractController
{
    private $journeeParisRepository;
    private $journeeDepartementaleRepository;
    private $competiteurRepository;
    private $mailer;
    private $environment;

    /**
     * ContactController constructor.
     * @param JourneeDepartementaleRepository $journeeParisRepository
     * @param JourneeParisRepository $journeeDepartementaleRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param EntityManagerInterface $em
     * @param MailerInterface $mailer
     * @param Environment $environment
     */
    public function __construct(JourneeDepartementaleRepository $journeeParisRepository,
                                JourneeParisRepository $journeeDepartementaleRepository,
                                CompetiteurRepository $competiteurRepository,
                                EntityManagerInterface $em,
                                MailerInterface $mailer,
                                Environment $environment)
    {
        $this->em = $em;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->competiteurRepository = $competiteurRepository;
    }

    /**
     * @Route("/contact", name="contact")
     * @throws Exception
     */
    public function index(): Response
    {
        $type = ($this->get('session')->get('type') != null ? $this->get('session')->get('type') : 'departementale');
        if ($type == 'departementale') $journees = $this->journeeDepartementaleRepository->findAll();
        else if ($type == 'paris') $journees = $this->journeeParisRepository->findAll();
        else throw new Exception('Ce championnat est inexistant', 500);

        $competiteurs = $this->competiteurRepository->findBy([], ['nom' => 'ASC', 'prenom' => 'ASC']);

        return $this->render('contact/index.html.twig', [
            'competiteurs' => $competiteurs,
            'journees' => $journees
        ]);
    }

    // TODO
    /**
     * @Route("/contact/{sujet}/{message}", name="contact.player.email")
     * @param string $sujet
     * @param string $message
     * @return string
     */
    /*public function contact(string $sujet, string $message) {

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
