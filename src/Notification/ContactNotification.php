<?php
namespace App\Notification;

use App\Entity\Competiteur;
use App\Entity\Contact;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\TexterInterface;
use Twig\Environment;

class ContactNotification {

    /**
     * @var MailerInterface
     */
    private $mailer;
    /**
     * @var Environment
     */
    private $environment;

    public function __construct(MailerInterface $mailer, Environment $environment)
    {
        $this->mailer = $mailer;
        $this->environment = $environment;
    }

    /**
     * @param Contact $contact
     * @param Competiteur $capitaine
     * @return string
     */
    public function notify(Contact $contact, Competiteur $capitaine) {

        if ($capitaine->isContactableMail() && $capitaine->getMail() && $capitaine->getMail() != "") $from = new Address($capitaine->getMail(), $capitaine->getNom());
        else if($capitaine->isContactableMail2() && $capitaine->getMail2() && $capitaine->getMail2() != "") $from = new Address($capitaine->getMail2(), $capitaine->getNom());
        else $from = new Address('stephen.sakovitch@orange.fr', 'SAKOVITCH Stephen');

        $to = [];

        foreach ($contact->getCompetiteurs() as $player) {
            if ($player && $player->getIdCompetiteur() !== $capitaine->getIdCompetiteur()) {
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
    }

    /**
     * @param TexterInterface $texter
     * @throws \Symfony\Component\Notifier\Exception\TransportExceptionInterface
     */
    /*public function loginSuccess(TexterInterface $texter)
    {
        $sms = new SmsMessage('+33687697121', 'A new message have been sent!');
        $texter->send($sms);
    }*/

}