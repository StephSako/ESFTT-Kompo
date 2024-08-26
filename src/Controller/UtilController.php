<?php

namespace App\Controller;

use App\Entity\Championnat;
use App\Entity\Journee;
use App\Entity\Rencontre;
use App\Entity\Titularisation;
use App\Repository\ChampionnatRepository;
use App\Repository\RencontreRepository;
use DateInterval;
use DateTime;
use Exception;
use FFTTApi\Model\CalculatedUnvalidatedPartie;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Transliterator;

class UtilController extends AbstractController
{
    const MAPS_URI = "https://www.google.com/maps/dir/?api=1&travelmode=driving&destination=";
    const WAZE_URI = "https://waze.com/ul?navigate=yes&q=";
    const IFRAME_URI = "https://maps.google.com/maps?hl=fr&z=6&ie=UTF8&output=embed&q=";
    private $rencontreRepository;
    private $championnatRepository;
    private $logger;

    /**
     * @param RencontreRepository $rencontreRepository
     * @param LoggerInterface $logger
     * @param ChampionnatRepository $championnatRepository
     */
    public function __construct(RencontreRepository   $rencontreRepository,
                                LoggerInterface       $logger,
                                ChampionnatRepository $championnatRepository)
    {
        $this->rencontreRepository = $rencontreRepository;
        $this->championnatRepository = $championnatRepository;
        $this->logger = $logger;
    }

    /**
     * Formatte une adresse avec adresse, code postal et ville
     * @param string $address
     * @param string $postalCode
     * @param string $ville
     * @return string
     */
    public static function formatAddress(string $address, string $postalCode, string $ville): string
    {
        return $address .
            ($postalCode ? ($address ? ', ' : '') . $postalCode : '') .
            ($ville ? ' ' . $ville : '');
    }

    /**
     * @param int $nbPlayers
     * @param string $alias
     * @return string
     */
    public static function generateIdJoueurToX(int $nbPlayers, string $alias): string
    {
        return ' IN (' . implode(', ', array_map(function ($r) use ($alias) {
                return $alias . '.idJoueur' . $r;
            }, range(0, $nbPlayers - 1))) . ')';
    }

    /**
     * Retourne un message selon que le championnat est terminé ou pas pour autoriser la pré-phase
     * @param Championnat $championnat
     * @return array
     * @throws Exception
     */
    public function isPreRentreeLaunchable(Championnat $championnat): array
    {
        $latestDate = $this->getLastDates($championnat);
        $maxDate = clone max($latestDate);
        $latestDateMax = new DateTime(date_format(($maxDate->add(new DateInterval('P1D'))), 'Y-m-d'));
        $today = new DateTime();
        if (!count($latestDate)) return ['launchable' => false, 'message' => "Ce championnat n'a pas d'équipes enregistrées"];
        else if ($latestDateMax <= $today) return ['launchable' => true, 'message' => 'La phase est terminée et la pré-phase est prête à être lancée'];
        else return ['launchable' => false, 'message' => null];
    }

    /**
     * Retourne les dates au plus tard de toutes les recontres du championnat sélectionné
     * @param Championnat $championnat
     * @return array
     */
    public function getLastDates(Championnat $championnat): array
    {
        return array_unique(array_map(function (Rencontre $renc) {
            return max([$renc->isReporte() ? $renc->getDateReport() : null, $renc->getIdJournee()->getDateJournee()]);
        }, $championnat->getRencontres()->toArray()), SORT_REGULAR);
    }

    /**
     * Génère un token afin de modifier le mot de passe d'un utilisateur en passant l'username et le date changer (combien de temps
     * le token est valide) en paramètre
     * @param int $idCompetiteur
     * @param string $dateChanger
     * @return string
     * @throws Exception
     */
    public function generateGeneratePasswordLink(int $idCompetiteur, string $dateChanger): string
    {
        $token = json_encode(
            [
                'idCompetiteur' => $idCompetiteur,
                'dateValidation' => (new DateTime())->add(new DateInterval($dateChanger))->getTimestamp()
            ]);
        return $this->getParameter('url_prod') . '/login/reset-password/' . $this->encryptToken($token);
    }

    /**
     * @param string $token
     * @return string
     * @throws Exception
     */
    public function encryptToken(string $token): string
    {
        $key = hash('sha256', $this->getParameter('secret_phrase'));
        $iv = substr(hash('sha256', $key), 0, openssl_cipher_iv_length($this->getParameter('cipher')));
        $encrypted = urlencode(openssl_encrypt($token, $this->getParameter('cipher'), $key, 0, $iv));

        // On vérifie que le token est bien déchiffrable
        try {
            $this->decryptToken($encrypted);
            return $encrypted;
        } catch (Exception $e) {
            $this->logger->error("TOKEN DECRYPTION INVALIDE : " . $token . ' | ' . $encrypted);
            return 'token-encryption-failed';
        }
    }

    /**
     * @param string $token
     * @return array|null
     * @throws Exception
     */
    public function decryptToken(string $token): ?array
    {
        $key = hash('sha256', $this->getParameter('secret_phrase'));
        $iv = substr(hash('sha256', $key), 0, openssl_cipher_iv_length($this->getParameter('cipher')));
        $tokenDecoded = urldecode($token);
        $decryption = openssl_decrypt($tokenDecoded, $this->getParameter('cipher'), $key, 0, $iv);
        $tokenDecoded = json_decode($decryption, true);

        if ($tokenDecoded == null) {
            $this->logger->error("TOKEN DECRYPTION INVALID : " . $token);
            throw new Exception("Le token est invalide !", 500);
        }
        return $tokenDecoded;
    }

    /**
     * @param int $limiteBrulage
     * @param int $idChampionnat
     * @param int $idJoueur
     * @param int $nbJoueurs
     * @param int $idJournee
     */
    public function checkInvalidSelection(int $limiteBrulage, int $idChampionnat, int $idJoueur, int $nbJoueurs, int $idJournee)
    {
        $this->deleteInvalidSelectedPlayers($this->rencontreRepository->getSelectedWhenBurnt($idJoueur, $idJournee, $limiteBrulage, $nbJoueurs, $idChampionnat), $nbJoueurs, $idJoueur);
    }

    /**
     * @param array $invalidCompos
     * @param int $nbJoueurs
     * @param int $idCompetiteur
     */
    public function deleteInvalidSelectedPlayers(array $invalidCompos, int $nbJoueurs, int $idCompetiteur)
    {
        foreach ($invalidCompos as $compo) {
            $i = 0;
            while ($i != $nbJoueurs) {
                if ($compo['isPlayer' . $i] == $idCompetiteur) {
                    $compo['compo']->setIdJoueurN($i, null);
                    break;
                }
                $i++;
            }
        }
    }

    /**
     * Retourne la prochaine journée à jouer depuis tous les championnats
     * @param int|null $idChampionnat
     * @return Journee|null
     */
    public function nextJourneeToPlayAllChamps(?int $idChampionnat = null): ?Journee
    {
        $allChamps = $this->championnatRepository->findBy(
            $idChampionnat ? ['idChampionnat' => $idChampionnat] : [],
            ['nom' => 'ASC']
        );

        $nextJourneeByChampionnats = array_map(function ($c) {
            return $c->getNextJourneeToPlay();
        }, $allChamps);

        $nextJourneeByChampionnats = array_filter($nextJourneeByChampionnats, function ($cNJTP) {
            return $cNJTP;
        });

        /** Si toutes les dates de tous les championnats sont indéfinies */
        if (!$nextJourneeByChampionnats) {
            return $allChamps ? $allChamps[0]->getJournees()[0] : null;
        }

        usort($nextJourneeByChampionnats, function ($a, $b) use ($allChamps) {
            if (!$a && !$b) return $allChamps[0]->getJournees()[0]->getDateJournee()->getTimestamp();
            if (!$a) return $b->getDateJournee()->getTimestamp();
            if (!$b) return $a->getDateJournee()->getTimestamp();
            return $a->getDateJournee()->getTimestamp() - $b->getDateJournee()->getTimestamp();
        });
        return array_shift($nextJourneeByChampionnats);
    }

    /**
     * @param string $username
     * @param array $existingUsernames
     * @return string
     */
    public function getUniqueUsername(string $username, array $existingUsernames): string
    {
        $username = str_replace(' ', '', $username);
        $username = mb_convert_case($username, MB_CASE_LOWER, "UTF-8");
        $username = Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($username);
        $draftUsername = $username;
        $i = 1;
        if (in_array($username, $existingUsernames)) $draftUsername = $username . '_' . $i;

        while (in_array($draftUsername, $existingUsernames)) {
            $i++;
            $draftUsername = $username . '_' . $i;
        }
        return $draftUsername;
    }

    /**
     * @param string $prefixe
     * @return string
     */
    public function getAdminUpdateLog(string $prefixe): string
    {
        return $prefixe . $this->getUser()->getPrenom() . ' ' . $this->getUser()->getNom()[0] . '. le ' . date_format(new DateTime(), 'd/m/Y');
    }

    /**
     * Détermine si le navbar du back-office est conservée ou non
     * @param string $redirection
     * @param array $options
     * @param string|null $backOfficeParam
     * @return array
     */
    public function keepBackOfficeNavbar(string $redirection, array $options, ?string $backOfficeParam): array
    {
        return [
            'redirect' => $this->redirectToRoute($redirection, $options),
            'issue' => ($backOfficeParam && $backOfficeParam != 'true') || ($backOfficeParam == 'true' && !($this->getUser()->isCapitaine() || $this->getUser()->isAdmin()))
        ];
    }

    /**
     * Retourne les erreurs profondes d'un formulaire
     * @param FormInterface $form
     * @return string
     */
    public function getFormDeepErrors(FormInterface $form): string
    {
        $errors = '';
        foreach ($form->getErrors(true) as $i => $error) {
            $errors .= $error->getMessage() . (!$i ? '<br>' : '');
        }
        return strlen($errors) ? $errors : "Le formulaire n'est pas valide";
    }

    /**
     * Formatte les matches joués par le joueur par date
     * @param array $matches
     * @param string $licence
     * @return array
     */
    public function formatHistoMatches(string $licence, array $matches = []): array
    {
        $formattedMatches = [];
        $virtualPoints = floatval($this->get('session')->get('pointsMensuels' . $licence));

        /** @var CalculatedUnvalidatedPartie $match */
        foreach ($matches as $match) {
            $formattedMatches[$match->getDate()]['matches'][] = $match;

            if (!array_key_exists('epreuve', $formattedMatches[$match->getDate()])) $formattedMatches[$match->getDate()]['epreuve'] = $match->getEpreuve();
            if (!array_key_exists('coefficient', $formattedMatches[$match->getDate()])) $formattedMatches[$match->getDate()]['coefficient'] = $match->getCoefficient();
            if (!array_key_exists('startVirtualPoints', $formattedMatches[$match->getDate()])) $formattedMatches[$match->getDate()]['startVirtualPoints'] = $virtualPoints;

            $virtualPoints += $match->getPointsGagnes();
            $formattedMatches[$match->getDate()]['updatedVirtualPoints'] = $virtualPoints;

            if (!array_key_exists('totalPointsWon', $formattedMatches[$match->getDate()])) $formattedMatches[$match->getDate()]['totalPointsWon'] = $match->getPointsGagnes();
            else $formattedMatches[$match->getDate()]['totalPointsWon'] += $match->getPointsGagnes();
        }
        return array_reverse($formattedMatches);
    }

    /**
     * Retourne la liste des journées à afficher dans le navbar en fonction de la titularisation du joueur dans le championnat
     * @param Championnat $championnat
     * @return array
     */
    public function getJourneesNavbar(Championnat $championnat): array
    {
        $journees = $championnat->getJournees()->toArray();
        $titularisationActifChampionnat = current(array_filter($this->getUser()->getTitularisations()->toArray(), function (Titularisation $titularisation) use ($championnat) {
            return $titularisation->getIdChampionnat()->getIdChampionnat() == $championnat->getIdChampionnat();
        }));
        if ($titularisationActifChampionnat) {
            $datesRencontresEquipeTitulaire = $titularisationActifChampionnat->getIdEquipe()->getRencontres()->toArray();
            foreach ($journees as $i => $journee) {
                $journee->setDateJournee($datesRencontresEquipeTitulaire[$i]->getDateReport());
            }
        }
        return $journees;
    }
}
