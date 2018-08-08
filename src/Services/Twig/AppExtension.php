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
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getAssetsDir', array($this, 'getAssetsDir')),
        ];
    }

    /**
     * @param int    $number
     * @param int    $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     * @param string $symbol
     *
     * @return string
     */
    public function priceFilter(int $number, int $decimals = 0, string $decPoint = '.', string $thousandsSep = ',', string $symbol = '€'): string
    {
        return number_format($number / 100, $decimals, $decPoint, $thousandsSep).' '.$symbol;
    }

    /**
     * @param string $dir
     *
     * @return string
     */
    public function getAssetsDir(string $dir = 'room'): string
    {
        return 'images/'.$dir.'/';
    }
}
