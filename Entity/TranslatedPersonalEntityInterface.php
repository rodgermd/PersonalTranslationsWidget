<?php
/**
 * Created by PhpStorm.
 * User: rodger
 * Date: 12/8/14
 * Time: 10:29 PM
 */

namespace Ladela\PersonalTranslationsWidgetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Gedmo\Translatable\Translatable;

/**
 * Interface TranslatedPersonalEntityInterface
 *
 * @package Ladela\PersonalTranslationsWidgetBundle\Entity
 */
interface TranslatedPersonalEntityInterface extends Translatable
{
    /**
     * Sets Locale
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setTranslatableLocale($locale);

    /**
     * Gets translations
     *
     * @return ArrayCollection
     */
    public function getTranslations();

    /**
     * Adds translation
     *
     * @param AbstractPersonalTranslation $translation
     *
     * @return $this
     */
    public function addTranslation(AbstractPersonalTranslation $translation);

    /**
     * Gets translation class
     *
     * @return string
     */
    public static function getTranslationClass();
} 