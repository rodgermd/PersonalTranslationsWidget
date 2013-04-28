<?php
namespace Ladela\PersonalTranslationsWidgetBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ladela\PersonalTranslationsWidgetBundle\DependencyInjection\Compiler\GedmoTranslationsPass;
use Ladela\PersonalTranslationsWidgetBundle\DependencyInjection\Compiler\TwigResourcePass;

class PersonalTranslationsWidgetBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);

    $container->addCompilerPass(new GedmoTranslationsPass());
  }
}