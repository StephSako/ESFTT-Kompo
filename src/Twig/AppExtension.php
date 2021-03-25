<?php

namespace App\Twig;

use App\Entity\JourneeDepartementale;
use App\Entity\JourneeParis;
use App\Entity\RencontreDepartementale;
use App\Entity\RencontreParis;
use DateTime;
use Twig\Extension\AbstractExtension;
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
}