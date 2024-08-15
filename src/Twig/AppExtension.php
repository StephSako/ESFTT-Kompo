<?php

namespace App\Twig;

use Cocur\Slugify\Slugify;
use DateTime;
use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('journeeStillEditable', [$this, 'journeeStillEditable']),
            new TwigFunction('brulageCumule', [$this, 'brulageCumule']),
            new TwigFunction('isBrulesJ2', [$this, 'isBrulesJ2']),
            new TwigFunction('listeRemplacants', [$this, 'listeRemplacants']),
            new TwigFunction('isFieldEditable', [$this, 'isFieldEditable']),
            new TwigFunction('editableMandatoryFields', [$this, 'editableMandatoryFields']),
            new TwigFunction('isFieldEditableMandatory', [$this, 'isFieldEditableMandatory'])
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('listeEquipesSansDivision', [$this, 'listeEquipesSansDivision']),
            new TwigFilter('customSlug', [$this, 'customSlug']),
            new TwigFilter('listeEquipeToManage', [$this, 'listeEquipeToManage']),
            new TwigFilter('classement', [$this, 'classement']),
            new TwigFilter('array_sum', [$this, 'array_sum'])
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
     * Détermine les champs éditables obligatoires dans le formulaire de la page
     * @param array $champs
     * @return array
     */
    public function editableMandatoryFields(array $champs): array
    {
        return array_map(function (FormView $champ) {
            return count($champ->vars) && array_key_exists('attr', $champ->vars) && count($champ->vars['attr']) && array_key_exists('class', $champ->vars['attr']) && str_contains($champ->vars['attr']['class'], 'validate');
        }, $champs);
    }

    /**
     * Détermine si un champ est éditable et obligatoire dans le formulaire de la page
     * @param string $champ
     * @param array $champsEditables
     * @return bool
     */
    public function isFieldEditableMandatory(string $champ, array $champsEditables): bool
    {
        return array_key_exists($champ, $champsEditables) && $champsEditables[$champ];
    }

    /**
     * Détermine si un champ est éditable dans le formulaire de la page
     * @param string $champ
     * @param array $champsEditables
     * @return bool
     */
    public function isFieldEditable(string $champ, array $champsEditables): bool
    {
        return array_key_exists($champ, $champsEditables);
    }

    /**
     * Retourne la somme des valeurs numérique d'une tableau
     * @param array $array
     * @return int
     */
    public function array_sum(array $array): int
    {
        return array_sum($array);
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
        $str = $nbEquipes > 1 ? 'Les équipes ' : "L'équipe ";
        $numEquipes = array_values($numEquipes);
        sort($numEquipes);

        foreach ($numEquipes as $i => $numEquipe) {
            $str .= $numEquipe;
            if ($i == $nbEquipes - 2) $str .= ' et ';
            elseif ($i < $nbEquipes - 1) $str .= ', ';
        }

        $str .= $nbEquipes > 1 ? " n'ont " : " n'a ";
        $str .= 'pas de division' . ($nbEquipes > 1 ? 's' : '') . ' affiliée' . ($nbEquipes > 1 ? 's' : '') . ' : vous ne pouvez donc pas accéder à';
        $str .= ($nbEquipes > 1 ? ' leurs ' : ' ses ') . "compositions d'équipe";

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
            foreach ($equipe as $joueur) {
                $brulageGeneral[] = $joueur;
            }
        }
        $brulageJoueursCompo = (array_filter($brulageGeneral, function ($brulageG) use ($selectedPlayers) {
            return in_array($brulageG['idCompetiteur'], $selectedPlayers);
        }));

        $isBurnt = array_filter($brulageJoueursCompo, function ($brulageJoueur) use ($numEquipe) {
            return in_array(1, array_filter($brulageJoueur['brulage'], function ($numEquipeBrulage) use ($numEquipe) {
                return $numEquipeBrulage < $numEquipe;
            }, ARRAY_FILTER_USE_KEY));
        });

        $burntJ2 = array_values(array_map(function ($joueurBruleJ2) {
            return $joueurBruleJ2['idCompetiteur'];
        }, $isBurnt));

        return in_array($idCompetiteur, $burntJ2) && count($burntJ2) > 1;
    }

    /**
     * @param array $teams
     * @param int $mode : 0 => non enregistrées, 1 => create, 2 => update
     * @return string
     */
    public function listeEquipeToManage(array $teams, int $mode): string
    {
        $nbEquipes = count($teams);
        $str = $nbEquipes > 1 ? 'Les équipes ' : "L'équipe ";

        foreach (array_values($teams) as $i => $numEquipe) {
            $str .= $numEquipe;
            if ($i == $nbEquipes - 2) $str .= ' et ';
            elseif ($i < $nbEquipes - 1) $str .= ', ';
        }

        $str .= ($mode == 0 ? ($nbEquipes > 1 ? ' ne sont pas ' : " n'est pas ") : ($nbEquipes > 1 ? ' seront ' : ' sera '));
        $str .= ($mode == 0 ? 'enregistrées auprès de la FFTT' : ($mode == 1 ? 'créée' : 'modifiée')) . ($nbEquipes > 1 && $mode > 0 ? 's' : '');

        return $str;
    }

    /**
     * @param float $pointsVirtuels
     * @return int
     */
    public function classement(float $pointsVirtuels): int
    {
        return intval(substr(floor($pointsVirtuels), 0, -2));
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
        /** on récupère la liste des brûlages de l'équipe */
        if (!array_key_exists('Équipe ' . $numeroEquipe, $allBrulages)) return [];
        else $brulagesEquipe = $allBrulages['Équipe ' . $numeroEquipe];

        /** On filtre les joueurs de l'équipe disponibles non sélectionnés pour cette journée */
        $disposNonSelectionnesEquipe = array_filter($allDispos['Équipe ' . $numeroEquipe], function ($joueurDisposNonSelectionne) use ($selectedIdsPlayersEquipe) {
            return $joueurDisposNonSelectionne['disponibilite'] == "1" && !in_array($joueurDisposNonSelectionne['joueur']->getIdCompetiteur(), $selectedIdsPlayersEquipe);
        });

        /** On filtre les joueurs de l'équipe non brûlés pour cette journée */
        $joueursNonBrules = array_filter($brulagesEquipe, function ($joueur) use ($numeroEquipe) {
            return array_sum(array_filter($joueur['brulage'], function ($numEquipe) use ($numeroEquipe) {
                    return $numEquipe < $numeroEquipe;
                }, ARRAY_FILTER_USE_KEY)) < 2;
        });
        $joueursNonBrules = array_map(function ($joueurNonBrule) {
            return $joueurNonBrule['idCompetiteur'];
        }, $joueursNonBrules);

        return array_keys(array_filter($disposNonSelectionnesEquipe, function ($remplacant) use ($joueursNonBrules) {
            return in_array($remplacant['joueur']->getIdCompetiteur(), $joueursNonBrules);
        }));
    }
}