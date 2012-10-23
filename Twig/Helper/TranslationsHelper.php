<?php

namespace Ladela\PersonalTranslationsWidgetBundle\Twig\Helper;

use Gedmo\Translatable\TranslatableListener;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\DependencyInjection\Container;
use Ladela\PersonalTranslationsWidgetBundle\TranslationsGetter\TranslationsGetterInterface;

class TranslationsHelper extends \Symfony\Component\DependencyInjection\ContainerAware
{
  protected $gedmo_translatable_listener;
  protected $doctrine;
  protected $languages = array();

  public function __construct(TranslatableListener $gedmo_translatable_listener, Registry $doctrine, Container $container)
  {
    $this->gedmo_translatable_listener = $gedmo_translatable_listener;
    $this->doctrine                    = $doctrine;

    $languages = $container->getParameter('ladela_personal_translations.languages');
    $getter = $container->getParameter('ladela_personal_translations.getter');

    if (!empty($languages))
    {
      $this->languages = $languages;
    }
    elseif(!empty($getter))
    {
      $getter = $container->get($getter);
      if ($getter instanceof TranslationsGetterInterface)
      {
        $this->languages = $getter->getLanguages();
      }
    }
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
    return $this->languages;
  }
}