<?php

namespace SezzlePayment\Subscriber\Backend;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware_Controllers_Backend_Order;

class Order implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * @param $pluginDirectory
     */
    public function __construct($pluginDirectory)
    {
        $this->pluginDirectory = $pluginDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order' => 'onOrderPostDispatch'
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onOrderPostDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var Shopware_Controllers_Backend_Order $controller */
        $controller = $args->get('subject');

        $view = $controller->View();
        $request = $controller->Request();

        $view->addTemplateDir($this->pluginDirectory . '/Resources/views');

        if ($request->getActionName() == 'index') {
            $view->extendsTemplate('backend/sezzle_payment/app.js');
        }

        if ($request->getActionName() == 'load') {
            $view->extendsTemplate('backend/sezzle_payment/view/detail/window.js');
        }
    }
}
