<?php

namespace Ladela\PersonalTranslationsWidgetBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ladela\PersonalTranslationsWidgetBundle\Twig\Helper\TranslationsHelper;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Ladela\PersonalTranslationsWidgetBundle\Form\Subscriber\AddTranslatedFieldGroupSubscriber;

/**
 * Class TranslatableFieldGroupType
 *
 * @package Ladela\PersonalTranslationsWidgetBundle\Form\Type
 */
class TranslatableFieldGroupType extends AbstractType
{
    protected $container, $helper;

    /**
     * Constructor
     *
     * @param AddTranslatedFieldGroupSubscriber $subscriber
     * @param TranslationsHelper           $helper
     */
    public function __construct(AddTranslatedFieldGroupSubscriber $subscriber, TranslationsHelper $helper)
    {
        $this->subscriber = $subscriber;
        $this->helper = $helper;
    }

    /**
     * Builds form
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['fields']) {
            Throw new \InvalidArgumentException("You should provide a field to translate");
        }

        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'remove_empty' => true,
                'csrf_protection' => false,
                'personal_translation' => false,
                'locales' => $this->helper->getLanguages(),
                'required_locale' => array('en'),
                'fields' => false,
                'widgets' => 'text',
                'field_options' => array(),
                'entity_manager_removal' => true,
                'object' => null,
                'required' => false
            )
        );
    }

    /**
     * Gets widget name
     *
     * @return string
     */
    public function getName()
    {
        return 'translatable_group';
    }
}
