<?php
/**
 * Created by PhpStorm.
 * User: rodger
 * Date: 12.05.14
 * Time: 15:06
 */

namespace Ladela\PersonalTranslationsWidgetBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TranslationsGetterPass
 *
 * @package Ladela\PersonalTranslationsWidgetBundle\DependencyInjection\Compiler
 */
class TranslationsGetterPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $languagesGetters = $container->findTaggedServiceIds('languages_getter');
        $helperDefinition = $container->getDefinition('twig.translations.ladela_translations_helper');
        if (count($languagesGetters)) {

            $keys   = array_keys($languagesGetters);
            $getter = reset($keys);

            $helperDefinition->addMethodCall('setLanguagesGetter', array(new Reference($getter)));
        } else {
            $languages = $container->getParameter('ladela_personal_translations.languages', array());
            $helperDefinition->addMethodCall('setLanguagesGetter', array($languages));
        }
    }
}