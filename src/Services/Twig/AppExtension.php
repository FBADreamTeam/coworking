<?php

namespace App\Services\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class AppExtention
 * Permet de créer des fonctions pour twig.
 */
class AppExtension extends AbstractExtension
{
    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return array(
            new TwigFilter('price', array($this, 'priceFilter')),
        );
    }

    /**
     * @return array|Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('getAssetsDir', array($this, 'getAssetsDir')),
        ];
    }


    public function priceFilter($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',', $symbol = '€')
    {
        return number_format($number / 100, $decimals, $decPoint, $thousandsSep).' '.$symbol;
    }

    public function getAssetsDir(string $dir = 'room')
    {
        return 'images/' . $dir . '/';
    }
}
