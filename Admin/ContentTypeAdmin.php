<?php

namespace Sherlockode\SonataAdvancedContentBundle\Admin;

use Sherlockode\AdvancedContentBundle\Form\Type\ContentTypeFormType;
use Sherlockode\AdvancedContentBundle\Form\Type\FieldsType;
use Sherlockode\AdvancedContentBundle\Manager\ConfigurationManager;
use Sherlockode\AdvancedContentBundle\Manager\ContentTypeManager;
use Sherlockode\AdvancedContentBundle\Model\ContentTypeInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ContentTypeAdmin extends AbstractAdmin
{
    /**
     * @var ContentTypeManager
     */
    private $contentTypeManager;

    /**
     * @var array
     */
    private $fieldTypes = [];

    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    /**
     * @param ContentTypeManager $manager
     */
    public function setContentTypeManager(ContentTypeManager $manager)
    {
        $this->contentTypeManager = $manager;
    }

    /**
     * @param ConfigurationManager $configurationManager
     */
    public function setConfigurationManager(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
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
        // We need to declare each form field through the form mapper to ensure they are displayed correctly
        $form
            ->add('name')
            ->add('slug')
            ->add('linkType', ChoiceType::class, [
                'mapped' => false,
            ])
            ->add('pageType')
            ->add('page')
            ->add('allowSeveralContents')
        ;
        if ($this->getSubject()->getId()) {
            // We need to declare each form field through the form mapper to ensure they are displayed correctly
            $form->add('fields', FieldsType::class, [
                'label' => 'content_type.form.fields',
                'translation_domain' => 'AdvancedContentBundle',
                'type_choices' => [],
            ]);

            $fieldTypes = [];
            foreach ($this->getSubject()->getFields() as $field) {
                $fieldTypes[$field->getId()] = $field->getType();
            }
            $this->fieldTypes = $fieldTypes;
        }
    }

    /**
     * Create ContentTypeFormType form
     *
     * @return mixed
     */
    private function getContentTypeFormBuilder()
    {
        $this->formOptions['contentType'] = $this->getSubject();

        return $this->getFormContractor()->getFormFactory()
            ->createNamedBuilder($this->getUniqid(), ContentTypeFormType::class, null, $this->formOptions);
    }

    public function getFormBuilder()
    {
        $this->formOptions['data_class'] = $this->getClass();

        $formBuilder = $this->getContentTypeFormBuilder();
        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }

    public function defineFormBuilder(FormBuilderInterface $formBuilder)
    {
        $formBuilder = $this->getContentTypeFormBuilder();
        parent::defineFormBuilder($formBuilder);
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('name', null, ['label' => 'content_type.form.name'])
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

    /**
     * @param $object
     *
     * @return string
     */
    public function toString($object)
    {
        if ($object instanceof ContentTypeInterface && $object->getName()) {
            return $object->getName();
        }

        return parent::toString($object);
    }
}
