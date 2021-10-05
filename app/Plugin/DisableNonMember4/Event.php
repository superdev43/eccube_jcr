<?php

namespace Plugin\DisableNonMember4;

use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Event implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    public function __construct(
        ContainerInterface $container,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $this->container = $container;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EccubeEvents::FRONT_CART_BUYSTEP_COMPLETE => 'onFrontCartBuystepComplete',
            'Shopping/login.twig' => 'onShoppingLoginTwigRender'
        ];
    }

    public function onFrontCartBuystepComplete(EventArgs $eventArgs)
    {
        // IS_AUTHENTICATED_REMEMBEREDの場合はShoppingControllerからリダイレクトされてくるのでROLE_USERにしておく
        if (!$this->authorizationChecker->isGranted('ROLE_USER')) {
            $response = new RedirectResponse($this->container->get('router')->generate('shopping_login'));
            $eventArgs->setResponse($response);
        }
    }

    public function onShoppingLoginTwigRender(TemplateEvent $event)
    {
        $event->addAsset('@DisableNonMember4/shopping_login_css.twig');
        $event->addSnippet('@DisableNonMember4/shopping_login.twig');
    }
}
