<?php
/*
* Plugin Name : PointExpired
*
* Copyright (C) BraTech Co., Ltd. All Rights Reserved.
* http://www.bratech.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\PointExpired;

use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Plugin\PointExpired\Repository\ConfigRepository;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Workflow\Event\Event;
use Doctrine\ORM\EntityManagerInterface;

class PointExpiredEvent implements EventSubscriberInterface
{

    private $entityManager;
    private $authorizationChecker;
    private $tokenStorage;
    private $configRepository;

    public function __construct(
            EntityManagerInterface $entityManager,
            AuthorizationCheckerInterface $authorizationChecker,
            TokenStorageInterface $tokenStorage,
            ConfigRepository $configRepository
            )
    {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->configRepository = $configRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'checkPointExpired',
            EccubeEvents::FRONT_ENTRY_INDEX_COMPLETE => 'hookFrontEntryIndexComplete',
            EccubeEvents::FRONT_SHOPPING_COMPLETE_INITIALIZE => 'hookFrontShoppingCompleteInitialize',
            'workflow.order.transition.cancel' => 'rollbackPeriod',
            'workflow.order.transition.back_to_in_progress' => 'extendPeriod',
            'workflow.order.transition.return' => 'rollbackPeriod',
            'workflow.order.transition.cancel_return' => 'extendPeriod',
            '@admin/Customer/index.twig' => 'onTemplateAdminCustomer',
            '@MailMagazine4/admin/index.twig' => 'onTemplateAdminCustomer',
            '@MailMagazine4/admin/history_condition.twig' => 'onTemplateMailmagazineHistoryCondition',
                ];
    }

    public function checkPointExpired(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $request = $event->getRequest();
        $attributes = $request->attributes;
        $route = $attributes->get('_route');
        $checkList = ['homepage','mypage','cart','shopping'];

        if (in_array($route, $checkList)){
            if ($this->authorizationChecker->isGranted('ROLE_USER')) {
                $Customer = $this->tokenStorage->getToken()->getUser();
                if($Customer instanceof \Eccube\Entity\Customer){
                    $expiredDate = $Customer->getPointExpiredDate();
                    if($expiredDate instanceof \DateTime){
                        $now = new \DateTime();
                        if($expiredDate < $now){
                            $Customer->setPoint(0);
                            $this->entityManager->persist($Customer);
                            $this->entityManager->flush($Customer);
                        }
                    }
                }
            }
        }
    }

    public function hookFrontShoppingCompleteInitialize(EventArgs $event)
    {
        $Order = $event->getArgument('Order');
        $Customer = $Order->getCustomer();
        if($Customer instanceof \Eccube\Entity\Customer){
            $period = $Customer->getExtensionPeriod();
            if(!is_null($period)){
                $date = new \DateTime();
                $date->modify('+ '. $period .' days');
                $expiredDate = $Customer->getPointExpiredDate();
                $Order->setPrevPointExpiredDate($expiredDate)
                        ->setExtensionPeriod($period);
                $this->entityManager->persist($Order);
                $this->entityManager->flush($Order);
                if($expiredDate instanceof \DateTime){
                    if($date > $expiredDate){
                        $Customer->setPointExpiredDate($date);
                    }
                }else{
                    $Customer->setPointExpiredDate($date);
                }
                $this->entityManager->persist($Customer);
                $this->entityManager->flush($Customer);
            }
        }
    }

    public function extendPeriod(Event $event)
    {
        $Order = $event->getSubject()->getOrder();
        $Customer = $Order->getCustomer();
        if ($Customer) {
            $period = $Customer->getExtensionPeriod();
            if(!is_null($period)){
                $date = new \DateTime();
                $date->modify('+ '. $period .' days');
                $expiredDate = $Customer->getPointExpiredDate();
                $Order->setPrevPointExpiredDate($expiredDate)
                      ->setExtensionPeriod($period);
                $this->entityManager->persist($Order);
                $this->entityManager->flush($Order);
                if($expiredDate instanceof \DateTime){
                    if($date > $expiredDate){
                        $Customer->setPointExpiredDate($date);
                    }
                }else{
                    $Customer->setPointExpiredDate($date);
                }
            }
        }
    }

    public function rollbackPeriod(Event $event)
    {
        $Order = $event->getSubject()->getOrder();
        $Customer = $Order->getCustomer();
        if ($Customer) {
            $period = $Order->getExtensionPeriod();
            $expiredDate = $Order->getPrevPointExpiredDate();
            if(!is_null($period)){
                if($expiredDate instanceof \DateTime){
                    $Customer->setPointExpiredDate($expiredDate);
                }else{
                    $expiredDate = $Customer->getPointExpiredDate();
                    if($expiredDate instanceof \DateTime){
                        $expiredDate->modify('- '. $period .' days');
                        $date = new \DateTime($expiredDate->format('Y/m/d'));
                        $Customer->setPointExpiredDate($date);
                    }
                }
            }else{
                // ver.1.0.0の設定のために残す
                $period = $Customer->getExtensionPeriod();
                if(!is_null($period)){
                    $expiredDate = $Customer->getPointExpiredDate();
                    if($expiredDate instanceof \DateTime){
                        $expiredDate->modify('- '. $period .' days');
                        $date = new \DateTime($expiredDate->format('Y/m/d'));
                        $Customer->setPointExpiredDate($date);
                    }
                }
            }
        }
    }

    public function hookFrontEntryIndexComplete(EventArgs $event)
    {
        $Customer = $event->getArgument('Customer');
        $config = $this->configRepository->findOneBy(['name' => 'period']);
        if($config){
            $period = $config->getValue();
            $Customer->setExtensionPeriod($period);
            $this->entityManager->persist($Customer);
            $this->entityManager->flush($Customer);
        }
    }

    public function onTemplateAdminCustomer(TemplateEvent $event)
    {
        $twig = '@PointExpired/admin/Customer/customer_search.twig';
        $event->addSnippet($twig);
    }

    public function onTemplateMailmagazineHistoryCondition(TemplateEvent $event)
    {
        $parameters = $event->getParameters();

        $searchData = $parameters['search_data'];

        $parameters['search_data'] = $searchData;
        $event->setParameters($parameters);

        $twig = '@PointExpired/admin/mailmagazine_history_condition_add.twig';
        $event->addSnippet($twig);

    }
}