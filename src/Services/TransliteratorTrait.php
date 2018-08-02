<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 02/08/2018
 * Time: 12:15
 */

namespace App\Services;


use Behat\Transliterator\Transliterator;

trait TransliteratorTrait
{
    /**
     * @param string $text
     * @return string
     */
    public static function slugify(string $text): string
    {
        return Transliterator::transliterate($text);
    }
}