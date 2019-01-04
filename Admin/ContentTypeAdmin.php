<?php

namespace Sherlockode\SonataAdvancedContentBundle\Admin;

use Sherlockode\AdvancedContentBundle\Manager\ContentTypeManager;
use Sherlockode\AdvancedContentBundle\Manager\FormBuilderManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ContentTypeAdmin extends AbstractAdmin
{
    /**
     * @var FormBuilderManager
     */
    private $formBuilderManager;

    /**
     * @var ContentTypeManager
     */
    private $contentTypeManager;

    /**
     * @var array
     */
    private $fieldTypes = [];

    public function setFormBuilderManager(FormBuilderManager $manager)
    {
        $this->formBuilderManager = $manager;
    }

    public function setContentTypeManager(ContentTypeManager $manager)
    {
        $this->contentTypeManager = $manager;
    }

    public function getFormTheme()
    {
        return array_merge(
            parent::getFormTheme(),
            ['@SherlockodeAdvancedContent/Form/content_type.html.twig']
        );
    }

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('name', TextType::class, ['label' => 'content_type.name'])
        ;
        if ($this->getSubject()->getId()) {
            $form
                ->add('fields', FormType::class, ['label' => 'content_type.fields']) // dummy call to register the field
            ;
            $this->formBuilderManager->buildContentTypeForm($form->getFormBuilder(), $this->getSubject());

            $fieldTypes = [];
            foreach ($this->getSubject()->getFields() as $field) {
                $fieldTypes[$field->getId()] = $field->getType();
            }
            $this->fieldTypes = $fieldTypes;
        }
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('name', null, ['label' => 'content_type.name'])
            ->add('_action', 'actions', [
                'actions' => [
                    'edit'   => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    public function preUpdate($object)
    {
        $this->contentTypeManager->processFieldsChangeType($object, $this->fieldTypes);
    }
}
