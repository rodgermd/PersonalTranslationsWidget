<?php

namespace Ladela\PersonalTranslationsWidgetBundle\Twig\Helper;

use Doctrine\ORM\EntityManager;
use Gedmo\Translatable\TranslatableListener;
use Ladela\PersonalTranslationsWidgetBundle\TranslationsGetter\TranslationsGetterInterface;

/**
 * Class TranslationsHelper
 *
 * @package Ladela\PersonalTranslationsWidgetBundle\Twig\Helper
 */
class TranslationsHelper
{
    /** @var TranslatableListener */
    protected $gedmoTranslatableListener;
    /** @var EntityManager */
    protected $em;
    /** @var array */
    protected $languages = array();

    /**
     * Object constructor
     *
     * @param TranslatableListener $gedmoTranslatableListener
     * @param EntityManager        $em
     */
    public function __construct(TranslatableListener $gedmoTranslatableListener, EntityManager $em)
    {
        $this->gedmoTranslatableListener = $gedmoTranslatableListener;
        $this->em = $em;
    }

    /**
     * Sets languages getter
     *
     * @param TranslationsGetterInterface $getter
     */
    public function setLanguagesGetter(TranslationsGetterInterface $getter)
    {
        $this->languages = $getter->getLanguages();
    }

    /**
     * Sets languages
     *
     * @param array $languages
     */
    public function setLanguages(array $languages)
    {
        $this->languages = $languages;
    }

    /**
     * Gets entity available fields
     *
     * @param $entity
     *
     * @return array
     * @throws NoTranslationsDefinedException
     */
    public function getEntityTranslatableFields($entity)
    {
        $translatable_config = $this->gedmoTranslatableListener->getConfiguration($this->em, get_class($entity));

        $fields = @$translatable_config['fields'];
        if (!is_array($fields) || count($fields) == 0) {
            throw new NoTranslationsDefinedException('No translations defined');
        }

        return $translatable_config['fields'];
    }

    /**
     * Gets available languages for translation
     *
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * Gets Gedmo default locale
     *
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->gedmoTranslatableListener->getDefaultLocale();
    }
}
