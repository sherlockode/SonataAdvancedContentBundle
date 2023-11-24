<?php

namespace Sherlockode\SonataAdvancedContentBundle\Admin\Extension;

use Sherlockode\AdvancedContentBundle\Manager\ConfigurationManager;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;

class PageExtension extends AbstractAdminExtension
{
    /**
     * @var ConfigurationManager
     */
    private $configurationManager;


    public function configureFormFields(FormMapper $formMapper): void
    {
        if ($formMapper->has('pageType')) {
            $formMapper->tab('page.form.tabs.label')
                ->with('page.form.tabs.general');

            $formMapper->add('pageType', ModelType::class, [
                'label' => 'page.form.page_type',
                'class' => $this->configurationManager->getEntityClass('page_type'),
                'property' => 'name',
                'choice_translation_domain' => false,
                'required' => false,
                'attr' => ['class' => 'acb-page-page-type'],
            ], [
                'admin_code' => 'sherlockode_advanced_content.admin.page_type'
            ]);
            $formMapper->end()->end();
        }
    }

    /**
     * @param ConfigurationManager $configurationManager
     */
    public function setConfigurationManager(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }
}
