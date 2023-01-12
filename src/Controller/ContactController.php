<?php

namespace App\Controller;

use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteRepository;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
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
    private $disponibiliteRepository;
    private $utilController;

    /**
     * ContactController constructor.
     * @param CompetiteurRepository $competiteurRepository
     * @param ChampionnatRepository $championnatRepository
     * @param DisponibiliteRepository $disponibiliteRepository
     * @param UtilController $utilController
     * @param MailerInterface $mailer
     */
    public function __construct(CompetiteurRepository $competiteurRepository,
                                ChampionnatRepository $championnatRepository,
                                DisponibiliteRepository $disponibiliteRepository,
                                UtilController $utilController,
                                MailerInterface $mailer)
    {
        $this->competiteurRepository = $competiteurRepository;
        $this->championnatRepository = $championnatRepository;
        $this->mailer = $mailer;
        $this->disponibiliteRepository = $disponibiliteRepository;
        $this->utilController = $utilController;
    }

    /**
     * @Route("/contact", name="contact")
     * @return Response
     */
    public function index(): Response
    {
        if (!$this->get('session')->get('type')) $championnat = $this->utilController->nextJourneeToPlayAllChamps()->getIdChampionnat();
        else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $this->utilController->nextJourneeToPlayAllChamps()->getIdChampionnat());

        $journees = ($championnat ? $championnat->getJournees()->toArray() : []);
        $allChampionnats = $this->championnatRepository->findAll();
        $competiteurs = $this->competiteurRepository->findBy(['isArchive' => false], ['nom' => 'ASC', 'prenom' => 'ASC',]);

        // Disponibilités du joueur
        $id = $championnat->getIdChampionnat();
        $disposJoueur = $this->disponibiliteRepository->findBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idChampionnat' => $id]);
        $disposJoueurFormatted = null;
        if ($this->getUser()->isCompetiteur()) {
            $disposJoueurFormatted = [];
            foreach($disposJoueur as $dispo) {
                $disposJoueurFormatted[$dispo->getIdJournee()->getIdJournee()] = $dispo->getDisponibilite();
            }
        }

        $idRedacteur = $this->getUser()->getIdCompetiteur();
        $categories['tous'] = ['joueurs' => $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole(null, $idRedacteur)), 'titleItem' => 'Tous', 'titleModale' => 'Tout le monde'];
        $categories['loisirs'] = ['joueurs' => $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole('Loisir', $idRedacteur)), 'titleItem' => 'Loisirs', 'titleModale' => 'Les loisirs'];
        $categories['competiteurs'] = ['joueurs' => $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole('Competiteur', $idRedacteur)), 'titleItem' => 'Compétiteurs', 'titleModale' => 'Les compétiteurs'];
        $categories['crit_fed'] = ['joueurs' => $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole('CritFed', $idRedacteur)), 'titleItem' => 'Critérium fédéral', 'titleModale' => 'Les compétiteurs du critérium fédéral'];
        $categories['capitaines'] = ['joueurs' => $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole('Capitaine', $idRedacteur)), 'titleItem' => 'Capitaines', 'titleModale' => 'Les capitaines'];
        $categories['entraineurs'] = ['joueurs' => $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole('Entraineur', $idRedacteur)), 'titleItem' => 'Entraîneurs', 'titleModale' => 'Les entraîneurs'];
        $categories['administrateurs'] = ['joueurs' => $this->returnPlayersContact($this->competiteurRepository->findJoueursByRole('Admin', $idRedacteur)), 'titleItem' => 'Administrateurs', 'titleModale' => 'Les administrateurs'];

        return $this->render('contact/index.html.twig', [
            'competiteurs' => $competiteurs,
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'disposJoueur' => $disposJoueurFormatted,
            'journees' => $journees,
            'categories' => $categories
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
            $json = json_encode(['message' => 'L\'e-mail a été envoyé !', 'success' => true]);
        } catch (TransportExceptionInterface $e) {
            $json = json_encode(['message' => 'L\'emai n\'a pas pu être envoyé !', 'success' => false, 'error' => $e->getMessage()]);
        } catch (Exception $e) {
            $json = json_encode(['message' => 'L\'emai n\'a pas pu être envoyé !', 'success' => false, 'error' => $e->getMessage()]);
        }

        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
