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

config.yml
~~~~~~~~~~
personal_translations_widget:
  getter: your-languages-getter-service-id
~~~~~~~~~~
  the service class must implement TranslationsGetterInterface and return array of language-key => label pairs

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
  languages: ['en', 'de']
~~~~~~~~~~
