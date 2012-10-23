<?php
namespace Ladela\PersonalTranslationsWidgetBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PersonalTranslationsWidgetBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);

    $gedmo_listener_translatable = $container->getExtension('gedmo.listener.translatable');
    if (!$gedmo_listener_translatable)
    {
      $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
      $loader->load('gedmo_translatable.yml');
    }
  }
}