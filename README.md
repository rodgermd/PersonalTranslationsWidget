Installation
----------

Add to composer.json:
~~~~~~~~~~~~~~~~~~~~
"ladela/personal-translations-widget-bundle" : "dev-master"
~~~~~~~~~~~~~~~~~~~~

Add to AppKernel.php:
```
new Ladela\PersonalTranslationsWidgetBundle\PersonalTranslationsWidgetBundle(),
```

Copy or symlink file:
~~~~~~~~~~~~~~~~~~~~
Resources/views/Form/translatable_field_widget.html.twig located in the bundle directory
~~~~

to
~~~~
/app/Resources/views/translatable_field_widget.html.twig locate din the project root
~~~~~~~~~~~~~~~~~~~~


Languages configuration
---------

Getter service

The service should have a tag *languages_getter* and return array of languages: code = > name


```
  tags:
    - { name: languages_getter }
```

```
use Ladela\PersonalTranslationsWidgetBundle\TranslationsGetter\TranslationsGetterInterface;

class LanguagesGetter implements TranslationsGetterInterface
{
  public function getLanguages()
  {
    return array('en' => 'English', 'de' => 'Deutsch');
  }
}
```

or use languages as array:

~~~~~~~~~~
personal_translations_widget:
  languages:
        en: English
        es: Spanish
~~~~~~~~~~

Sonata Admin sample
---

~~~
protected function configureFormFields(FormMapper $form)
{
  $subject = $form->getAdmin()->getSubject();
  $form
    ->add('translations', 'translatable_field', array(
    'personal_translation' => 'Site\BaseBundle\Entity\AccommodationTranslation',
    'fields' => array('title', 'description', 'secondary_text'),
    'widgets' => array('title' => 'text', 'description' => 'textarea', 'secondary_text' => 'textarea'),
    'field_options'  => array('secondary_text' => array('attr' => array('class' => 'text-field')))
  ))
}
~~~
