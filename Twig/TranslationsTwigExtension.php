<?php

namespace Ladela\PersonalTranslationsWidgetBundle\Twig;

use Symfony\Component\Form\FormView;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

use Ladela\PersonalTranslationsWidgetBundle\Twig\Helper\TranslationsHelper;

class TranslationsTwigExtension extends Twig_Extension
{
    protected $helper;

    public function __construct(TranslationsHelper $helper)
    {
        $this->helper = $helper;
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('get_languages', array($this, 'getLanguages')),
            new Twig_SimpleFunction('get_entity_translatable_fields', array($this, 'getEntityTranslatableFields')),
        );
    }

    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('translation_fields', array($this, 'getTranslationFields'))
        );
    }

    /**
     * Gets available languages
     *
     * @return array
     */
    public function getLanguages()
    {
        return $this->helper->getLanguages();
    }

    /**
     * Gets entity fields available for translation
     *
     * @param $entity
     *
     * @return array
     */
    public function getEntityTranslatableFields($entity)
    {
        return $this->helper->getEntityTranslatableFields($entity);
    }

    public function getTranslationFields(FormView $form)
    {
        $fields = array();
        foreach(array_keys($form->children) as $key) {
            preg_match('#(?<key>.*):(.*)#', $key, $matches);
            $fields[] = $matches['key'];
        }
        return array_unique($fields);
    }

    /**
     * Gets extension name
     *
     * @return string
     */
    public function getName()
    {
        return 'translations_twig_extension';
    }


}