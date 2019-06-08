<?php

/*
 * This file is part of the AdminLTE-Bundle demo.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManager;
use KevinPapst\AdminLTEBundle\Event\SidebarMenuEvent;
use KevinPapst\AdminLTEBundle\Event\ThemeEvents;
use KevinPapst\AdminLTEBundle\Model\MenuItemModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use KevinPapst\AdminLTEBundle\Model\UserModel;

/**
 * Class MenuBuilder configures the main navigation.
 */
class MenuBuilderSubscriber implements EventSubscriberInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $security;

    private $entityManger;

    private $user;

    /**
     * @param AuthorizationCheckerInterface $security
     */
    public function __construct(AuthorizationCheckerInterface $security,EntityManagerInterface $entityManager,TokenStorageInterface $tokenStorage)
    {
        $this->security = $security;
        $this->user = $tokenStorage->getToken()->getUser();

        /**
         * @var EntityManager
         */
        $this->entityManger=$entityManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ThemeEvents::THEME_SIDEBAR_SETUP_MENU => ['onSetupNavbar', 100],
            ThemeEvents::THEME_BREADCRUMB => ['onSetupNavbar', 100],
        ];
    }

    /**
     * Generate the main menu.
     *
     * @param SidebarMenuEvent $event
     */
    public function onSetupNavbar(SidebarMenuEvent $event)
    {
        $event->addItem(
            new MenuItemModel('homepage', 'Tableau de bord', 'homepage', [], 'fas fa-tachometer-alt')
        );

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $event->addItem(
                new MenuItemModel('update_file', 'Mis a jour', 'update_file', [], 'fab fa-wpforms')
            );
        }

//        $event->addItem(
  //          new MenuItemModel('html', 'Show html', 'html', [], 'fab fa-wpforms')
    //    );

        $repos=$this->entityManger->getRepository('App:Model');

        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {

            if(!empty($this->user->getId())){
            $model=$repos->findByUserId($this->user->getId());

            $model=$this->entityManger->getRepository('App:Model')->findByUserId( $this->user->getId());
        }

            if(!empty($model)){
                foreach ($model as $row){
                    $demo = new MenuItemModel('demo', 'Formation', null, [], 'far fa-arrow-alt-circle-right');
                    $demo->addChild(
                        new MenuItemModel($row->getId(), $row->getName(), 'formation', array('id' => $row->getId()), 'far fa-arrow-alt-circle-down')
                    );
                    $event->addItem($demo);
                }
            }
        }

        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $event->addItem(
                new MenuItemModel('logout', 'Déconnexion', 'fos_user_security_logout', [], 'fas fa-sign-out-alt')
            );
        } else {
            $event->addItem(
                new MenuItemModel('login', 'menu.login', 'fos_user_security_login', [], 'fas fa-sign-in-alt')
            );
        }

        $this->activateByRoute(
            $event->getRequest()->get('_route'),
            $event->getItems()
        );
    }

    /**
     * @param string $route
     * @param MenuItemModel[] $items
     */
    protected function activateByRoute($route, $items)
    {
        foreach ($items as $item) {
            if ($item->hasChildren()) {
                $this->activateByRoute($route, $item->getChildren());
            } elseif ($item->getRoute() == $route) {
                $item->setIsActive(true);
            }
        }
    }
}
