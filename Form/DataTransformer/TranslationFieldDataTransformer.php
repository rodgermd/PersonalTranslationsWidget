<?php
/**
 * Created by PhpStorm.
 * User: rodger
 * Date: 12/9/14
 * Time: 10:04 PM
 */
namespace Ladela\PersonalTranslationsWidgetBundle\Form\DataTransformer;

use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Ladela\PersonalTranslationsWidgetBundle\Entity\TranslatedPersonalEntityInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class TranslationFieldDataTranformer
 *
 * @package Ladela\PersonalTranslationsWidgetBundle
 */
class TranslationFieldDataTransformer implements DataTransformerInterface
{
    /** @var TranslatedPersonalEntityInterface */
    protected $object;
    /** @var string */
    protected $field;
    /** @var array */
    protected $cultures = array();
    /** @var string */
    protected $defaultCulture;

    /**
     * Sets field name
     *
     * @param string $field
     *
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Sets related object
     *
     * @param TranslatedPersonalEntityInterface $object
     *
     * @return $this
     */
    public function setObject(TranslatedPersonalEntityInterface $object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Sets cultures
     *
     * @param array $cultures
     *
     * @return $this
     */
    public function setCultures(array $cultures)
    {
        $this->cultures = $cultures;

        return $this;
    }

    /**
     * Sets default culture (to return reverse transformed value)
     *
     * @param string $defaultCulture
     *
     * @return $this
     */
    public function setDefaultCulture($defaultCulture)
    {
        $this->defaultCulture = $defaultCulture;

        return $this;
    }

    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     * This method is called on two occasions inside a form field:
     *
     * 1. When the form field is initialized with the data attached from the datasource (object or array).
     * 2. When data from a request is submitted using {@link Form::submit()} to transform the new input data
     *    back into the renderable format. For example if you have a date field and submit '2009-10-10'
     *    you might accept this value because its easily parsed, but the transformer still writes back
     *    "2009/10/10" onto the form field (for further displaying or other purposes).
     *
     * This method must be able to deal with empty values. Usually this will
     * be NULL, but depending on your implementation other empty values are
     * possible as well (such as empty strings). The reasoning behind this is
     * that value transformers must be chainable. If the transform() method
     * of the first value transformer outputs NULL, the second value transformer
     * must be able to process that value.
     *
     * By convention, transform() should return an empty string if NULL is
     * passed.
     *
     * @param mixed $value The value in the original representation
     *
     * @return mixed The value in the transformed representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function transform($value)
    {
        if (!$this->object) {
            return $value;
        }
        $value = $this->prepareEmptyCulturesArray();

        array_map(
            function (AbstractPersonalTranslation $translation) use (&$value) {
                if ($translation->getField() == $this->field && array_key_exists($translation->getLocale(), $value)) {
                    $value[$translation->getLocale()] = $translation->getContent();
                }
            },
            $this->getFieldTranslations()
        );

        return $value;
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     *
     * This method is called when {@link Form::submit()} is called to transform the requests tainted data
     * into an acceptable format for your data processing/model layer.
     *
     * This method must be able to deal with empty values. Usually this will
     * be an empty string, but depending on your implementation other empty
     * values are possible as well (such as empty strings). The reasoning behind
     * this is that value transformers must be chainable. If the
     * reverseTransform() method of the first value transformer outputs an
     * empty string, the second value transformer must be able to process that
     * value.
     *
     * By convention, reverseTransform() should return NULL if an empty string
     * is passed.
     *
     * @param mixed $value The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function reverseTransform($array)
    {
        $translations = $this->prepareTranslationsArray();
        foreach ($array as $key => $value) {
            if (array_key_exists($key, $translations)) {
                $translations[$key]->setContent($value);
            }
        }

        return @$array[$this->defaultCulture] ?: reset($array); // return default culture value or first arrived value
    }

    /**
     * Gets object translation of the current field
     *
     * @return AbstractPersonalTranslation[]
     */
    public function getFieldTranslations()
    {
        return array_filter(
            $this->object->getTranslations()->toArray(),
            function (AbstractPersonalTranslation $translation) {
                return $translation->getField() == $this->field;
            }
        );
    }

    /**
     * Gets array locale => translation
     *
     * @return AbstractPersonalTranslation[]
     */
    public function prepareTranslationsArray()
    {
        $result = array();
        array_map(
            function (AbstractPersonalTranslation $translation) use (&$result) {
                $result[$translation->getLocale()] = $translation;
            },
            $this->getFieldTranslations()
        );

        $class = $this->object->getTranslationClass();
        foreach ($this->cultures as $culture) {
            if (!array_key_exists($culture, $result)) {
                /** @var AbstractPersonalTranslation $translation */
                $translation = new $class($culture, $this->field);
                $this->object->addTranslation($translation);
                $result[$culture] = $translation;
            }
        }

        return $result;
    }

    /**
     * Gets array with keys as cultures, empty values
     *
     * @return array
     */
    protected function prepareEmptyCulturesArray()
    {
        $result = array();
        foreach ($this->cultures as $culture) {
            $result[$culture] = '';
        }

        return $result;
    }
}