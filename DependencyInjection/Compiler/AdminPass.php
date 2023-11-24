<?php

namespace Sherlockode\SonataAdvancedContentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AdminPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $mapping = $container->getParameter('sherlockode_advanced_content.entity_class_mapping');

        $tag = $container->getDefinition('sherlockode_advanced_content.admin.content')->getTag('sonata.admin');
        $tag = $tag[0] + ['model_class' => $mapping['content']];

        $container
            ->getDefinition('sherlockode_advanced_content.admin.content')
            ->setTags(['sonata.admin' => [$tag]])
            ->addMethodCall('setFormContractor', [$container->getDefinition('sherlockode_advanced_content.form_contractor.content')])
        ;

        $tag = $container->getDefinition('sherlockode_advanced_content.admin.page')->getTag('sonata.admin');
        $tag = $tag[0] + ['model_class' => $mapping['page']];

        $container
            ->getDefinition('sherlockode_advanced_content.admin.page')
            ->setTags(['sonata.admin' => [$tag]])
            ->addMethodCall('setFormContractor', [$container->getDefinition('sherlockode_advanced_content.form_contractor.page')])
        ;

        $tag = $container->getDefinition('sherlockode_advanced_content.admin.page_type')->getTag('sonata.admin');
        $tag = $tag[0] + ['model_class' => $mapping['page_type']];

        $container
            ->getDefinition('sherlockode_advanced_content.admin.page_type')
            ->setTags(['sonata.admin' => [$tag]])
            ->addMethodCall('setFormContractor', [$container->getDefinition('sherlockode_advanced_content.form_contractor.page_type')])
        ;
    }
}
