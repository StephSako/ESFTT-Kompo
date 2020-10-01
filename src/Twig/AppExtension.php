<?php

namespace App\Twig;

use DateTime;
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

    public function stillEditable(DateTime $date)
    {
        return (int)(new DateTime())->diff($date)->format('%R%a') >= 0;
    }
}