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

    public function createFromArray(array $array, string $clubEquipeA, string $clubEquipeB): RencontreDetails
    {

        $joueursA = [];
        $joueursB = [];
        foreach ($array['joueur'] as $joueur) {
            $joueursA[] = [$joueur['xja'], $joueur['xca']];
            $joueursB[] = [$joueur['xjb'], $joueur['xcb']];
        }

        $parties = $this->getParties($array['partie']);

        if (is_array($array['resultat']['resa'])) {
            [$scoreA, $scoreB] = $this->getScores($parties);
        } else {
            $scoreA = $array['resultat']['resa'] == "F0" ? 0 : $array['resultat']['resa'];
            $scoreB = $array['resultat']['resb'] == "F0" ? 0 : $array['resultat']['resb'];
        }
        $joueursAFormatted = $this->formatJoueurs($joueursA, $clubEquipeA);
        $joueursBFormatted = $this->formatJoueurs($joueursB, $clubEquipeB);

        $expectedA = 0;
        $expectedB = 0;

        foreach ($joueursAFormatted as $joueurA) {
            foreach ($joueursBFormatted as $joueurB) {
                if($joueurA->getPoints() === $joueurB->getPoints()){
                    $expectedA += 0.5;
                    $expectedB += 0.5;
                }
                elseif ($joueurA->getPoints() > $joueurB->getPoints()) {
                    $expectedA += 1;
                } else {
                    $expectedB += 1;
                }
            }
        }

        $rencontreDetails = new RencontreDetails(
            $array['resultat']['equa'],
            $array['resultat']['equb'],
            $scoreA,
            $scoreB,
            $joueursAFormatted,
            $joueursBFormatted,
            $parties,
            $expectedA,
            $expectedB
        );

        return $rencontreDetails;
    }

    private function getScores(array $parties)
    {
        $scoreA = 0;
        $scoreB = 0;

        foreach ($parties as $partie) {
            $scoreA += $partie->getScoreA();
            $scoreB += $partie->getScoreB();
        }

        return [$scoreA, $scoreB];
    }

    /**
     * @param $data
     * @param string $playerClubId
     * @return Joueur[]
     */
    private function formatJoueurs($data, string $playerClubId): array
    {
        $joueursClub = $this->api->getJoueursByClub($playerClubId);

        $joueurs = [];
        foreach ($data as $joueurData) {
            $nomPrenom = $joueurData[0];
            [$nom, $prenom] = Utils::returnNomPrenom($nomPrenom);

            try {
                if ($nom === "" && $prenom === "Absent") {
                    $joueurs[] = new Joueur($nom, $prenom, "", null, null);
                } else {
                    $playerFoundInClub = false;

                    foreach ($joueursClub as $joueurClub){
                        if($joueurClub->getNom() === Accentuation::remove($nom) && $joueurClub->getPrenom() === $prenom){
                            list($sexe, $points) = !empty($joueurData[1]) ? explode(" ", $joueurData[1]) : [null, null];
                            $joueurs[] = new Joueur(
                                $joueurClub->getNom(),
                                $joueurClub->getPrenom(),
                                $joueurClub->getLicence(),
                                intval(substr($points, 0, -3)),
                                $sexe
                            );
                            $playerFoundInClub = true;
                            break;
                        }
                    }
                    if(!$playerFoundInClub){
                        $joueurs[] = new Joueur($nom, $prenom, "", null, null);
                    }
                }
            } catch (NoFFTTResponseException $e) {
                $joueurs[] = new Joueur($nom, $prenom, "", null, null);
            }
        }
        return $joueurs;
    }

    private function getParties($data)
    {
        $parties = [];
        foreach ($data as $partieData) {
            $parties[] = new Partie(
                $partieData['ja'],
                $partieData['jb'],
                $partieData['scorea'] === '-' ? 0 : intval($partieData['scorea']),
                $partieData['scoreb'] === '-' ? 0 : intval($partieData['scoreb'])
            );
        }
        return $parties;
    }
}