<?php

namespace Ladela\PersonalTranslationsWidgetBundle\Twig\Helper;

use Doctrine\ORM\EntityManager;
use Gedmo\Translatable\TranslatableListener;
use Ladela\PersonalTranslationsWidgetBundle\TranslationsGetter\TranslationsGetterInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TranslationsHelper
 *
 * @package Ladela\PersonalTranslationsWidgetBundle\Twig\Helper
 */
class TranslationsHelper implements ContainerAwareInterface
{
    /** @var TranslatableListener */
    protected $gedmo_translatable_listener;
    /** @var EntityManager */
    protected $em;
    /** @var array */
    protected $languages = array();

    /**
     * Object constructor
     *
     * @param TranslatableListener $gedmo_translatable_listener
     * @param EntityManager        $em
     */
    public function __construct(TranslatableListener $gedmo_translatable_listener, EntityManager $em)
    {
        $this->gedmo_translatable_listener = $gedmo_translatable_listener;
        $this->em                          = $em;
    }

    /**
     * Sets container
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $languages = $container->getParameter('ladela_personal_translations.languages');
        $getter    = $container->getParameter('ladela_personal_translations.getter');

        if (!empty($languages)) {
            $this->languages = $languages;
        } elseif (!empty($getter)) {
            $getter = $container->get($getter);
            if ($getter instanceof TranslationsGetterInterface) {
                $this->languages = $getter->getLanguages();
            }
        }
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
        $translatable_config = $this->gedmo_translatable_listener->getConfiguration($this->em, get_class($entity));

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
}