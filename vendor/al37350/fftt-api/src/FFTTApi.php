<?php
/**
 * Created by Antoine Lamirault.
 */

namespace FFTTApi;

use Accentuation\Accentuation;
use FFTTApi\Exception\ClubNotFoundException;
use FFTTApi\Exception\InvalidLienRencontre;
use FFTTApi\Exception\JoueurNotFound;
use FFTTApi\Model\Actualite;
use FFTTApi\Model\CalculatedUnvalidatedPartie;
use FFTTApi\Model\Classement;
use FFTTApi\Model\ClubDetails;
use FFTTApi\Model\Equipe;
use FFTTApi\Model\EquipePoule;
use FFTTApi\Model\Historique;
use FFTTApi\Model\Joueur;
use FFTTApi\Model\JoueurDetails;
use FFTTApi\Model\Organisme;
use FFTTApi\Model\Partie;
use FFTTApi\Model\Club;
use FFTTApi\Model\VirtualPoints;
use FFTTApi\Model\UnvalidatedPartie;
use FFTTApi\Exception\InvalidCredidentials;
use FFTTApi\Exception\NoFFTTResponseException;
use FFTTApi\Model\Rencontre\Rencontre;
use FFTTApi\Model\Rencontre\RencontreDetails;
use FFTTApi\Service\ClubFactory;
use FFTTApi\Service\PointCalculator;
use FFTTApi\Service\RencontreDetailsFactory;
use FFTTApi\Service\Utils;
use GuzzleHttp\Exception\ClientException;
use DateTime;

class FFTTApi
{
    private $id;
    private $password;
    private $apiRequest;

    // Premier jour de Juillet comptabilisation de la saison
    const PREMIER_JOUR_SAISON = 9;
    /**
     * Dates de publication des matches (on part du principe qu'il n'y aura pas de matches officiels le 30 et 31 Décembre et que la publication aura lieu le 1er Janvier ...)
     * mois => jour
     **/
    const DATES_PUBLICATION = [1 => 1, 2 => 6, 3 => 4, 4 => 6, 5 => 4, 6 => 10, 7 => self::PREMIER_JOUR_SAISON, 10 => 5, 11 => 6];

    public function __construct(string $id, string $password)
    {
        $this->id = $id;
        $this->password = md5($password);
        $this->apiRequest = new ApiRequest($this->password, $this->id);
    }

    public function initialize()
    {
        $time = round(microtime(true) * 1000);
        $timeCrypted = hash_hmac("sha1", $time, $this->password);
        $uri = 'https://apiv2.fftt.com/mobile/pxml/xml_initialisation.php?serie=' . $this->id
            . '&tm=' . $time
            . '&tmc=' . $timeCrypted
            . '&id=' . $this->id;

        try{
            $response = $this->apiRequest->send($uri);
        }
        catch (ClientException $clientException){
            if($clientException->getResponse()->getStatusCode() === 401){
                throw new InvalidCredidentials();
            }
            throw $clientException;
        }

        return $response;
    }

    /**
     * @param string $type
     * F = Fédération
     * Z = Zone
     * L = Ligue
     * D = Département
     * @return Organisme[]
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws NoFFTTResponseException
     */
    public function getOrganismes(string $type = "Z"): array
    {
        if (!in_array($type, ['F', 'Z', 'L', 'D'])) {
            $type = 'Z';
        }

        $organismes = $this->apiRequest->get('xml_organisme', [
            'type' => $type,
        ])["organisme"];

        $result = [];
        foreach ($organismes as $organisme) {
            $result[] = new Organisme(
                $organisme["libelle"],
                $organisme["id"],
                $organisme["code"],
                $organisme["idPere"]
            );
        }

        return $result;
    }

    /**
     * @param int $departementId
     * @return Club[]
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws NoFFTTResponseException
     */
    public function getClubsByDepartement(int $departementId): array
    {

        $data = $this->apiRequest->get('xml_club_dep2', [
            'dep' => $departementId,
        ])['club'];

        $clubFactory = new ClubFactory();
        return $clubFactory->createFromArray($data);
    }

    /**
     * @param string $name
     * @return Club[]
     */
    public function getClubsByName(string $name)
    {
        try {
            $data = $this->apiRequest->get('xml_club_b', [
                'ville' => $name,
            ])['club'];

            $data = $this->wrappedArrayIfUnique($data);

            $clubFactory = new ClubFactory();
            return $clubFactory->createFromArray($data);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param string $clubId
     * @return ClubDetails
     * @throws ClubNotFoundException
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws NoFFTTResponseException
     */
    public function getClubDetails(string $clubId): ClubDetails
    {
        $clubData = $this->apiRequest->get('xml_club_detail', [
            'club' => $clubId,
        ])['club'];
        if (empty($clubData['numero'])) {
            throw new ClubNotFoundException($clubId);
        }
        return new ClubDetails(
            intval($clubData['numero']),
            $clubData['nom'],
            is_array($clubData['nomsalle']) ? null : $clubData['nomsalle'],
            is_array($clubData['adressesalle1']) ? null : $clubData['adressesalle1'],
            is_array($clubData['adressesalle2']) ? null : $clubData['adressesalle2'],
            is_array($clubData['adressesalle3']) ? null : $clubData['adressesalle3'],
            is_array($clubData['codepsalle']) ? null : $clubData['codepsalle'],
            is_array($clubData['villesalle']) ? null : $clubData['villesalle'],
            is_array($clubData['web']) ? null : $clubData['web'],
            is_array($clubData['nomcor']) ? null : $clubData['nomcor'],
            is_array($clubData['prenomcor']) ? null : $clubData['prenomcor'],
            is_array($clubData['mailcor']) ? null : $clubData['mailcor'],
            is_array($clubData['telcor']) ? null : $clubData['telcor'],
            is_array($clubData['latitude']) ? null : floatval($clubData['latitude']),
            is_array($clubData['longitude']) ? null : floatval($clubData['longitude'])
        );
    }

    /**
     * @param string $clubId
     * @return Joueur[]
     * @throws ClubNotFoundException
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     */
    public function getJoueursByClub(string $clubId): array
    {
        try {
            $arrayJoueurs = $this->apiRequest->get('xml_liste_joueur_o', [
                    'club' => $clubId,
                ]
            );
        } catch (NoFFTTResponseException $e) {
            throw new ClubNotFoundException($clubId);
        }

        $result = [];

        foreach ($arrayJoueurs['joueur'] ?? [] as $joueur) {
            $realJoueur = new Joueur(
                $joueur['licence'],
                $joueur['nclub'],
                $joueur['club'],
                Utils::removeSeparatorsDuplication(trim($joueur['nom'])),
                Utils::removeSeparatorsDuplication(trim($joueur['prenom'])),
                !is_array($joueur['points']) ? (int) $joueur['points'] : null,
                !is_array($joueur['echelon']) ? $joueur['echelon'] : null,
                !is_array($joueur['place']) ? (int) $joueur['place'] : null
            );
            $result[] = $realJoueur;
        }
        return $result;
    }

    /**
     * Appel de service de récupération de joueurs par nom et prénom
     * @param string $nom
     * @param string $prenom
     * @return array
     */
    public function getJoueursByNomXHR(string $nom, string $prenom = ""): array
    {
        return $this->apiRequest->get('xml_liste_joueur', [
                'nom' => addslashes(Accentuation::remove($nom)),
                'prenom' => addslashes(Accentuation::remove($prenom)),
            ]
        );
    }

    /**
     * @param string $nom
     * @param string $prenom
     * @return Joueur[]
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws NoFFTTResponseException
     */
    public function getJoueursByNom(string $nom, string $prenom = ""): array
    {
        $nom = addslashes(mb_convert_case(Accentuation::remove($nom), MB_CASE_UPPER, "UTF-8"));
        $prenom = addslashes(mb_convert_case(Accentuation::remove($prenom), MB_CASE_UPPER, "UTF-8"));

        $arrayJoueurs = $this->getJoueursByNomXHR($nom, $prenom);

        // Si le joueur est trouvé du 1er coup
        if (array_key_exists('joueur', $arrayJoueurs)) {
            $arrayJoueurs = $arrayJoueurs['joueur'];
        } else {
            // Sinon on affine en ne cherchant que la 1ère partie alphabétique du nom et du prénom
            preg_match('/^(?<nom>[A-ZÀ-Ý]*)/', $nom, $nomLettres);
            preg_match('/^(?<prenom>[A-ZÀ-Ý]*)/', $prenom, $prenomLettres);
            $arrayJoueurs = $this->getJoueursByNomXHR($nomLettres['nom'], $prenomLettres['prenom']);

            if (count($arrayJoueurs) === 0) {
                Utils::writeLog("Joueur [" . $nom . ' ' . $prenom . "] non trouvé après affinage");
                throw new JoueurNotFound($nom . ' ' . $prenom);
            } else {
                // S'il s'agit de plusieurs joueurs ...
                if (!array_key_exists('nom', $arrayJoueurs['joueur'])) $arrayJoueurs = $arrayJoueurs['joueur'];

                $arrayJoueurs = array_filter($arrayJoueurs, function ($joueur) use ($nom, $prenom) {
                    return Utils::removeSeparatorsDuplication(trim(mb_convert_case(Accentuation::remove($joueur['nom']), MB_CASE_UPPER, "UTF-8"))) === Utils::removeSeparatorsDuplication(trim($nom))
                        && Utils::removeSeparatorsDuplication(trim(mb_convert_case(Accentuation::remove($joueur['prenom']), MB_CASE_UPPER, "UTF-8"))) === Utils::removeSeparatorsDuplication(trim($prenom));
                });
            }
        }

        $arrayJoueurs = $this->wrappedArrayIfUnique($arrayJoueurs);

        $result = [];

        foreach ($arrayJoueurs as $joueur) {
            $realJoueur = new Joueur(
                $joueur['licence'],
                $joueur['nclub'],
                $joueur['club'],
                $joueur['nom'],
                $joueur['prenom'],
                $joueur['clast'] ? (int) $joueur['clast'] : null,
                null,
                null
            );
            $result[] = $realJoueur;
        }
        return $result;
    }

    /**
     * @param string $licenceId
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws JoueurNotFound
     */
    public function getJoueurDetailsByLicence(string $licenceId, ?string $clubId = null)
    {
        $options = [
            'licence' => $licenceId,
        ];

        if (null !== $clubId) {
            $options['club'] = $clubId;
        }

        try {
            $data = $this->apiRequest->get('xml_licence_b', $options);
        } catch (InternalServerErrorException $e) {
            if (null !== $clubId) {
                throw new ClubNotFoundException($clubId);
            }
            throw $e;
        }

        if (array_key_exists('licence', $data)) {
            $data = $data['licence'];
        } else {
            throw new JoueurNotFound($licenceId);
        }

        if (is_array(array_values($data)[0])) { // Une liste de joueurs est retournée si le paramètre "licence" est vide et que "club" est renseigné et existe
            $listeJoueurs = [];
            foreach ($data as $joueur) {
                $listeJoueurs[] = $this->returnJoueurDetails($joueur);
            }

            return $listeJoueurs;
        } else {
            return $this->returnJoueurDetails($data);
        }
    }

    /**
     * @param array $joueurDetails
     * @return JoueurDetails
     */
    private function returnJoueurDetails(array $joueurDetails): JoueurDetails
    {
        return new JoueurDetails(
            $joueurDetails['licence'],
            $joueurDetails['nom'],
            $joueurDetails['prenom'],
            $joueurDetails['numclub'],
            $joueurDetails['nomclub'],
            'M' === $joueurDetails['sexe'],
            $joueurDetails['cat'],
            $joueurDetails['initm'] ? (float) $joueurDetails['initm'] : null,
            (float) $joueurDetails['point'],
            $joueurDetails['pointm'] ? (float) $joueurDetails['pointm'] : (float) $joueurDetails['point'],
            $joueurDetails['apointm'] ? (float) $joueurDetails['apointm'] : null
        );
    }

    /**
     * @param string $licenceId
     * @return Classement
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws JoueurNotFound
     */
    public function getClassementJoueurByLicence(string $licenceId): Classement
    {
        try {
            $joueurDetails = $this->apiRequest->get('xml_joueur', [
                'licence' => $licenceId,
            ])['joueur'];
        } catch (NoFFTTResponseException $e) {
            throw new JoueurNotFound($licenceId);
        }

        $classement = new Classement(
            new \DateTime(),
            $joueurDetails['point'],
            $joueurDetails['apoint'],
            intval($joueurDetails['clast']),
            intval($joueurDetails['clnat']),
            intval($joueurDetails['rangreg']),
            intval($joueurDetails['rangdep']),
            intval($joueurDetails['valcla']),
            intval($joueurDetails['valinit'])
        );
        return $classement;
    }

    /**
     * @param string $licenceId
     * @return Historique[]
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws JoueurNotFound
     */
    public function getHistoriqueJoueurByLicence(string $licenceId): array
    {
        try {
            $classements = $this->apiRequest->get('xml_histo_classement', [
                'numlic' => $licenceId,
            ]);
            if (empty($classements)) return [];
            $classements = $classements['histo'];
        } catch (NoFFTTResponseException $e) {
            throw new JoueurNotFound($licenceId);
        }
        $result = [];
        $classements = $this->wrappedArrayIfUnique($classements);

        foreach ($classements as $classement) {
            $explode = explode(' ', $classement['saison']);

            $historique = new Historique($explode[1], $explode[3], intval($classement['phase']), intval($classement['point']));
            $result[] = $historique;
        }

        return $result;
    }

    /**
     * @param string $joueurId
     * @return Partie[]
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     */
    public function getPartiesJoueurByLicence(string $joueurId): array
    {

        try {
            $parties = $this->apiRequest->get('xml_partie_mysql', [
                'licence' => $joueurId,
            ]);
            $parties = array_key_exists('partie', $parties) ? $this->wrappedArrayIfUnique($parties['partie']) : [];
        } catch (NoFFTTResponseException $e) {
            $parties = [];
        }
        $res = [];

        foreach ($parties as $partie) {
            list($nom, $prenom) = Utils::returnNomPrenom($partie['advnompre']);
            $realPartie = new Partie(
                $partie["vd"] === "V" ? true : false,
                intval($partie['numjourn']),
                \DateTime::createFromFormat('d/m/Y', $partie['date']),
                floatval($partie['pointres']),
                floatval($partie['coefchamp']),
                $partie['advlic'],
                $partie['advsexe'] === 'M' ? true : false,
                $nom,
                $prenom,
                intval($partie['advclaof'])
            );
            $res[] = $realPartie;
        }
        return $res;
    }

    /**
     * Détermine si la date d'un match est hors de la plage des dates définissant les matches comme validés/comptabilisés
     */
    public function isNouveauMoisVirtuel(Datetime $date): bool {
        try {
            $date = $date->setTime(0, 0);
            $jour = $date->format('j');
            $mois = $date->format('n');
            $annee = $date->format('Y');

            while (!array_key_exists($mois, self::DATES_PUBLICATION)) {
                if ($mois == 12) {
                    $mois = 1;
                    $annee++;
                } else $mois++;
            }
            return $date->setTime(0,0)->getTimestamp() >= (new Datetime($annee . '/' . $mois . '/' . self::DATES_PUBLICATION[$mois]))->setTime(0,0)->getTimestamp();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Détermine la date de la dernière journée dont au moins un match est validé (pour lequel les points gagnés sont différents de 0.0)
     * @param Partie[] $validatedParties
     * @return int
     */
    public function lastValidatedMatchDate(array $validatedParties): int {
        $lastValidatedMatch = array_filter($validatedParties, function($partie) { return $partie->getPointsObtenus() != 0.0; });
        return (count($lastValidatedMatch) ? array_values($lastValidatedMatch)[0]->getDate() : new DateTime())->setTime(0, 0)->getTimestamp();
    }

    /**
     * @param string $joueurId
     * @return array
     * @throws InvalidURIParametersException
     * @throws URIPartNotValidException
     */
    public function getUnvalidatedPartiesJoueurByLicenceAndEarnedPoints(string $joueurId, bool $arePointdPhase2Updated): array
    {
        try {
            $validatedParties = $this->getPartiesJoueurByLicence($joueurId);
            $totalPointsObtenus = array_sum(array_map(function ($partie) use ($arePointdPhase2Updated) {
                /** Si les points officiels sont différents des points de début de saison (points de phase 2 à jour), on ne prend que les matches à partir de janvier */
                return ($arePointdPhase2Updated && $partie->getDate()->format('n') >= 1 && $partie->getDate()->format('n') <= 6) ?
                    floatval(round($partie->getPointsObtenus(), 1, PHP_ROUND_HALF_EVEN)) : 0.0;
            }, $validatedParties));
        } catch (NoFFTTResponseException $e) {
            $validatedParties = [];
            $totalPointsObtenus = 0.0;
        }

        try {
            $allParties = $this->apiRequest->get('xml_partie', [
                    'numlic' => $joueurId,
                ])["partie"] ?? [];
        } catch (NoFFTTResponseException $e) {
            $allParties = [];
        }

        $lastValidatedMatchDate = $this->lastValidatedMatchDate($validatedParties);

        $result = ['unvalidatedParties' => [], 'totalPointsObtenus' => $totalPointsObtenus];
        try {
            foreach ($allParties as $partie) {
                if ($partie["forfait"] === "0") {
                    list($nom, $prenom) = Utils::returnNomPrenom($partie['nom']);
                    $datePartie = \DateTime::createFromFormat('d/m/Y', $partie['date'])->setTime(0, 0);
                    $found = count(array_filter($validatedParties, function ($validatedPartie) use ($partie, $nom, $prenom, $datePartie, $lastValidatedMatchDate) {
                        return $partie["date"] === $validatedPartie->getDate()->format("d/m/Y")
                            /** Si le nom du joueur correspond bien */
                            && Utils::removeAccentLowerCaseRegex($nom) === Utils::removeAccentLowerCaseRegex($validatedPartie->getAdversaireNom())
                            /** Si le prénom du joueur correspond bien */
                            && (
                                preg_match('/' . Utils::removeAccentLowerCaseRegex($prenom) . '.*/', Utils::removeAccentLowerCaseRegex($validatedPartie->getAdversairePrenom())) or
                                str_contains(Utils::removeAccentLowerCaseRegex($prenom), Utils::removeAccentLowerCaseRegex($validatedPartie->getAdversairePrenom()))
                            )
                            /** Si le coefficient est renseigné */
                            && $validatedPartie->getCoefficient() === floatval($partie['coefchamp'])
                            /** Si le joueur n'est pas absent */
                            && !str_contains($prenom, "Absent") and !str_contains($nom, "Absent")
                            /** Si le match s'est déroulé après la date du dernier match validé */
                            && $datePartie->getTimestamp() <= $lastValidatedMatchDate
                            /** Si la partie a été réalisée durant le mois dernier ou durant le mois actuel */
                            && !($validatedPartie->getPointsObtenus() === 0.0
                                && (
                                    ($datePartie->format('n') === (new DateTime())->format('n')
                                     && $datePartie->format('Y') === (new DateTime())->format('Y'))
                                    || ($datePartie->format('n') . '/' . $datePartie->format('Y')) === date('n', strtotime('-1 month')) . '/' . date('Y', strtotime('-1 month'))
                                )
                            );
                    }));

                    if (!$found) {
                        $result['unvalidatedParties'][] = new UnvalidatedPartie(
                            $partie["epreuve"],
                            $partie["idpartie"],
                            floatval($partie["coefchamp"]),
                            $partie["victoire"] === "V",
                            false,
                            \DateTime::createFromFormat('d/m/Y', $partie['date']),
                            $nom,
                            $prenom,
                            Utils::formatPoints($partie["classement"])
                        );
                    }
                }
            }
            return $result;
        } catch (\Exception $e) {
            $result['unvalidatedParties'] = [];
            return $result;
        }
    }

    /**
     * @param string $joueurId
     * @return VirtualPoints Objet contenant les points gagnés/perdus et le classement virtuel du joueur
     */
    public function getJoueurVirtualPoints(string $joueurId): VirtualPoints
    {
        $pointCalculator = new PointCalculator();
        $matches = [];

        try {
            $classement = $this->getJoueurDetailsByLicence($joueurId);
            $virtualMonthlyPointsWon = 0.0;
            $virtualMonthlyPoints = 0.0;
            $latestMonth = null;
            $arePointdPhase2Updated = $classement->getPointsDebutSaison() !== $classement->getLicence() && (new DateTime())->format('n') < 2;
            $data = $this->getUnvalidatedPartiesJoueurByLicenceAndEarnedPoints($joueurId, $arePointdPhase2Updated);
            $monthPoints = round(($classement->getPointsLicence() + $data['totalPointsObtenus']), 1, PHP_ROUND_HALF_EVEN);
            $unvalidatedParties = $data['unvalidatedParties'];

            usort($unvalidatedParties, function (UnvalidatedPartie $a, UnvalidatedPartie $b) {
                return $a->getDate() >= $b->getDate();
            });

            /** @var UnvalidatedPartie $unvalidatedParty */
            foreach ($unvalidatedParties as $unvalidatedParty) {
                if ($latestMonth == null) {
                    $latestMonth = $unvalidatedParty->getDate()->format("m");
                } else {
                    if ($latestMonth != $unvalidatedParty->getDate()->format("m") && $this->isNouveauMoisVirtuel($unvalidatedParty->getDate())) {
                        $monthPoints = round($monthPoints + $virtualMonthlyPointsWon, 1, PHP_ROUND_HALF_EVEN);
                        $virtualMonthlyPointsWon = 0.0;
                        $latestMonth = $unvalidatedParty->getDate()->format("m");
                    }
                }

                $coeff = $unvalidatedParty->getCoefficientChampionnat();

                if (!$unvalidatedParty->isForfait()) {
                    $adversairePoints = $unvalidatedParty->getAdversaireClassement();

                    try {
                        $availableJoueurs = $this->getJoueursByNom($unvalidatedParty->getAdversaireNom(), $unvalidatedParty->getAdversairePrenom());
                        foreach ($availableJoueurs as $availableJoueur) {
                            if (round(($unvalidatedParty->getAdversaireClassement() / 100)) == $availableJoueur->getPoints()) {
                                $classementJoueur = $this->getClassementJoueurByLicence($availableJoueur->getLicence());
                                $adversairePoints = round($classementJoueur->getPoints(), 1, PHP_ROUND_HALF_EVEN);
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        $adversairePoints = $unvalidatedParty->getAdversaireClassement();
                    }

                    $points = $unvalidatedParty->isVictoire()
                        ? $pointCalculator->getPointVictory($monthPoints, floatval($adversairePoints))
                        : $pointCalculator->getPointDefeat($monthPoints, floatval($adversairePoints));
                    $virtualMonthlyPointsWon += $points * $coeff;

                    $matches[] = new CalculatedUnvalidatedPartie(
                        $unvalidatedParty->getAdversaireNom() . ' ' . $unvalidatedParty->getAdversairePrenom(),
                        $unvalidatedParty->isForfait(),
                        $unvalidatedParty->isVictoire(),
                        $adversairePoints,
                        $points * $coeff,
                        $unvalidatedParty->getDate()->format('d/m/o'),
                        str_replace(['FED_', 'L08_'], ['', ''], $unvalidatedParty->getEpreuve()),
                        $coeff
                    );
                }
            }

            $virtualMonthlyPoints = $monthPoints + $virtualMonthlyPointsWon;
            return new VirtualPoints(
                ($classement->getPointsLicence() + $data['totalPointsObtenus']),
                $virtualMonthlyPointsWon,
                $virtualMonthlyPoints,
                $virtualMonthlyPoints - $classement->getPointsDebutSaison(),
                $matches
            );
        } catch (JoueurNotFound $e) {
            return new VirtualPoints(($classement->getPointsLicence() + $data['totalPointsObtenus']), 0.0, $classement->getPointsLicence(), 0.0, $matches);
        }
    }

    /**
     * @param string $joueurId
     * @return float points mensuels gagnés ou perdus en fonction des points mensuels de l'adversaire
     */
    public function getVirtualPoints(string $joueurId) : float {
        return $this->getJoueurVirtualPoints($joueurId)->getPointsWon();
    }

    /**
     * @param string $clubId
     * @param string|null $type
     * @return Equipe[]
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws NoFFTTResponseException
     */
    public function getEquipesByClub(string $clubId, string $type = null)
    {
        $params = [
            'numclu' => $clubId,
        ];
        if ($type) {
            $params['type'] = $type;
        }

        if ($this->apiRequest->get('xml_equipe', $params) == []) return [];
        $data = $this->apiRequest->get('xml_equipe', $params)['equipe'];
        $data = $this->wrappedArrayIfUnique($data);

        $result = [];
        foreach ($data as $dataEquipe) {
            $result[] = new Equipe(
                $dataEquipe['libequipe'],
                $dataEquipe['libdivision'],
                $dataEquipe['liendivision'],
                $dataEquipe['libepr']
            );
        }
        return $result;
    }

    /**
     * @param string $lienDivision
     * @return EquipePoule[]
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws NoFFTTResponseException
     */
    public function getClassementPouleByLienDivision(string $lienDivision): array
    {
        $data = $this->apiRequest->get('xml_result_equ', ["action" => "classement"], $lienDivision)['classement'];
        $result = [];
        $lastClassment = 0;
        foreach ($data as $equipePouleData) {

            if (!is_string($equipePouleData['equipe'])) {
                break;
            }

            $result[] = new EquipePoule(
                $equipePouleData['clt'] === '-' ? $lastClassment : intval($equipePouleData['clt']),
                $equipePouleData['equipe'],
                intval($equipePouleData['joue']),
                intval($equipePouleData['pts']),
                $equipePouleData['numero'],
                intval($equipePouleData['totvic']),
                intval($equipePouleData['totdef']),
                intval($equipePouleData['idequipe']),
                $equipePouleData['idclub']
            );
            $lastClassment = $equipePouleData['clt'] == "-" ? $lastClassment : intval($equipePouleData['clt']);
        }
        return $result;
    }

    /**
     * @param string $lienDivision
     * @return Rencontre[]
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws NoFFTTResponseException
     */
    public function getRencontrePouleByLienDivision(string $lienDivision): array
    {
        $data = $this->apiRequest->get('xml_result_equ', [], $lienDivision);

        $result = [];
        if (array_key_exists('tour', $data)) {
            $data = $data['tour'];
            foreach ($data as $dataRencontre) {
                $equipeA = $dataRencontre['equa'];
                $equipeB = $dataRencontre['equb'];

                $result[] = new Rencontre(
                    $dataRencontre['libelle'],
                    is_array($equipeA) ? '': $equipeA,
                    is_array($equipeB) ? '': $equipeB,
                    intval($dataRencontre['scorea']),
                    intval($dataRencontre['scoreb']),
                    $dataRencontre['lien'],
                    \DateTime::createFromFormat('d/m/Y', $dataRencontre['dateprevue']),
                    empty($dataRencontre['datereelle']) ? null : \DateTime::createFromFormat('d/m/Y', $dataRencontre['datereelle'])
                );
            }
        }
        return $result;
    }


    /**
     * @param Equipe $equipe
     * @return Rencontre[]
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws NoFFTTResponseException
     */
    public function getProchainesRencontresEquipe(Equipe $equipe): array
    {
        $nomEquipe = Utils::extractNomEquipe($equipe);
        $rencontres = $this->getRencontrePouleByLienDivision($equipe->getLienDivision());

        $prochainesRencontres = [];
        foreach ($rencontres as $rencontre) {
            if ($rencontre->getDateReelle() === null && $rencontre->getNomEquipeA() === $nomEquipe || $rencontre->getNomEquipeB() === $nomEquipe) {
                $prochainesRencontres[] = $rencontre;
            }
        }
        return $prochainesRencontres;
    }

    /**
     * @param Equipe $equipe
     * @return ClubDetails|null
     * @throws ClubNotFoundException
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws NoFFTTResponseException
     */
    public function getClubEquipe(Equipe $equipe): ?ClubDetails
    {
        $nomEquipe = Utils::extractClub($equipe);
        $club = $this->getClubsByName($nomEquipe);

        if(count($club) === 1){
            return $this->getClubDetails($club[0]->getNumero());
        }

        return null;
    }

    /**
     * @param string $lienRencontre
     * @param string $clubEquipeA
     * @param string $clubEquipeB
     * @return RencontreDetails
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws InvalidLienRencontre
     * @throws NoFFTTResponseException
     */
    public function getDetailsRencontreByLien(string $lienRencontre, string $clubEquipeA = "", string $clubEquipeB = ""): RencontreDetails
    {
        $data = $this->apiRequest->get('xml_chp_renc', [], $lienRencontre);
        if (!(isset($data['resultat']) && isset($data['joueur']) && isset($data['partie']))) {
            throw new InvalidLienRencontre($lienRencontre);
        }

        // Vérification s'il y a inversion des équipes sur la feuille de match
        preg_match('/^renc_id=[0-9]+&is_retour=[0-9]+&phase=[0-9]+&res_1=[0-9]+&res_2=[0-9]+&equip_1=(?<equip_1>[A-Za-z0-9+\-\.(?:%28)(?:%29)]+)&equip_2=(?<equip_2>[A-Za-z0-9+\-\.(?:%28)(?:%29)]+)&equip_id1=[0-9]+&equip_id2=[0-9]+&clubnum_1=[0-9]+&clubnum_2=[0-9]+$/', $lienRencontre, $match);
        $equipesInversees = false;
        if (
            array_key_exists('equip_1', $match) &&
            array_key_exists('equip_2', $match) &&
            $match['equip_1'] == urlencode($data['resultat']['equb']) &&
            $match['equip_2'] == urlencode($data['resultat']['equa'])
        ) {
            $equipesInversees = true;
        }

        $factory = new RencontreDetailsFactory($this);
        return $factory->createFromArray($data, $clubEquipeA, $clubEquipeB, $equipesInversees);
    }

    /**
     * @return Actualite[]
     * @throws Exception\InvalidURIParametersException
     * @throws Exception\URIPartNotValidException
     * @throws NoFFTTResponseException
     */
    public function getActualites(): array
    {
        $data = $this->apiRequest->get('xml_new_actu')['news'];
        $data = $this->wrappedArrayIfUnique($data);

        $result = [];
        foreach ($data as $dataActualite) {
            $result[] = new Actualite(
                \DateTime::createFromFormat('Y-m-d', $dataActualite["date"]),
                $dataActualite['titre'],
                $dataActualite['description'],
                $dataActualite['url'],
                $dataActualite['photo'],
                $dataActualite['categorie']
            );
        }
        return $result;
    }

    private function wrappedArrayIfUnique($array): array
    {
        if (count($array) == count($array, COUNT_RECURSIVE)) {
            return [$array];
        }
        return $array;
    }
}