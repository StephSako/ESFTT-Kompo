<?php

namespace App\Twig;

use App\Entity\Journee;
use App\Entity\Rencontre;
use Cocur\Slugify\Slugify;
use DateTime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('rencontreStillEditable', [$this, 'rencontreStillEditable']),
            new TwigFunction('journeeStillEditable', [$this, 'journeeStillEditable']),
            new TwigFunction('brulageCumule', [$this, 'brulageCumule']),
            new TwigFunction('isBrulesJ2', [$this, 'isBrulesJ2']),
            new TwigFunction('journeePassee', [$this, 'journeePassee']),
            new TwigFunction('listeRemplacants', [$this, 'listeRemplacants'])
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('listeEquipesSansDivision', [$this, 'listeEquipesSansDivision']),
            new TwigFilter('customSlug', [$this, 'customSlug']),
            new TwigFilter('listeEquipeToManage', [$this, 'listeEquipeToManage'])
        ];
    }

    /**
     * @param DateTime $latestDate
     * @return bool
     */
    public function journeeStillEditable(DateTime $latestDate): bool
    {
        return intval($latestDate->diff(new DateTime())->format('%R%a')) <= 0;
    }

    /**
     * @param Rencontre $rencontre
     * @return bool
     */
    public function rencontreStillEditable(Rencontre $rencontre): bool
    {
        $dateDepassee = intval((new DateTime())->diff($rencontre->getIdJournee()->getDateJournee())->format('%R%a')) >= 0;
        $dateReporteeDepassee = intval((new DateTime())->diff($rencontre->getDateReport())->format('%R%a')) >= 0;
        return (($dateDepassee && !$rencontre->isReporte()) || ($dateReporteeDepassee && $rencontre->isReporte()) || $rencontre->getIdJournee()->getUndefined());
    }

    /**
     * @param $brulages
     * @param int $limite
     * @return int
     */
    public function brulageCumule($brulages, int $limite): int
    {
        return array_sum(array_slice($brulages, 0, $limite));
    }

    /**
     * @param array $numEquipes
     * @return string
     */
    public function listeEquipesSansDivision(array $numEquipes): string
    {
        $nbEquipes = count($numEquipes);
        $str = $nbEquipes > 1 ? 'Les équipes ' : 'L\'équipe ';
        $numEquipes = array_values($numEquipes);
        sort($numEquipes);

        foreach ($numEquipes as $i => $numEquipe) {
            $str .= $numEquipe;
            if ($i == $nbEquipes - 2) $str .= ' et ';
            elseif ($i < $nbEquipes - 1) $str .= ', ';
        }

        $str .= $nbEquipes > 1 ? ' n\'ont ' : ' n\'a ';
        $str .= 'pas de division' . ($nbEquipes > 1 ? 's':'') . ' affiliée' . ($nbEquipes > 1 ? 's':'') . ' : vous ne pouvez donc pas accéder à';
        $str .= ($nbEquipes > 1 ? ' leurs ' : ' ses ') . 'compositions d\'équipe';

        return $str;
    }

    /**
     * @param string $chaine
     * @return string
     */
    public function customSlug(string $chaine): string
    {
        return (new Slugify())->slugify($chaine);
    }

    /**
     * @param int $numEquipe
     * @param int $idCompetiteur
     * @param array $selectedPlayers
     * @param array $brulageGeneralEquipes
     * @return bool
     */
    public function isBrulesJ2(int $numEquipe, int $idCompetiteur, array $selectedPlayers, array $brulageGeneralEquipes): bool
    {
        $brulageGeneral = [];
        foreach ($brulageGeneralEquipes as $equipe) {
            foreach ($equipe['joueurs'] as $joueur) {
                $brulageGeneral[] = $joueur;
            }
        }
        $brulageJoueursCompo = (array_filter($brulageGeneral, function($brulageG) use ($selectedPlayers){
            return in_array($brulageG['idCompetiteur'], $selectedPlayers);
        }));

        $isBurnt = array_filter($brulageJoueursCompo, function($brulageJoueur) use ($numEquipe) {
            return in_array(1, array_filter($brulageJoueur['brulage'], function($numEquipeBrulage) use ($numEquipe) {
                return $numEquipeBrulage < $numEquipe;
            }, ARRAY_FILTER_USE_KEY));
        });

        $burntJ2 = array_values(array_map(function($joueurBruleJ2) {
            return $joueurBruleJ2['idCompetiteur'];
        },$isBurnt));

        return in_array($idCompetiteur, $burntJ2) && count($burntJ2) > 1;
    }

    /**
     * @param array $teams
     * @param int $mode : 0 => delete, 1 => create, 2 => update
     * @return string
     */
    public function listeEquipeToManage(array $teams, int $mode): string
    {
        $nbEquipes = count($teams);
        $str = $nbEquipes > 1 ? 'Les équipes ' : 'L\'équipe ';

        foreach (array_values($teams) as $i => $numEquipe) {
            $str .= $numEquipe;
            if ($i == $nbEquipes - 2) $str .= ' et ';
            elseif ($i < $nbEquipes - 1) $str .= ', ';
        }

        $str .= $nbEquipes > 1 ? ' seront ' : ' sera ';
        $str .= ($mode == 0 ? 'supprimée' : ($mode == 1 ? 'créée' : 'modifiée')) . ($nbEquipes > 1 ? 's' : '');

        return $str;
    }

    /**
     * @param Journee $journee
     * @return bool
     */
    public function journeePassee(Journee $journee): bool {
        return !$journee->getUndefined() && intval((new DateTime())->diff($journee->getDateJournee())->format('%R%a')) < 0;
    }

    /**
     * @param int $numeroEquipe
     * @param array $allBrulages
     * @param array $allDispos
     * @param array $selectedIdsPlayersEquipe
     * @return array
     */
    public function listeRemplacants(int $numeroEquipe, array $allBrulages, array $allDispos, array $selectedIdsPlayersEquipe): array
    {
        $brulageEquipe = array_filter($allBrulages, function($e) use ($numeroEquipe) {
            return $e['nomEquipe'] == 'Équipe n°' . $numeroEquipe;
        });
        if (count($brulageEquipe) == 0) return [];

        /** on récupère la liste des brûlages de l'équipe */
        $brulagesEquipe = array_values($brulageEquipe)[0]['joueurs'];

        /** On filtre les joueurs de l'équipe disponibles non sélectionnés pour cette journée */
        $disposNonSelectionnesEquipe = array_filter($allDispos['Équipe n°' . $numeroEquipe], function ($joueurDisposNonSelectionne) use ($selectedIdsPlayersEquipe) {
            return $joueurDisposNonSelectionne['disponibilite'] == "1" && !in_array($joueurDisposNonSelectionne['joueur']->getIdCompetiteur(), $selectedIdsPlayersEquipe);
        });

        /** On filtre les joueurs de l'équipe non brûlés pour cette journée */
        $joueursNonBrules = array_filter($brulagesEquipe, function($joueur) use ($numeroEquipe) {
            return array_sum(array_filter($joueur['brulage'], function($numEquipe) use ($numeroEquipe) {
                    return $numEquipe < $numeroEquipe;
                }, ARRAY_FILTER_USE_KEY)) < 2;
        });
        $joueursNonBrules = array_map(function($joueurNonBrule) {
            return $joueurNonBrule['idCompetiteur'];
        }, $joueursNonBrules);

        return array_keys(array_filter($disposNonSelectionnesEquipe, function($remplacant) use ($joueursNonBrules) {
            return in_array($remplacant['joueur']->getIdCompetiteur(), $joueursNonBrules);
        }));
    }
}