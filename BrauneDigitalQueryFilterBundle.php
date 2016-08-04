<?php

namespace BrauneDigital\QueryFilterBundle;

use BrauneDigital\QueryFilterBundle\DependencyInjection\Compiler\FilterCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BrauneDigitalQueryFilterBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new FilterCompilerPass());
    }
}
