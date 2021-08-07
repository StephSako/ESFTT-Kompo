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
            new TwigFunction('isBrulesJ2', [$this, 'isBrulesJ2'])
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
     * @param Journee $journee
     * @param Rencontre[] $rencontres
     * @return bool
     */
    public function journeeStillEditable(Journee $journee, array $rencontres): bool
    {
        $nbRencontresReportees = count(array_filter($rencontres, function ($rencontre)
            {
                return ($rencontre->isReporte() && intval((new DateTime())->diff($rencontre->getDateReport())->format('%R%a')) >= 0 ? $rencontre : null);
            }));
        $dateDepassee = intval((new DateTime())->diff($journee->getDateJournee())->format('%R%a')) >= 0;
        return (($dateDepassee || $nbRencontresReportees > 0) || $journee->getUndefined());
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

        foreach (array_values($numEquipes) as $i => $numEquipe) {
            $str .= $numEquipe;
            if ($i == $nbEquipes - 2) $str .= ' et ';
            elseif ($i < $nbEquipes - 1) $str .= ', ';
        }

        $str .= $nbEquipes > 1 ? ' n\'ont ' : ' n\'a ';
        $str .= 'pas de division' . ($nbEquipes > 1 ? 's':'') . ' affiliée' . ($nbEquipes > 1 ? 's':'') . ' : vous ne pouvez donc pas accéder ';
        $str .= $nbEquipes > 1 ? ' à leurs compositions d\'équipe.' : ' à ses compositions d\'équipe.';

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
     * @param int $nbCompo
     * @param int $idCompetiteur
     * @param array $brulageJ2
     * @param array $selectedPlayers
     * @return bool
     */
    public function isBrulesJ2(int $nbCompo, int $idCompetiteur, array $brulageJ2, array $selectedPlayers): bool
    {
        $burntJ2 = array_intersect(array_merge(...array_filter($brulageJ2, function($index) use ($nbCompo) {
            return $index < $nbCompo;
        },ARRAY_FILTER_USE_KEY)), $selectedPlayers);
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
}