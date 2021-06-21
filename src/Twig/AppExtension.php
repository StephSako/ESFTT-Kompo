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
            new TwigFilter('customSlug', [$this, 'customSlug'])
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
        $str = '';
        if (count($numEquipes) > 1) $str .= 'Les équipes ';
        else $str .= 'L\'équipe ';

        foreach ($numEquipes as $i => $numEquipe) {
            $str .= $numEquipe;
            if ($i == count($numEquipes) - 2) $str .= ' et ';
            elseif ($i < count($numEquipes) - 1) $str .= ', ';
        }

        if (count($numEquipes) > 1) $str .= ' n\'ont ';
        else $str .= ' n\'a ';

        $str .= 'pas de division' . (count($numEquipes) > 1 ? 's':'') . ' affiliée' . (count($numEquipes) > 1 ? 's':'') . ' : vous ne pouvez donc pas accéder ';

        if (count($numEquipes) > 1) $str .= ' à leurs compositions d\'équipe.';
        else $str .= ' à ses compositions d\'équipe.';

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
}