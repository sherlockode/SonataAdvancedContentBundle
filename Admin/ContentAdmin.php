<?php

namespace Sherlockode\SonataAdvancedContentBundle\Admin;

use Sherlockode\AdvancedContentBundle\Form\Type\ContentType;
use Sherlockode\AdvancedContentBundle\Model\ContentInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormBuilderInterface;

class ContentAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'admin_afb_content';

    protected function configure(): void
    {
        $this->setFormTheme(array_merge(
            parent::getFormTheme(),
            ['@SherlockodeAdvancedContent/Form/content.html.twig']
        ));
    }

    public function configureFormFields(FormMapper $form): void
    {
        $form->tab('content.form.tabs.label')
            ->with('content.form.tabs.general', [
                'fields' => [
                    'name' => 'name',
                    'slug' => 'slug',
                    'locale' => 'locale',
                ],
            ])
            ->end()
            ->with('content.form.tabs.fields', [
                'fields' => [
                    'fieldValues' => 'fieldValues',
                ],
                'box_class' => 'box box-primary box-content-field-values'
            ])
            ->end()
            ->end(); // init the groups data for this admin class
    }

    public function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => 'content.id'])
            ->add('name', null, ['label' => 'content.form.name'])
            ->add('_action', 'actions', [
                'actions' => [
                    'edit'   => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /**
     * @param ProxyQueryInterface $query
     *
     * @return ProxyQueryInterface
     */
    public function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);
        $query->getQueryBuilder()
            ->where('o.page IS NULL');

        return $query;
    }

    /**
     * @param $object
     *
     * @return string
     */
    public function toString($object): string
    {
        if ($object instanceof ContentInterface && $object->getName()) {
            return $object->getName();
        }

        return parent::toString($object);
    }
}
