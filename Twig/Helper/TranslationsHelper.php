<?php

namespace Ladela\PersonalTranslationsWidgetBundle\Twig\Helper;

use Gedmo\Translatable\TranslatableListener;
use Doctrine\Bundle\DoctrineBundle\Registry;

class TranslationsHelper
{
  protected $gedmo_translatable_listener;
  protected $doctrine;

  public function __construct(TranslatableListener $gedmo_translatable_listener, Registry $doctrine)
  {
    $this->gedmo_translatable_listener = $gedmo_translatable_listener;
    $this->doctrine                    = $doctrine;
  }

  /**
   * Gets entity available fields
   * @param $entity
   * @return array
   * @throws NoTranslationsDefinedException
   */
  public function getEntityTranslatableFields($entity)
  {
    $translatable_config = $this->gedmo_translatable_listener->getConfiguration(
      $this->doctrine->getManager(),
      get_class($entity)
    );

    $fields = @$translatable_config['fields'];
    if (!is_array($fields) || count($fields) == 0) throw new NoTranslationsDefinedException('No translations defined');

    return $translatable_config['fields'];
  }

  /**
   * Gets available languages for translation
   * @return array
   */
  public function getLanguages()
  {
    return array('en', 'de');
  }
}