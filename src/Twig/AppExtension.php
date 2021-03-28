<?php

namespace App\Twig;

use App\Entity\JourneeDepartementale;
use App\Entity\JourneeParis;
use App\Entity\RencontreDepartementale;
use App\Entity\RencontreParis;
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
            new TwigFunction('brulageCumule', [$this, 'brulageCumule'])
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('listeEquipesSansDivision', [$this, 'listeEquipesSansDivision'])
        ];
    }

    /**
     * @param JourneeDepartementale|JourneeParis $journee
     * @param RencontreDepartementale[]|RencontreParis[] $rencontres
     * @return bool
     */
    public function journeeStillEditable($journee, array $rencontres): bool
    {
        $nbRencontresReportees = count(array_filter($rencontres, function ($rencontre)
            {
                return ($rencontre->isReporte() && intval((new DateTime())->diff($rencontre->getDateReport())->format('%R%a')) >= 0 ? $rencontre : null);
            }));
        $dateDepassee = intval((new DateTime())->diff($journee->getDate())->format('%R%a')) >= 0;
        return (($dateDepassee || $nbRencontresReportees > 0) || $journee->getUndefined());
    }

    /**
     * @param RencontreDepartementale|RencontreParis $rencontre
     * @return bool
     */
    public function rencontreStillEditable($rencontre): bool
    {
        $dateDepassee = intval((new DateTime())->diff($rencontre->getIdJournee()->getDate())->format('%R%a')) >= 0;
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
}