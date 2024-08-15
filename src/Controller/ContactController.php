<?php

namespace App\Controller;

use App\Entity\Competiteur;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    private $disponibiliteRepository;

    /**
     * ContactController constructor.
     * @param CompetiteurRepository $competiteurRepository
     * @param ChampionnatRepository $championnatRepository
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param MailerInterface $mailer
     */
    public function __construct(CompetiteurRepository   $competiteurRepository,
                                ChampionnatRepository   $championnatRepository,
                                DisponibiliteRepository $disponibiliteRepository,
                                MailerInterface         $mailer)
    {
        $this->competiteurRepository = $competiteurRepository;
        $this->championnatRepository = $championnatRepository;
        $this->mailer = $mailer;
        $this->disponibiliteRepository = $disponibiliteRepository;
    }

    /**
     * @Route("/contacter", name="contact")
     * @param Request $request
     * @param UtilController $utilController
     * @return Response
     */
    public function index(Request $request, UtilController $utilController): Response
    {
        $checkIsBackOffice = $utilController->keepBackOfficeNavbar('contact', [], $request->query->get('backoffice'));
        if ($checkIsBackOffice['issue']) return $checkIsBackOffice['redirect'];
        else $isBackoffice = $request->query->get('backoffice') == 'true';

        $allChampionnats = $championnat = $disposJoueurFormatted = $journees = null;
        if (!$isBackoffice) {
            $nextJourneeToPlayAllChampsIdChamp = $utilController->nextJourneeToPlayAllChamps()->getIdChampionnat();
            if (!$this->get('session')->get('type')) $championnat = $nextJourneeToPlayAllChampsIdChamp;
            else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $nextJourneeToPlayAllChampsIdChamp);

            // Disponibilités du joueur
            $id = $championnat->getIdChampionnat();
            $disposJoueur = $this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idChampionnat' => $id]);
            if ($this->getUser()->isCompetiteur()) {
                $disposJoueurFormatted = [];
                foreach ($disposJoueur as $dispo) {
                    $disposJoueurFormatted[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
                }
            }

            $journees = $utilController->getJourneesNavbar($championnat);
            $allChampionnats = $this->championnatRepository->getAllChampionnats();
        }

        $competiteurs = $this->competiteurRepository->findBy(['isArchive' => false], ['nom' => 'ASC', 'prenom' => 'ASC',]);

        $idRedacteur = $this->getUser()->getIdCompetiteur();
        $allPlayersButMe = $this->competiteurRepository->findJoueursByRole(null, $idRedacteur);
        $categories['tous'] = ['joueurs' => $this->returnPlayersContactByMedia($allPlayersButMe), 'titleItem' => 'Tous', 'titleModale' => 'Tout le monde'];
        $categories['loisirs'] = ['joueurs' => $this->returnPlayersContactByMedia(array_filter($allPlayersButMe, function (Competiteur $j) {
            return $j->isLoisir();
        })), 'titleItem' => 'Loisirs', 'titleModale' => 'Les loisirs'];
        $categories['jeunes'] = ['joueurs' => $this->returnPlayersContactByMedia($this->competiteurRepository->findJoueursByRole('Jeune', $idRedacteur)), 'titleItem' => 'Jeunes', 'titleModale' => 'Les jeunes'];
        $categories['competiteurs'] = ['joueurs' => $this->returnPlayersContactByMedia(array_filter($allPlayersButMe, function (Competiteur $j) {
            return $j->isCompetiteur();
        })), 'titleItem' => 'Compétiteurs', 'titleModale' => 'Les compétiteurs'];
        $categories['crit_fed'] = ['joueurs' => $this->returnPlayersContactByMedia(array_filter($allPlayersButMe, function (Competiteur $j) {
            return $j->isCritFed();
        })), 'titleItem' => 'Critérium fédéral', 'titleModale' => 'Les compétiteurs du critérium fédéral'];
        $categories['capitaines'] = ['joueurs' => $this->returnPlayersContactByMedia(array_filter($allPlayersButMe, function (Competiteur $j) {
            return $j->isCapitaine();
        })), 'titleItem' => 'Capitaines', 'titleModale' => 'Les capitaines'];
        $categories['entraineurs'] = ['joueurs' => $this->returnPlayersContactByMedia(array_filter($allPlayersButMe, function (Competiteur $j) {
            return $j->isEntraineur();
        })), 'titleItem' => 'Entraîneurs', 'titleModale' => 'Les entraîneurs'];
        $categories['administrateurs'] = ['joueurs' => $this->returnPlayersContactByMedia($this->competiteurRepository->findJoueursByRole('Admin', $idRedacteur)), 'titleItem' => 'Administrateurs', 'titleModale' => 'Les administrateurs'];
        $categories['custom'] = ['joueurs' => $this->returnPlayersContactByPlayer($allPlayersButMe), 'titleItem' => 'Personnalisée', 'titleModale' => 'Message personnalisé', 'isCustom' => true];

        return $this->render('contact/index.html.twig', [
            'competiteurs' => $competiteurs,
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'disposJoueur' => $disposJoueurFormatted,
            'journees' => $journees,
            'categories' => $categories,
            'isBackOffice' => $isBackoffice
        ]);
    }

    /**
     * Formatte les joueurs contactables par média
     * @param array $joueurs
     * @return array
     */
    public function returnPlayersContactByMedia(array $joueurs): array
    {
        $mails = [];
        $contactablesMails = [];
        $notContactablesMails = [];
        foreach ($joueurs as $joueur) {
            if ($joueur->getFirstContactableMail()) {
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
            if ($joueur->getFirstContactablePhoneNumber()) {
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
     * Formatte les joueurs contactables par média
     * @param array $joueurs
     * @return array
     */
    public function returnPlayersContactByPlayer(array $joueurs): array
    {
        $joueursByPlayer = [];
        foreach ($joueurs as $joueur) {
            $joueursByPlayer[$joueur->getIdCompetiteur()]['joueur'] = $joueur;
            $joueursByPlayer[$joueur->getIdCompetiteur()]['mail'] = $joueur->getFirstContactableMail();
            $joueursByPlayer[$joueur->getIdCompetiteur()]['sms'] = $joueur->getFirstContactablePhoneNumber();
        }
        return $joueursByPlayer;
    }

    /**
     * @Route("/contacter/custom-infos-contact", name="contact.custom.infos-contact")
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactsInfosFromCustomMessage(Request $request): JsonResponse
    {
        return new JsonResponse($this->render('ajax/modalContactCustomContacts.html.twig', [
            'infosCustomContacts' => $this->returnPlayersContactByMedia($this->competiteurRepository->findBy(['idCompetiteur' => $request->query->get('contactIDs')]))
        ])->getContent());
    }

    /**
     * @param Address[] $addressReceiver
     * @param bool $importance
     * @param string $sujet
     * @param string $htmlContent
     * @param array|null $str_replacers
     * @param bool|null $isPrivate
     * @param array|null $addressCopy
     * @return Response
     */
    public function sendMail(array $addressReceiver, bool $importance, string $sujet, string $htmlContent, ?array $str_replacers, ?bool $isPrivate = false, ?array $addressCopy = null): Response
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

        if ($addressCopy) $email->cc(...$addressCopy);

        try {
            $this->mailer->send($email);
            $json = json_encode(['message' => "L'e-mail a été envoyé", 'success' => true]);
        } catch (TransportExceptionInterface $e) {
            $json = json_encode(['message' => "L'e-mail n'a pas pu être envoyé", 'success' => false, 'error' => $e->getMessage()]);
        }

        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
