<?php

namespace Ladela\PersonalTranslationsWidgetBundle\Form\Subscriber;

use Doctrine\ORM\EntityManager;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Ladela\PersonalTranslationsWidgetBundle\Entity\AbstractTranslation;
use Ladela\PersonalTranslationsWidgetBundle\Entity\TranslatedPersonalEntityInterface;
use Ladela\PersonalTranslationsWidgetBundle\Twig\Helper\TranslationsHelper;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class AddTranslatedFieldGroupSubscriber
 *
 * @package Ladela\PersonalTranslationsWidgetBundle\Form\Subscriber
 */
class AddTranslatedFieldGroupSubscriber implements EventSubscriberInterface
{
    /** @var FormFactoryInterface */
    private $factory;
    /** @var array */
    private $options;
    /** @var ValidatorInterface */
    private $validator;
    /** @var EntityManager */
    private $em;
    /** @var TranslationsHelper */
    protected $translationsHelper;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param EntityManager        $em
     * @param ValidatorInterface   $validator
     * @param TranslationsHelper   $translationsHelper
     */
    public function __construct(
        FormFactoryInterface $factory,
        EntityManager $em,
        ValidatorInterface $validator,
        TranslationsHelper $translationsHelper
    ) {
        $this->factory = $factory;
        $this->em = $em;
        $this->validator = $validator;
        $this->translationsHelper = $translationsHelper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that we want to listen on the form.pre_set_data
        // , form.post_data and form.bind_norm_data event
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_SUBMIT => 'postBind',
            FormEvents::SUBMIT => 'bindNormData'
        );
    }

    /**
     * Binds translations
     *
     * @param $data
     *
     * @return array
     */
    private function bindTranslations($data)
    {
        //Small helper function to extract all Personal Translation
        //from the Entity for the field we are interested in
        //and combines it with the fields

        $collection = array();
        $availableTranslations = array();

        foreach ($data as $translation) {
            /** @var AbstractPersonalTranslation $translation */
            foreach ($this->options['fields'] as $field) {
                if (strtolower($translation->getField()) == strtolower($field)) {
                    $availableTranslations[strtolower($translation->getLocale())][$field] = $translation;
                }
            }
        }

        foreach ($this->getFieldNames() as $locale => $fields) {
            foreach ($fields as $field_key => $field_name) {
                if (isset($availableTranslations[strtolower($locale)]) && isset($availableTranslations[strtolower(
                            $locale
                        )][$field_key])
                ) {
                    $translation = $availableTranslations[strtolower($locale)][$field_key];
                } else {
                    $translation = $this->createPersonalTranslation($locale, $field_key, null);
                }

                $collection[] = array(
                    'locale' => $locale,
                    'fieldName' => $field_name,
                    'fieldKey' => $field_key,
                    'translation' => $translation,
                );
            }
        }

        return $collection;
    }

    /**
     * Gets field names
     *
     * @return array
     */
    private function getFieldNames()
    {
        //helper function to generate all field names in format:
        // '<locale>' => '<field>|<locale>'
        $collection = array();

        foreach ($this->translationsHelper->getLanguages() as $key => $locale) {
            if (is_numeric($key)) {
                $key = $locale;
            }
            foreach ($this->options['fields'] as $field) {
                $collection[$key][$field] = $field . ":" . $key;
            }
        }

        return $collection;
    }

    /**
     * Creates new translation
     *
     * @param string $locale
     * @param string $field
     * @param string $content
     *
     * @return AbstractPersonalTranslation
     */
    private function createPersonalTranslation($locale, $field, $content)
    {
        //creates a new Personal Translation
        $className = $this->options['personal_translation'];

        return new $className($locale, $field, $content);
    }

    /**
     * On bind data
     *
     * @param FormEvent $event
     */
    public function bindNormData(FormEvent $event)
    {
        //Validates the submitted form
        $form = $event->getForm();
        $this->options = $form->getConfig()->getOptions();

        foreach ($this->getFieldNames() as $locale => $fieldNames) {
            foreach ($fieldNames as $form_field_name) {
                $content = $form->get($form_field_name)->getData();
                $fieldName = preg_replace('/:.+/', '', $form_field_name);

                if (
                    null === $content &&
                    in_array($locale, $this->options['required_locale']) &&
                    @$this->options['field_options'][$fieldName]['required']
                ) {
                    $form->addError(
                        new FormError(sprintf("Field '%s' for locale '%s' cannot be blank", $fieldName, $locale))
                    );
                } else {
                    $translation = $this->createPersonalTranslation($locale, $fieldName, $content);
                    $errors = $this->validator->validate($translation);

                    if ($errors->count()) {
                        foreach ($errors as $error) {
                            $form->addError(new FormError($error->getMessage()));
                        }
                    }
                }
            }
        }
    }

    /**
     * On submit
     *
     * @param FormEvent $event
     */
    public function postBind(FormEvent $event)
    {
        //if the form passed the validation then set the corresponding Personal Translations
        $form = $event->getForm();
        $data = $form->getData();
        $this->options = $form->getConfig()->getOptions();

        $entity = $form->getParent()->getData();

        foreach ($this->bindTranslations($data) as $bound) {
            $content = $form->get($bound['fieldName'])->getData();

            /** @var AbstractTranslation $translation */
            $translation = $bound['translation'];

            // set the submitted content
            $translation->setContent($content);

            //test if its new
            if ($translation->getId()) {
                //Delete the Personal Translation if its empty
                if (null === $content && $this->options['remove_empty']) {
                    $data->removeElement($translation);

                    if ($this->options['entity_manager_removal']) {
                        $this->em->remove($translation);
                    }
                }
            } elseif (null !== $content && $entity) {
                //add it to entity
                $entity->addTranslation($translation);

                if (!$data->contains($translation)) {
                    $data->add($translation);
                }
            }
        }

        $this->updateFields($entity);
    }

    /**
     * On pre set data
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        //Builds the custom 'form' based on the provided locales
        $data = $event->getData();
        if (!$data) {
            return false;
        }
        $form = $event->getForm();
        $this->options = $form->getConfig()->getOptions();

        foreach ($this->bindTranslations($data) as $bound) {
            $field_key = $bound['fieldKey'];

            $form->add(
                $this->factory->createNamed(
                    $bound['fieldName'],
                    is_string(
                        $this->options['widgets']
                    ) ? $this->options['widgets'] : @$this->options['widgets'][$field_key],
                    $bound['translation']->getContent(),
                    array_merge(
                        array(
                            'label' => $bound['fieldKey'] . ':' . $bound['locale'],
                            'required' => in_array($bound['locale'], $this->options['required_locale']),
                            'mapped' => false,
                            'property_path' => 'translation',
                            'auto_initialize' => false,
                        ),
                        (@$this->options['field_options'][$field_key] ?: array())
                    )
                )
            );
        }
    }

    /**
     * Updates base fields using translations
     *
     * @param TranslatedPersonalEntityInterface $entity
     */
    protected function updateFields(TranslatedPersonalEntityInterface $entity)
    {
        $entity->setTranslatableLocale($this->translationsHelper->getDefaultLocale());
        foreach($entity->getTranslations() as $translation)
        {
            /** @var AbstractPersonalTranslation $translation */
            if ($translation->getLocale() == $entity->getLocale())
            {
                $method = 'set' . ucfirst($translation->getField());
                if (method_exists($entity, $method)) {
                    call_user_func(array($entity, $method), $translation->getContent());
                }
            }
        }
    }
}
