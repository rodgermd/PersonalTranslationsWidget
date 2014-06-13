<?php
/**
 * Created by PhpStorm.
 * User: rodger
 * Date: 12.05.14
 * Time: 14:15
 */

namespace Ladela\PersonalTranslationsWidgetBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractTranslatedEntity
 *
 * @package Ladela\PersonalTranslationsWidgetBundle\Entity
 * @ORM\MappedSuperclass
 */
abstract class AbstractTranslatedEntity
{
    /**
     * @var string
     */
    protected $locale;

    /** @var ArrayCollection */
    protected $translations;

    /**
     * Object constructor
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Sets Locale
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Gets Locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Gets translations
     *
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Adds translation
     *
     * @param AbstractTranslation $t
     *
     * @return $this
     */
    public function addTranslation(AbstractTranslation $t)
    {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }

        return $this;
    }
}
