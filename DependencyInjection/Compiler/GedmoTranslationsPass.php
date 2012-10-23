<?php

namespace Ladela\PersonalTranslationsWidgetBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class GedmoTranslationsPass implements CompilerPassInterface
{
  public function process(ContainerBuilder $container)
  {
    if (!$container->hasDefinition('gedmo.listener.translatable'))
    {
      $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
      $loader->load('gedmo_translatable.yml');
    }
  }
}