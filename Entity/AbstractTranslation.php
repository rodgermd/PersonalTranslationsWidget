<?php
/**
 * Created by PhpStorm.
 * User: rodger
 * Date: 12.05.14
 * Time: 14:14
 */

namespace Ladela\PersonalTranslationsWidgetBundle\Entity;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * Abstract class for entity translation
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractTranslation extends AbstractPersonalTranslation
{
    /**
     * Convinient constructor
     *
     * @param string $locale
     * @param string $field
     * @param string $value
     */
    public function __construct($locale, $field, $value = null)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }

    /** @var AbstractTranslatedEntity  */
    protected $object;

    /**
     * @ORM\PrePersist
     */
    public function updateParentFields()
    {
        if ($this->object->getLocale() == $this->locale) {
            $method = Inflector::camelize('set' . ucfirst($this->field));
            if (method_exists($this->object, $method)) {
                $this->object->$method($this->getContent());
            }
        }
    }
}
