<?php
namespace App\Notification;

use App\Entity\Contact;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
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
     * @param int $idCapitaine
     * @throws TransportExceptionInterface
     */
    public function notify(Contact $contact, int $idCapitaine) {
        $to = [];

        foreach ($contact->getCompetiteurs() as $player) {
            if ($player && $player->getIdCompetiteur() !== $idCapitaine) {
                if ($player->isContactableMail()) array_push($to, new Address($player->getMail(), $player->getNom()));
                if ($player->isContactableMail2()) array_push($to, new Address($player->getMail2(), $player->getNom() . '_2'));
            }
        }

        $email = (new TemplatedEmail())
            ->from(new Address('stephen.sakovitch@orange.fr', 'Stephen'))
            ->to(...$to)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($contact->getTitre())
            ->text($contact->getMessage())
            ->htmlTemplate('macros/email.html.twig');
        $this->mailer->send($email);
    }

}