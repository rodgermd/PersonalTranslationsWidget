<?php

namespace Ladela\PersonalTranslationsWidgetBundle\Twig;

use Twig_Extension;
use Twig_Filter_Method;
use Twig_Function_Method;

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
      'get_languages'                  => new Twig_Function_Method($this, 'getLanguages'),
      'get_entity_translatable_fields' => new Twig_Function_Method($this, 'getEntityTranslatableFields'),
    );
  }

  /**
   * Gets available languages
   * @return array
   */
  public function getLanguages()
  {
    return $this->helper->getLanguages();
  }

  /**
   * Gets entity fields available for translation
   * @param $entity
   * @return array
   */
  public function getEntityTranslatableFields($entity)
  {
    return $this->helper->getEntityTranslatableFields($entity);
  }

  /**
   * Gets extension name
   * @return string
   */
  public function getName()
  {
    return 'translations_twig_extension';
  }
}