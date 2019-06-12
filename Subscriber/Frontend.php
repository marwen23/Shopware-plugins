<?php

namespace KbProducts\Subscriber;

use Enlight\Event\SubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Frontend implements SubscriberInterface
{
    private $container;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend'        => 'onFrontendPreDispatch',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_KbProducts' => 'onGetControllerPathFrontend',

        ];
    }

    public function onFrontendPreDispatch(\Enlight_Event_EventArgs $args)
    {
        $this->container->get('template')->addTemplateDir(__DIR__ . '/..' . '/Resources/views/');
        $this->container->get('snippets')->addConfigDir(__DIR__ . '/..' . '/Resources/snippets/');
    }

    public function onGetControllerPathFrontend(\Enlight_Event_EventArgs $args)
    {
        return __DIR__ . '/../Controllers/Frontend/KbProducts.php';
    }
}
