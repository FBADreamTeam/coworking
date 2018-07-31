<?php

namespace App\Services\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class AppExtention
 * Permet de créer des fonctions pour twig.
 */
class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('price', array($this, 'priceFilter')),
        );
    }

    public function priceFilter($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',', $symbol = '€')
    {
        return number_format($number / 100, $decimals, $decPoint, $thousandsSep).' '.$symbol;
    }
}
