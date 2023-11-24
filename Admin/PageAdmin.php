<?php

namespace Sherlockode\SonataAdvancedContentBundle\Admin;

use Sherlockode\AdvancedContentBundle\Form\Type\PageType;
use Sherlockode\AdvancedContentBundle\Locale\LocaleProviderInterface;
use Sherlockode\AdvancedContentBundle\Model\PageInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormBuilderInterface;

class PageAdmin extends AbstractAdmin
{
    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;

    protected function configure(): void
    {
        $this->setFormTheme(array_merge(
            parent::getFormTheme(),
            ['@SherlockodeAdvancedContent/Form/content.html.twig']
        ));
    }

    public function configureFormFields(FormMapper $form): void
    {
        $form->tab('page.form.tabs.label')
            ->with('page.form.tabs.general')->end(); // init the groups data for this admin class

        $fields = [
            'pageIdentifier' => 'pageIdentifier',
            'pageType' => 'pageType',
        ];
        if ($this->getSubject()->getId()) {
            $fields = array_merge($fields, [
                'status' => 'status',
            ]);
        }

        $groups = $this->getFormGroups();
        $groups['page.form.tabs.label.page.form.tabs.general']['fields'] = $fields;
        $this->setFormGroups($groups);

        $form->with('page.form.tabs.meta')->end();
        if ($this->localeProvider->isMultilangEnabled()) {
            $fields = ['pageMetas' => 'pageMetas'];
        } else {
            $fields = ['pageMeta' => 'pageMeta'];
        }
        $groups = $this->getFormGroups();
        $groups['page.form.tabs.label.page.form.tabs.meta']['fields'] = $fields;
        $this->setFormGroups($groups);

        if ($this->getSubject()->getId()) {
            $form->with('page.form.tabs.content')->end();
            if ($this->localeProvider->isMultilangEnabled()) {
                $fields = ['contents' => 'contents'];
            } else {
                $fields = ['content' => 'content'];
            }
            $groups = $this->getFormGroups();
            $groups['page.form.tabs.label.page.form.tabs.content']['fields'] = $fields;
            $groups['page.form.tabs.label.page.form.tabs.content']['box_class'] = 'box box-primary box-content-field-values';
            $this->setFormGroups($groups);
        }
        $form->end();
    }

    public function configureListFields(ListMapper $list): void
    {
        $list
            ->add('pageIdentifier', null, ['label' => 'page.form.page_identifier'])
            ->add('title', null, [
                'label' => 'page.form.title',
                'template' => '@SherlockodeSonataAdvancedContent/Page/title.html.twig',
                'virtual_field' => true,
            ])
            ->add('status', null, [
                'label' => 'page.form.status',
                'template' => '@SherlockodeSonataAdvancedContent/Page/status.html.twig'
            ])
            ->add('_action', 'actions', [
                'actions' => [
                    'edit'   => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /**
     * @param $object
     *
     * @return string
     */
    public function toString($object): string
    {
        if ($object instanceof PageInterface && $object->getPageIdentifier()) {
            return $object->getPageIdentifier();
        }

        return parent::toString($object);
    }

    /**
     * @param LocaleProviderInterface $localeProvider
     */
    public function setLocaleProvider(LocaleProviderInterface $localeProvider)
    {
        $this->localeProvider = $localeProvider;
    }
}
