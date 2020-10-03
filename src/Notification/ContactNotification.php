<?php
namespace App\Notification;

use App\Entity\Contact;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
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

        // maildev --web 1080 --smtp 1025 --hide-extensions STARTTLS
        $email = (new TemplatedEmail())
            ->from(new Address('stephen.sakovitch@orange.fr', 'Stephen'))
            ->to(...$to)
            ->priority(Email::PRIORITY_HIGH)
            ->subject($contact->getTitre())
            ->htmlTemplate('macros/email.html.twig')
            ->context([
                'title' => $contact->getTitre(),
                'message' => $contact->getMessage()
            ]);

        $this->mailer->send($email);
    }

}