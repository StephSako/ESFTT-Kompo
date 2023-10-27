<?php

namespace FFTTApi\Service;

use Accentuation\Accentuation;
use FFTTApi\Exception\NoFFTTResponseException;
use FFTTApi\FFTTApi;
use FFTTApi\Model\Rencontre\Joueur;
use FFTTApi\Model\Rencontre\Partie;
use FFTTApi\Model\Rencontre\RencontreDetails;

/**
 * Created by Antoine Lamirault.
 */
class RencontreDetailsFactory
{
    /**
     * @var FFTTApi
     */
    private $api;

    public function __construct(FFTTApi $api)
    {
        $this->api = $api;
    }

    public function createFromArray(array $array, string $clubEquipeA, string $clubEquipeB, bool $equipesInversees): RencontreDetails
    {
        $joueursA = [];
        $joueursB = [];
        foreach ($array['joueur'] as $joueur) {
            $joueursA[] = ['nom' => $joueur[$equipesInversees ? 'xjb' : 'xja'] ?: '', 'points' => $joueur[$equipesInversees ? 'xcb' : 'xca'] ?: ''];
            $joueursB[] = ['nom' => $joueur[$equipesInversees ? 'xja' : 'xjb'] ?: '', 'points' => $joueur[$equipesInversees ? 'xca' : 'xcb'] ?: ''];
        }

        $wholeTeamAForfeit = 0 === count(array_filter($joueursA, function ($joueurA) { return $joueurA['nom'] && $joueurA['points']; }));
        $wholeTeamBForfeit = 0 === count(array_filter($joueursB, function ($joueurB) { return $joueurB['nom'] && $joueurB['points']; }));
        $joueursAFormatted = !$wholeTeamAForfeit ? $this->formatJoueurs($joueursA, $clubEquipeA) : [];
        $joueursBFormatted = !$wholeTeamBForfeit ? $this->formatJoueurs($joueursB, $clubEquipeB) : [];

        $parties = $this->getParties($array['partie'], $equipesInversees);

        if (is_array($array['resultat']['resa'])) {
            $scores = $this->getScores($parties);
            $scoreA = $scores[$equipesInversees ? 'scoreB' : 'scoreA'];
            $scoreB = $scores[$equipesInversees ? 'scoreA' : 'scoreB'];
        } else {
            $scoreA = $array['resultat'][$equipesInversees ? 'resb' : 'resa'] == "F0" ? 0 : $array['resultat'][$equipesInversees ? 'resb' : 'resa'];
            $scoreB = $array['resultat'][$equipesInversees ? 'resa' : 'resb'] == "F0" ? 0 : $array['resultat'][$equipesInversees ? 'resa' : 'resb'];
        }

        $expected = $this->getExpectedPoints($parties, $joueursAFormatted, $joueursBFormatted);

        return new RencontreDetails(
            $array['resultat'][$equipesInversees ? 'equb' : 'equa'],
            $array['resultat'][$equipesInversees ? 'equa' : 'equb'],
            $scoreA,
            $scoreB,
            $joueursAFormatted,
            $joueursBFormatted,
            $parties,
            $expected['expectedA'],
            $expected['expectedB']
        );
    }

    /**
     * @param Partie[] $parties
     * @param array<string, Joueur> $joueursAFormatted
     * @param array<string, Joueur> $joueursBFormatted
     * @return array{expectedA: float, expectedB: float}
     */
    private function getExpectedPoints(array $parties, array $joueursAFormatted, array $joueursBFormatted): array
    {
        $expectedA = 0;
        $expectedB = 0;

        foreach ($parties as $partie) {
            $adversaireA = $partie->getAdversaireA();
            $adversaireB = $partie->getAdversaireB();

            if (isset($joueursAFormatted[$adversaireA])) {
                $joueurA = $joueursAFormatted[$adversaireA];
                $joueurAPoints = $joueurA->getPoints();
            } else {
                $joueurAPoints = 'NONE';
            }

            if (isset($joueursBFormatted[$adversaireB])) {
                $joueurB = $joueursBFormatted[$adversaireB];
                $joueurBPoints = $joueurB->getPoints();
            } else {
                $joueurBPoints = 'NONE';
            }

            if ($joueurAPoints === $joueurBPoints) {
                $expectedA += 0.5;
                $expectedB += 0.5;
            } elseif ($joueurAPoints > $joueurBPoints) {
                $expectedA += 1;
            } else {
                $expectedB += 1;
            }
        }

        return [
            'expectedA' => $expectedA,
            'expectedB' => $expectedB,
        ];
    }

    /**
     * @param Partie[] $parties
     * @return array{scoreA: int, scoreB: int}
     */
    private function getScores(array $parties): array
    {
        $scoreA = 0;
        $scoreB = 0;

        foreach ($parties as $partie) {
            $scoreA += $partie->getScoreA();
            $scoreB += $partie->getScoreB();
        }

        return [
            'scoreA' => $scoreA,
            'scoreB' => $scoreB,
        ];
    }

    /**
     * @param array<array{nom: string, points: string}> $data
     * @param string $playerClubId
     * @return array<string, Joueur>
     */
    private function formatJoueurs(array $data, string $playerClubId): array
    {
        $joueursClub = $this->api->getJoueurDetailsByLicence('', $playerClubId);

        $joueurs = [];
        foreach ($data as $joueurData) {
            if ($joueurData['nom'] && $joueurData['points']) {
                $nomPrenom = $joueurData['nom'];
                [$nom, $prenom] = Utils::returnNomPrenom($nomPrenom);
                $joueurs[Utils::removeSeparatorsDuplication($nomPrenom)] = $this->formatJoueur($prenom, $nom, $joueurData['points'], $joueursClub);
            }
        }
        return $joueurs;
    }

    /**
     * @param string $prenom
     * @param string $nom
     * @param string $points
     * @param array $joueursClub
     * @return Joueur
     */
    private function formatJoueur(string $prenom, string $nom, string $points, array $joueursClub): Joueur
    {
        if ($nom === "" && $prenom === "Absent") {
            return new Joueur($nom, $prenom, "", null, null);
        }

        try {
            foreach ($joueursClub as $joueurClub) {
                if (Utils::removeSeparatorsDuplication(trim($joueurClub->getNom())) === $nom && Utils::removeSeparatorsDuplication(trim($joueurClub->getPrenom())) === $prenom) {
                    $return = preg_match('/^(N°[0-9]*- ){0,1}(?<sexe>[A-Z]{1}) (?<points>[0-9]+)pts$/', $points, $result);

                    if ($return === false) {
                        throw new \RuntimeException(
                            sprintf(
                                "Not able to extract sexe and points in '%s'",
                                $points
                            )
                        );
                    }
                    $sexe = $result['sexe'];
                    $playerPoints = $result['points'];

                    return new Joueur(
                        $nom,
                        $prenom,
                        $joueurClub->getLicence(),
                        $joueurClub->getPointsMensuel(),
                        $sexe
                    );
                }
            }

        } catch (NoFFTTResponseException $e) { }

        return new Joueur($nom, $prenom, "", null, null);
    }

    /**
     * @param array $data
     * @param bool $equipesInversees
     * @return Partie[]
     */
    private function getParties(array $data, bool $equipesInversees): array
    {
        $parties = [];
        foreach ($data as $partieData) {
            $setsDetails = array_map(function ($setDetail) {
                return intval($setDetail);
            }, explode(' ', trim($partieData['detail'])));

            $parties[] = new Partie(
                is_array($partieData[$equipesInversees ? 'jb' : 'ja']) ? 'Absent Absent' : Utils::removeSeparatorsDuplication($partieData[$equipesInversees ? 'jb' : 'ja']),
            is_array($partieData[$equipesInversees ? 'ja' : 'jb']) ? 'Absent Absent' : Utils::removeSeparatorsDuplication($partieData[$equipesInversees ? 'ja' : 'jb']),
                $partieData[$equipesInversees ? 'scoreb' : 'scorea'] === '-' ? 0 : intval($partieData[$equipesInversees ? 'scoreb' : 'scorea']),
                $partieData[$equipesInversees ? 'scorea' : 'scoreb'] === '-' ? 0 : intval($partieData[$equipesInversees ? 'scorea' : 'scoreb']),
                $setsDetails
            );
        }
        return $parties;
    }

}