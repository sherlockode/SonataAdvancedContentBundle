<?php

namespace Sherlockode\SonataAdvancedContentBundle;

use Sherlockode\SonataAdvancedContentBundle\DependencyInjection\Compiler\AdminPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SherlockodeSonataAdvancedContentBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AdminPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
    }
}
