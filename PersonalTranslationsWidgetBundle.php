<?php
namespace Ladela\PersonalTranslationsWidgetBundle;

use Ladela\PersonalTranslationsWidgetBundle\DependencyInjection\Compiler\TranslationsGetterPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ladela\PersonalTranslationsWidgetBundle\DependencyInjection\Compiler\GedmoTranslationsPass;
use Ladela\PersonalTranslationsWidgetBundle\DependencyInjection\Compiler\TwigResourcePass;

/**
 * Class PersonalTranslationsWidgetBundle
 *
 * @package Ladela\PersonalTranslationsWidgetBundle
 */
class PersonalTranslationsWidgetBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new GedmoTranslationsPass());
        $container->addCompilerPass(new TwigResourcePass());
        $container->addCompilerPass(new TranslationsGetterPass());
    }
}
