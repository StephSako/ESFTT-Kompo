<?php
/**
 * Created by Antoine Lamirault.
 */

namespace FFTTApi\Service;


use FFTTApi\Model\Equipe;

class Utils
{
    public static function returnNomPrenom(string $raw) {
        $raw = self::removeSeparatorsDuplication($raw);
        // On extrait le nom et le prénom
        $return = preg_match("/^(?<nom>[A-ZÀ-Ý]+(?:(?:[\s'\-])*[A-ZÀ-Ý]+)*)\s(?<prenom>[A-ZÀ-Ý][a-zà-ÿ]*(?:(?:[\s'\-])*[A-ZÀ-Ý]?[a-zà-ÿ]*)*)$/", $raw, $result);

        return 1 !== $return ? ['', ''] :
            [
                $result['nom'],
                $result['prenom'],
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

    public static function removeAccentLowerCaseRegex(string $string): string {
        return preg_replace(['/\?|\-|\s|\'/'], '.', mb_convert_case(\Transliterator::create('NFD; [:Nonspacing Mark:] Remove;')
            ->transliterate($string), MB_CASE_LOWER, "UTF-8"));
    }

    /**
     * Permet de supprimer des séparateurs (espaces, tirets) dupliqués.
     */
    public static function removeSeparatorsDuplication(string $raw): string
    {
        return preg_replace(['/\s+/', '/(?:\s*\-\s*)+|-+/'], [' ', '-'], $raw) ?? '';
    }

    /**
     * Ecrire dans le fichier de logs de l'API FFTT
     * @param string $message
     * @return void
     */
    public static function writeLog(string $message) {
        $fp = fopen('..\var\log\prod.log', 'a');
        fwrite($fp, $message . PHP_EOL);
        fclose($fp);
    }
}