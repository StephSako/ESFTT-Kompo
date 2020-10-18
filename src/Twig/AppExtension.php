<?php

namespace App\Twig;

use App\Entity\JourneeDepartementale;
use DateTime;
use Proxies\__CG__\App\Entity\JourneeParis;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('stillEditable', [$this, 'stillEditable']),
        ];
    }

    /**
     * @param JourneeDepartementale|JourneeParis $journee
     * @return bool
     */
    public function stillEditable($journee)
    {
        return ((int)(new DateTime())->diff($journee->getDate())->format('%R%a') >= 0 || $journee->getUndefined());
    }
}