<?php

namespace Sherlockode\SonataAdvancedContentBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Sherlockode\AdvancedContentBundle\Manager\ConfigurationManager;
use Sherlockode\AdvancedContentBundle\Model\ContentTypeInterface;
use Sherlockode\AdvancedContentBundle\Model\PageInterface;
use Sherlockode\AdvancedContentBundle\Model\PageTypeInterface;
use Sonata\AdminBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuBuilderListener
{
    /**
     * @var ConfigurationManager
     */
    private $configurationManager;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param ObjectManager                 $om
     * @param ConfigurationManager          $configurationManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        ObjectManager $om,
        ConfigurationManager $configurationManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->configurationManager = $configurationManager;
        $this->om = $om;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function addMenuItems(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu()->getChild('content.label');

        if ($this->authorizationChecker->isGranted('ROLE_SHERLOCKODE_ADVANCED_CONTENT_ADMIN_CONTENT_LIST')) {
            $contentTypeClass = $this->configurationManager->getEntityClass('content_type');
            $contentTypes = $this->om->getRepository($contentTypeClass)->findAll();
            /** @var ContentTypeInterface $contentType */
            foreach ($contentTypes as $contentType) {
                if ($contentType->getPage() instanceof PageInterface || $contentType->getPageType() instanceof PageTypeInterface) {
                    continue;
                }
                $menu->addChild('content_type_' . $contentType->getId(), [
                    'label'           => $contentType->getName(),
                    'extras'          => [
                        'label_catalogue' => false,
                    ],
                    'route'           => 'admin_afb_content_list',
                    'routeParameters' => ['content_type_id' => $contentType->getId()],
                ]);
            }
        }

        if ($this->authorizationChecker->isGranted('ROLE_SHERLOCKODE_ADVANCED_CONTENT_TOOLS')) {
            $menu->addChild('acb_tools', [
                'label'  => 'tools_menu_label',
                'extras' => ['label_catalogue' => 'AdvancedContentBundle'],
                'route'  => 'sherlockode_acb_tools_index',
            ]);
        }
    }
}
