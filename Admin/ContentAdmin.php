<?php

namespace Sherlockode\SonataAdvancedContentBundle\Admin;

use Sherlockode\AdvancedContentBundle\Form\Type\ContentType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormBuilderInterface;

class ContentAdmin extends AbstractAdmin
{
    /**
     * @var string
     */
    private $contentTypeClass;

    protected $baseRouteName = 'admin_afb_content';

    public function getFormTheme()
    {
        return array_merge(
            parent::getFormTheme(),
            ['@SherlockodeAdvancedContent/Form/content_type.html.twig']
        );
    }

    public function setContentTypeClass($class)
    {
        $this->contentTypeClass = $class;
    }

    public function configureFormFields(FormMapper $form)
    {
        // We need to declare each form field through the form mapper to ensure they are displayed correctly
        $form
            ->add('fieldValues')
        ;
    }

    /**
     * Create ContentType form
     *
     * @return mixed
     *
     * @throws \Exception
     */
    private function getContentFormBuilder()
    {
        if ($this->hasParentFieldDescription() && $this->getParentFieldDescription()->getOption('content_type')) {
            $contentType = $this->getParentFieldDescription()->getOption('content_type');
        } elseif ($this->getSubject() && $this->getSubject()->getContentType()) {
            $contentType = $this->getSubject()->getContentType();
        } elseif ($this->getRequest()->get('content_type_id')) {
            $contentTypeId = $this->getRequest()->get('content_type_id');
            $contentType = $this->getModelManager()
                ->getEntityManager($this->contentTypeClass)
                ->getRepository($this->contentTypeClass)
                ->find($contentTypeId);
        } else {
            throw new \Exception('Unable to guess the ContentType to use for this object');
        }

        $this->formOptions['contentType'] = $contentType;

        return $this->getFormContractor()->getFormFactory()
            ->createNamedBuilder($this->getUniqid(), ContentType::class, null, $this->formOptions);
    }

    public function getFormBuilder()
    {
        $this->formOptions['data_class'] = $this->getClass();

        $formBuilder = $this->getContentFormBuilder();
        $this->defineFormBuilder($formBuilder);

        return $formBuilder;
    }

    public function defineFormBuilder(FormBuilderInterface $formBuilder)
    {
        $formBuilder = $this->getContentFormBuilder();
        parent::defineFormBuilder($formBuilder);
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('id', null, ['label' => 'content.id'])
            ->add('contentType', null, ['associated_property' => 'name', 'label' => 'content.content_type'])
            ->add('_action', 'actions', [
                'actions' => [
                    'edit'   => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery();
        $request = $this->getRequest();

        $query->getQueryBuilder()
            ->join('o.contentType', 'content_type')
            ->where('content_type.id = :type')
            ->setParameter('type', $request->get('content_type_id'));

        return $query;
    }

    public function getPersistentParameters()
    {
        $parameters = parent::getPersistentParameters();

        return array_merge($parameters, [
            'content_type_id' => $this->getRequest()->get('content_type_id'),
        ]);
    }
}
