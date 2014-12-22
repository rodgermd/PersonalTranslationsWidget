<?php
/**
 * Created by PhpStorm.
 * User: rodger
 * Date: 12/9/14
 * Time: 9:54 PM
 */

namespace Ladela\PersonalTranslationsWidgetBundle\Form\Type;


use Doctrine\ORM\EntityManager;
use Gedmo\Translatable\TranslatableListener;
use Ladela\PersonalTranslationsWidgetBundle\Form\DataTransformer\TranslationFieldDataTransformer;
use Ladela\PersonalTranslationsWidgetBundle\Twig\Helper\TranslationsHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TranslatedFieldType
 *
 * @package Ladela\PersonalTranslationsWidgetBundle\Form\Type
 */
class TranslatedFieldType extends AbstractType
{
    /** @var TranslationsHelper */
    protected $translationsHelper;
    /** @var EntityManager */
    protected $entityManager;
    protected $translatableListener;

    /**
     * Constructor
     *
     * @param TranslationsHelper   $translationsHelper
     * @param EntityManager        $entityManager
     * @param TranslatableListener $translatableListener
     */
    public function __construct(TranslationsHelper $translationsHelper, EntityManager $entityManager, TranslatableListener $translatableListener)
    {
        $this->translationsHelper = $translationsHelper;
        $this->entityManager = $entityManager;
        $this->translatableListener = $translatableListener;
    }

    /**
     * Builds form
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fieldOptions = $options;
        unset($fieldOptions['field_type']);
        foreach ($this->translationsHelper->getLanguages() as $locale => $name) {
            $builder->add(
                $locale,
                $options['field_type'],
                array(
                    'label' => $name,
                    'required' => $locale == $this->translationsHelper->getDefaultLocale() ? $options['required'] : false
                )
            );
        }

        $transformer = new TranslationFieldDataTransformer();
        $transformer
            ->setCultures(array_keys($this->translationsHelper->getLanguages()))
            ->setField($builder->getName())
            ->setDefaultCulture($this->translatableListener->getDefaultLocale());

        // Adds transformer
        $builder->addModelTransformer($transformer);

        // On PreSet data - build translations
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($transformer) {
                $form = $event->getForm();
                $parentForm = $form->getParent();
                if ($parentForm->getData()) {
                    $transformer->setObject($parentForm->getData());
                }
            }, -10 // load later
        );

        // On PostSet data - persist translations
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($transformer) {
                foreach ($transformer->getFieldTranslations() as $translation) {
                    $this->entityManager->persist($translation);
                }
            }, -10 // load later
        );
    }

    /**
     * Adds field_type option
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'field_type' => 'text'
            )
        )->setRequired('field_type');
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'translated_field';
    }
}