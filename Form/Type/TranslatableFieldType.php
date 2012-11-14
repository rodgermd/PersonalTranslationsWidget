<?php

namespace Ladela\PersonalTranslationsWidgetBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ladela\PersonalTranslationsWidgetBundle\Twig\Helper\TranslationsHelper;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Ladela\PersonalTranslationsWidgetBundle\Form\Subscriber\AddTranslatedFieldSubscriber;

class TranslatableFieldType extends AbstractType
{
  protected $container, $helper;

  public function __construct(ContainerInterface $container, TranslationsHelper $helper)
  {
    $this->container = $container;
    $this->helper    = $helper;
  }

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    if (!class_exists($options['personal_translation'])) {
      Throw new \InvalidArgumentException(sprintf("Unable to find personal translation class: '%s'", $options['personal_translation']));
    }
    if (!$options['fields']) {
      Throw new \InvalidArgumentException("You should provide a field to translate");
    }

    $subscriber = new AddTranslatedFieldSubscriber($builder->getFormFactory(), $this->container, $options);
    $builder->addEventSubscriber($subscriber);
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    $resolver->replaceDefaults(array(
      'remove_empty'           => true,
      'csrf_protection'        => false,
      'personal_translation'   => false,
      'locales'                => $this->helper->getLanguages(),
      'required_locale'        => array('en'),
      'fields'                 => false,
      'widgets'                => 'text',
      'field_options'          => array(),
      'entity_manager_removal' => true,
      'object'                 => null,
      'required'               => false
    ));
  }

  public function getName()
  {
    return 'translatable_field';
  }
}