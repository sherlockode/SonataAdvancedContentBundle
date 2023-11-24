<?php

namespace Sherlockode\SonataAdvancedContentBundle\Admin;

use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelReferenceType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRegistryInterface;

class FormContractor implements FormContractorInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var FormRegistryInterface
     */
    protected $formRegistry;

    /**
     * @var string
     */
    protected $formType;

    /**
     * @param FormFactoryInterface  $formFactory
     * @param FormRegistryInterface $formRegistry
     */
    public function __construct(FormFactoryInterface $formFactory, FormRegistryInterface $formRegistry)
    {
        $this->formFactory = $formFactory;
        $this->formRegistry = $formRegistry;
    }

    /**
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return void
     */
    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'standard'));

        if ($fieldDescription->describesAssociation() || null !== $fieldDescription->getOption('admin_code')) {
            $fieldDescription->getAdmin()->attachAdminClass($fieldDescription);
        }
    }

    /**
     * @param string $name
     * @param array  $formOptions
     *
     * @return FormBuilderInterface
     */
    public function getFormBuilder(string $name, array $formOptions = []): FormBuilderInterface
    {
        return $this->formFactory->createNamedBuilder($name, $this->formType, null, $formOptions);
    }

    /**
     * @param string $formType
     *
     * @return $this
     */
    public function setFormType(string $formType): self
    {
        $this->formType = $formType;

        return $this;
    }

    /**
     * @param string|null               $type
     * @param FieldDescriptionInterface $fieldDescription
     * @param array                     $formOptions
     *
     * @return array|mixed[]
     */
    final public function getDefaultOptions(
        ?string $type,
        FieldDescriptionInterface $fieldDescription,
        array $formOptions = []
    ): array {
        $options = [];
        $options['sonata_field_description'] = $fieldDescription;

        if ($this->isAnyInstanceOf($type, [
            ModelType::class,
            ModelListType::class,
            ModelHiddenType::class,
            ModelAutocompleteType::class,
            ModelReferenceType::class,
        ])) {
            $options['class'] = $fieldDescription->getTargetModel();
            $options['model_manager'] = $fieldDescription->getAdmin()->getModelManager();

            if ($this->isAnyInstanceOf($type, [ModelAutocompleteType::class])) {
                if (!$fieldDescription->hasAssociationAdmin()) {
                    throw new \InvalidArgumentException(sprintf(
                        'The current field `%s` is not linked to an admin.'
                        .' Please create one for the target model: `%s`.',
                        $fieldDescription->getName(),
                        $fieldDescription->getTargetModel() ?? ''
                    ));
                }
            }
        } elseif ($this->isAnyInstanceOf($type, [AdminType::class])) {
            if (!$fieldDescription->hasAssociationAdmin()) {
                throw new \InvalidArgumentException(sprintf(
                    'The current field `%s` is not linked to an admin.'
                    .' Please create one for the target model: `%s`.',
                    $fieldDescription->getName(),
                    $fieldDescription->getTargetModel() ?? ''
                ));
            }

            if (!$fieldDescription->describesSingleValuedAssociation()) {
                throw new \InvalidArgumentException(sprintf(
                    'You are trying to add `%s` field `%s` which is not a One-To-One or Many-To-One association.'
                    .' You SHOULD use `%s` instead.',
                    AdminType::class,
                    $fieldDescription->getName(),
                    CollectionType::class
                ));
            }

            // set sensitive default value to have a component working fine out of the box
            $options['btn_add'] = false;
            $options['delete'] = false;

            $options['data_class'] = $fieldDescription->getAssociationAdmin()->getClass();
            $options['empty_data'] = static fn (): object => $fieldDescription->getAssociationAdmin()->getNewInstance();
            $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'admin'));
        } elseif ($this->isAnyInstanceOf($type, [
            CollectionType::class,
        ])) {
            if (!$fieldDescription->hasAssociationAdmin()) {
                throw new \InvalidArgumentException(sprintf(
                    'The current field `%s` is not linked to an admin.'
                    .' Please create one for the target model: `%s`.',
                    $fieldDescription->getName(),
                    $fieldDescription->getTargetModel() ?? ''
                ));
            }

            $options['type'] = AdminType::class;
            $options['modifiable'] = true;
            $options['type_options'] = $this->getDefaultAdminTypeOptions($fieldDescription, $formOptions);
        }

        return $options;
    }

    /**
     * @param string|null $type
     * @param array       $classes
     *
     * @return bool
     */
    private function isAnyInstanceOf(?string $type, array $classes): bool
    {
        if (null === $type) {
            return false;
        }

        foreach ($classes as $class) {
            if (is_a($type, $class, true)) {
                return true;
            }
        }

        // handle form type inheritance and check all parent types
        $resolvedType = $this->formRegistry->getType($type);
        $parentType = $resolvedType->getParent();
        if (null !== $parentType) {
            $parentType = \get_class($parentType->getInnerType());

            // all types have "Symfony\Component\Form\Extension\Core\Type\FormType" as parent
            // so we ignore it here for performance reasons
            if (FormType::class !== $parentType) {
                return $this->isAnyInstanceOf($parentType, $classes);
            }
        }

        return false;
    }

    /**
     * @param FieldDescriptionInterface $fieldDescription
     * @param array                     $formOptions
     *
     * @return array
     */
    private function getDefaultAdminTypeOptions(FieldDescriptionInterface $fieldDescription, array $formOptions): array
    {
        $typeOptions = [
            'sonata_field_description' => $fieldDescription,
            'data_class' => $fieldDescription->getAssociationAdmin()->getClass(),
            'empty_data' => static fn (): object => $fieldDescription->getAssociationAdmin()->getNewInstance(),
        ];

        if (isset($formOptions['by_reference'])) {
            $typeOptions['collection_by_reference'] = $formOptions['by_reference'];
        }

        return $typeOptions;
    }
}
