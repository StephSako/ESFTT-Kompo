<?php
/**
 * Created by Antoine Lamirault.
 */

namespace FFTTApi\Service;


use FFTTApi\Model\Equipe;

class Utils
{
    public static function returnNomPrenom(string $s) {
        $nom = [];
        $prenom = [];
        $words = explode(" ", $s);

        foreach ($words as $word){
            $lastChar = substr($word, -1);
            mb_strtolower($lastChar, "UTF-8") == $lastChar ? $prenom[] = $word : $nom[] = $word;
        }

        return [
            implode(" ", $nom),
            implode(" ", $prenom),
        ];
    }

    public static function formatPoints(string $classement) : string {
        $explode = explode("-", $classement);
        if(count($explode) == 2){
            $classement=$explode[1];
        }
        return $classement;
    }

    public static function extractNomEquipe(Equipe $equipe): string{
        $explode = explode(" - ", $equipe->getLibelle());
        if(count($explode) === 2){
            return $explode[0];
        }
        return $equipe->getLibelle();
    }

    public static function extractClub(Equipe $equipe): string{
       $nomEquipe = self::extractNomEquipe($equipe);
       return preg_replace('/ [0-9]+$/', '', $nomEquipe);
    }

}