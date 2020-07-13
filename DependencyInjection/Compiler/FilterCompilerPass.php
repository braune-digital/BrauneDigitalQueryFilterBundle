<?php
namespace BrauneDigital\QueryFilterBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FilterCompilerPass implements CompilerPassInterface {

    /**
     * Collect all filter tagged services and load them into the query manager
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('BrauneDigital\QueryFilterBundle\Service\QueryManager')) {
            return;
        }

        $definition = $container->findDefinition(
            'BrauneDigital\QueryFilterBundle\Service\QueryManager'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'bd_query_filter.filter'
        );

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    'addFilter',
                    array(new Reference($id), $attributes["alias"])
                );
            }
        }
    }
}