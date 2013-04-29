<?php

namespace Ladela\PersonalTranslationsWidgetBundle\Form\Subscriber;

use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormError;

class AddTranslatedFieldSubscriber implements EventSubscriberInterface
{
  private $factory;
  private $options;
  private $container;

  public function __construct(FormFactoryInterface $factory, ContainerInterface $container, Array $options)
  {
    $this->factory   = $factory;
    $this->options   = $options;
    $this->container = $container;
  }

  public static function getSubscribedEvents()
  {
    // Tells the dispatcher that we want to listen on the form.pre_set_data
    // , form.post_data and form.bind_norm_data event
    return array(
      FormEvents::PRE_SET_DATA => 'preSetData',
      FormEvents::POST_BIND    => 'postBind',
      FormEvents::BIND         => 'bindNormData'
    );
  }

  private function bindTranslations($data)
  {
    //Small helper function to extract all Personal Translation
    //from the Entity for the field we are interested in
    //and combines it with the fields

    $collection            = array();
    $availableTranslations = array();

    foreach ($data as $Translation) {
      foreach ($this->options['fields'] as $field)
        if (strtolower($Translation->getField()) == strtolower($field)) {
          $availableTranslations[strtolower($Translation->getLocale())][$field] = $Translation;
        }
    }

    foreach ($this->getFieldNames() as $locale => $fields) {
      foreach ($fields as $field_key => $field_name) {
        if (isset($availableTranslations[strtolower($locale)]) && isset($availableTranslations[strtolower($locale)][$field_key])) {
          $Translation = $availableTranslations[strtolower($locale)][$field_key];
        } else {
          $Translation = $this->createPersonalTranslation($locale, $field_key, NULL);
        }

        $collection[] = array(
          'locale'      => $locale,
          'fieldName'   => $field_name,
          'fieldKey'    => $field_key,
          'translation' => $Translation,
        );
      }
    }

    return $collection;
  }

  private function getFieldNames()
  {
    //helper function to generate all field names in format:
    // '<locale>' => '<field>|<locale>'
    $collection = array();

    foreach ($this->options['locales'] as $key => $locale) {
      if (is_numeric($key)) $key = $locale;
      foreach ($this->options['fields'] as $field)
        $collection[$key][$field] = $field . ":" . $key;
    }

    return $collection;
  }

  private function createPersonalTranslation($locale, $field, $content)
  {
    //creates a new Personal Translation
    $className = $this->options['personal_translation'];

    return new $className($locale, $field, $content);
  }

  public function bindNormData(FormEvent $event)
  {
    //Validates the submitted form
    $form = $event->getForm();

    $validator = $this->container->get('validator');

    foreach ($this->getFieldNames() as $locale => $fieldNames) {
      foreach ($fieldNames as $form_field_name) {
        $content   = $form->get($form_field_name)->getData();
        $fieldName = preg_replace('/:.+/', '', $form_field_name);

        if (
          NULL === $content &&
          in_array($locale, $this->options['required_locale']) &&
          @$this->options[$fieldName]['required']
        ) {
          $form->addError(new FormError(sprintf("Field '%s' for locale '%s' cannot be blank", $fieldName, $locale)));
        } else {
          $translation = $this->createPersonalTranslation($locale, $fieldName, $content);
          $errors      = $validator->validate($translation, array(sprintf("%s:%s", $fieldName, $locale)));

          if (count($errors) > 0) {
            foreach ($errors as $error) {
              $form->addError(new FormError($error->getMessage()));
            }
          }
        }
      }
    }
  }

  public function postBind(FormEvent $event)
  {
    //if the form passed the validattion then set the corresponding Personal Translations
    $form = $event->getForm();
    $data = $form->getData();

    $entity = $form->getParent()->getData();

    foreach ($this->bindTranslations($data) as $bound) {
      $content     = $form->get($bound['fieldName'])->getData();
      $translation = $bound['translation'];

      // set the submitted content
      $translation->setContent($content);

      //test if its new
      if ($translation->getId()) {
        //Delete the Personal Translation if its empty
        if (
          NULL === $content &&
          $this->options['remove_empty']
        ) {
          $data->removeElement($translation);

          if ($this->options['entity_manager_removal']) {
            $this->container->get('doctrine.orm.entity_manager')->remove($translation);
          }
        }
      } elseif (NULL !== $content) {
        //add it to entity
        $entity->addTranslation($translation);

        if (!$data->contains($translation)) {
          $data->add($translation);
        }
      }
    }
  }

  public function preSetData(FormEvent $event)
  {
    //Builds the custom 'form' based on the provided locales
    $data = $event->getData();
    $form = $event->getForm();

    // During form creation setData() is called with null as an argument
    // by the FormBuilder constructor. We're only concerned with when
    // setData is called with an actual Entity object in it (whether new,
    // or fetched with Doctrine). This if statement let's us skip right
    // over the null condition.
    if (null === $data) {
      return;
    }

    foreach ($this->bindTranslations($data) as $bound) {
      $field_key = $bound['fieldKey'];

      $form->add($this->factory->createNamed(
        $bound['fieldName'],
        is_string($this->options['widgets']) ? $this->options['widgets'] : @$this->options['widgets'][$field_key],
        $bound['translation']->getContent(),
        array_merge(array(
          'label'    => $bound['fieldKey'] . ':' . $bound['locale'],
          'required' => in_array($bound['locale'], $this->options['required_locale']),
          'mapped'   => false,
        ), (@$this->options['field_options'][$field_key] ? : array()))
      ));
    }
  }
}