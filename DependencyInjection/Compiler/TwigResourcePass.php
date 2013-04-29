<?php

namespace Ladela\PersonalTranslationsWidgetBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;

class TwigResourcePass implements CompilerPassInterface
{
  public function process(ContainerBuilder $container)
  {
    $res = "PersonalTranslationsWidgetBundle:Form:translatable_field_widget.html.twig";
    if (!$container->hasParameter('twig.form.resources'))
    {
      $container->setParameter('twig.form.resources', array($res));
    }
    else {
      $resources = $container->getParameter('twig.form.resources');
      $resources[] = $res;
      $container->setParameter('twig.form.resources', $resources);
    }
  }
}