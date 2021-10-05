<?php

namespace Plugin\OrderBulkDelete4;

use Eccube\Event\TemplateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Event implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            '@admin/Order/index.twig' => 'onAdminOrderIndexTwig',
        ];
    }

    public function onAdminOrderIndexTwig(TemplateEvent $event)
    {
        $event->addSnippet('@OrderBulkDelete4/admin/Order/index_js.twig');
    }
}
