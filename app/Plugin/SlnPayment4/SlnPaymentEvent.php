<?php

namespace Plugin\SlnPayment4;

use Eccube\Common\Constant;
use Eccube\Common\EccubeConfig;
use Eccube\Repository\PaymentRepository;
use Eccube\Repository\Master\OrderStatusRepository;
use Eccube\Event\TemplateEvent;
use Eccube\Event\EventArgs;
use Eccube\Event\EccubeEvents;
use Eccube\Entity\Customer;
use Eccube\Entity\Master\CustomerStatus;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Exception\ShoppingException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Plugin\SlnPayment4\Repository\MemCardIdRepository;
use Plugin\SlnPayment4\Repository\OrderPaymentHistoryRepository;
use Plugin\SlnPayment4\Repository\OrderPaymentStatusRepository;
use Plugin\SlnPayment4\Repository\PluginConfigRepository;
use Plugin\SlnPayment4\Service\Method\CreditCard;
use Plugin\SlnPayment4\Service\Method\RegisteredCreditCard;
use Plugin\SlnPayment4\Service\Method\MethodUtils;
use Plugin\SlnPayment4\Service\SlnMailService;
use Plugin\SlnPayment4\Service\BasicItem;
use Plugin\SlnPayment4\Service\Util;
use Plugin\SlnPayment4\Service\SlnAction\Cvs;
use Plugin\SlnPayment4\Service\SlnAction\Credit;
use Plugin\SlnPayment4\Exception\SlnShoppingException;
use Plugin\SlnPayment4\Service\SlnAction\Mem;


class SlnPaymentEvent implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var PaymentRepository
     */
    private $paymentRepository;

    /**
     * @var OrderStatusRepository
     */
    private $orderStatusRepository;

    /**
     * @var SlnMailService
     */
    private $mailService;

    /**
     * @var BasicItem
     */
    private $basicItem;

    /**
     * @var Util
     */
    private $util;

    /**
     * @var MemCardIdRepository
     */
    private $memCardIdRepository;

    /**
     * @var OrderPaymentHistoryRepository
     */
    private $orderPaymentHistoryRepository;

    /**
     * @var OrderPaymentStatusRepository
     */
    private $orderPaymentStatusRepository;

    /**
     * @var PluginConfigRepository
     */
    private $configRepository;

    /**
     * @var Mem
     */
    private $mem;

    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authorizationChecker,
        EccubeConfig $eccubeConfig,
        EventDispatcherInterface $eventDispatcher,
        PaymentRepository $paymentRepository,
        OrderStatusRepository $orderStatusRepository,
        SlnMailService $mailService,
        BasicItem $basicItem,
        Util $util,
        MemCardIdRepository $memCardIdRepository,
        OrderPaymentStatusRepository $orderPaymentStatusRepository,
        OrderPaymentHistoryRepository $orderPaymentHistoryRepository,
        PluginConfigRepository $configRepository,
        Mem $mem
    ) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->eccubeConfig = $eccubeConfig;
        $this->eventDispatcher = $eventDispatcher;
        $this->paymentRepository = $paymentRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->mailService = $mailService;
        $this->basicItem = $basicItem;
        $this->util = $util;
        $this->memCardIdRepository = $memCardIdRepository;
        $this->orderPaymentStatusRepository = $orderPaymentStatusRepository;
        $this->orderPaymentHistoryRepository = $orderPaymentHistoryRepository;
        $this->configRepository = $configRepository;
        $this->mem = $mem;
    }

    /**
     * ???????????????????????????????????????????????????????????????????????????????????????
     * ?????????????????????????????????????????????????????????????????????????????????
     * - ???????????????????????????
     * - ????????????????????????????????????????????????
     * - ?????????????????????????????????????????????????????????
     * ?????????????????????????????????0
     *
     * ??????
     * - array('eventName' => 'methodName')
     * - array('eventName' => array('methodName', $priority))
     * - array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            '@admin/Order/index.twig' => 'onAdminOrderIndexTwig',
            EccubeEvents::ADMIN_ORDER_INDEX_INITIALIZE => 'onAdminOrderIndexInitialize',
            EccubeEvents::ADMIN_ORDER_INDEX_SEARCH => 'onAdminOrderIndexSearch',
            '@admin/Order/edit.twig' => 'onAdminOrderEditTwig',
            EccubeEvents::ADMIN_ORDER_EDIT_INDEX_INITIALIZE => 'onAdminOrderEditIndexInitialize',
            'Cart/index.twig' => 'onCartIndexTwig',
            'Shopping/confirm.twig' => 'onShoppingConfirmTwig',
            'Mypage/index.twig' => 'onMypageTwig',
            'Mypage/history.twig' => 'onMypageTwig',
            'Mypage/favorite.twig' => 'onMypageTwig',
            'Mypage/change.twig' => 'onMypageTwig',
            'Mypage/change_complete.twig' => 'onMypageTwig',
            'Mypage/delivery.twig' => 'onMypageTwig',
            'Mypage/delivery_edit.twig' => 'onMypageTwig',
            'Mypage/withdraw.twig' => 'onMypageTwig',
            '@SlnRegular4/Mypage/regular_order.twig' => 'onMypageTwig',
            '@SlnRegular4/Mypage/regular_history.twig' => 'onMypageTwig',
            '@SlnPayment4/sln_edit_card.twig' => 'onMypageTwig',
            EccubeEvents::FRONT_CART_BUYSTEP_INITIALIZE => 'onFrontCartBuystepInitialize',
            'Shopping/index.twig' => 'onShoppingIndexTwig',
            'sln.service.regular.nextorder.complete' => 'onSlnServiceRegularNextorderComplete',
            'sln.service.regular.mypage_history.change_payids' => 'onSlnServiceRegularMypageHistoryChangePayids',
            EccubeEvents::ADMIN_CUSTOMER_EDIT_INDEX_INITIALIZE => 'onAdminCustomerEditIndexInitialize',
            EccubeEvents::ADMIN_CUSTOMER_DELETE_COMPLETE => 'onAdminCustomerDeleteComplete',
            EccubeEvents::FRONT_MYPAGE_WITHDRAW_INDEX_COMPLETE => 'onFrontMypageWithdrawComplete',
        ];
    }

    /**
     * ???????????? - ??????????????????
     */
    public function onAdminOrderIndexTwig(TemplateEvent $event) {
        $pData = $event->getParameters();
        $viewPayStatus = array();
        if ($pData['pagination']) {
            $orderIds = array();
            foreach ($pData['pagination'] as $order) {
                $orderIds[] = $order->getId();
            }
            if (count($orderIds)) {
                $payStatuses = $this->orderPaymentStatusRepository->findBy(array('id' => $orderIds));
                if (count($payStatuses)) {
                    foreach ($payStatuses as $payStatus) {
                        $viewPayStatus[$payStatus->getId()] = $payStatus;
                    }
                }
            }
        }
        $pData['viewPayStatus'] = $viewPayStatus;
        $pData['pay_status'] = array_flip($this->container->getParameter('arrPayStatusNames'));
        $token = $this->container->get('security.csrf.token_manager')->getToken(Constant::TOKEN_NAME)->getValue();
        $pData["pay_token"] = $token;
        $event->setParameters($pData);
        $event->addSnippet('@SlnPayment4/admin/order_index.twig');
    }

    /**
     * ???????????? - ??????????????????
     */
    public function onAdminOrderIndexInitialize(EventArgs $event)
    {
        $arrPayStatusNames = $this->container->getParameter('arrPayStatusNames');
        $builder = $event->getArgument('builder');
        $builder->add('sln_pay_status', ChoiceType::class, [
            'label' => '????????????',
            'choices' => $arrPayStatusNames,
            'expanded' => true,
            'multiple' => true,
        ]);
    }

    /**
     * ???????????? - ????????????
     */
    public function onAdminOrderIndexSearch(EventArgs $event)
    {
        $searchData = $event->getArgument('searchData');
        $qb = $event->getArgument('qb');
        $pyStatus = $searchData['sln_pay_status'];
        if (count($pyStatus)) {
            $qb2 = $this->entityManager->createQueryBuilder();
            $qb2->select('s')
                ->from('\Plugin\SlnPayment4\Entity\OrderPaymentStatus', 'sln_status')
                ->andWhere('o.id = sln_status.id')
                ->andWhere($qb2->expr()->in('sln_status.paymentStatus', $pyStatus));
            $qb->andWhere($qb->expr()->exists($qb2->getDQL()));
        }
    }

    /**
     * ?????????????????? - ????????????
     */
    public function onAdminOrderEditTwig(TemplateEvent $event)
    {
        $pData = $event->getParameters();
        $Order = $pData['Order'];

        if (MethodUtils::isSlnPaymentMethodByOrder($Order)) {
            //?????????????????????
            $paymentStatus = $this->orderPaymentStatusRepository->getStatus($Order);
            //????????????????????????????????????
            if ($paymentStatus) {
                $pData['payStatusId'] = $paymentStatus->getPaymentStatus();
                
                $payStatus = $this->orderPaymentStatusRepository->getPayStatusName($paymentStatus->getPaymentStatus());
                if (empty($payStatus)) {
                    // ??????????????????????????????
                    return;
                }
                $pData['payStatus'] = $payStatus;
                
                $pData['payAmount'] = $paymentStatus->getAmount();
                
                $pData['isCard'] = true;
            
                if (MethodUtils::isCvsMethod($Order->getPayment()->getMethodClass())) {
                    // ?????????????????????
                    $pData['isCard'] = false;
                    
                    $cvsName = "";
                    
                    if ($paymentStatus->getPayee()) {
                        //????????????
                        $arrCvsCd = $this->basicItem->getCvsCd();
                    
                        $cvsName = $arrCvsCd[$paymentStatus->getPayee()];
                        if (!$cvsName) {
                            $cvsName = $paymentStatus->getPayee();
                        }
                    
                        $pData['payCvsName'] = $cvsName;
                    }
                    
                    $FreeAreaHistory = $this->orderPaymentHistoryRepository
                                            ->findOneBy(
                                                array('orderId' => $Order->getId(),
                                                    'operateId' => array('2Add', '2Chg'),
                                                    'sendFlg' => 1,
                                                    'requestFlg' => 0,
                                                    'responseCd' => 'OK',
                                                ),
                                                array('id' => 'DESC')
                                            );
                    if ($FreeAreaHistory) {
                        $FreeAreabody = $FreeAreaHistory->getBody();
                        $FreeAreadata = json_decode($FreeAreabody, 1);
                    
                        //???????????????????????????????????????
                        $pData['payLink'] = $this->configRepository->getConfig()->getCreditConnectionPlace3() . sprintf("?code=%s&rkbn=2", $FreeAreadata['FreeArea']);
                    }
                }
                //?????????????????????
                $pData['payHistorys'] = $this->orderPaymentHistoryRepository->findBy(array('orderId' => $Order->getId()));
            }
        }
        $event->setParameters($pData);
        $event->addSnippet('@SlnPayment4/admin/order_edit.twig');
    }

    /**
     * redirectToRoute?????????????????????????????????
     */
    public function redirectToRouteResponse($route, $params = array()) {
        $router = $this->container->get('router');
        return new RedirectResponse($router->generate($route, $params), 302);
    }

    /**
     * ?????????????????? - ?????????
     */
    public function onAdminOrderEditIndexInitialize(EventArgs $event) {
        $request = $event->getRequest();
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            
            $Order = $event->getArgument('TargetOrder');
            $orderId = $Order->getId();
            
            if (!$orderId) {
                return ;
            }
            
            $history = $this->orderPaymentHistoryRepository
                ->findOneBy(
                    array('orderId' => $orderId,
                        'operateId' => array('2Add', '1Auth', '1Gathering', '1ReAuth'),
                        'sendFlg' => 1,
                        'requestFlg' => 0
                    ),
                    array('id' => 'DESC')
                );
            
            if (!$history) {
                return ;
            }
            
            $mode = $request->get('mode');

            if (!$mode || substr($mode, 0, 3) != "sln") {
                return ;
            }
            
            $cvs = $this->container->get(Cvs::class);
            $card = $this->container->get(Credit::class);
            
            try {
                //?????????????????????????????????
                switch ($mode) {
                    case 'sln_cvs_ref'://????????????
                        $ref = $cvs->Ref($Order, $this->configRepository->getConfig(), $history);

                        if (!is_null($ref->getAmount())) {//??????????????????
                            if ($Order->getPaymentTotal() == $ref->getAmount()) {//????????????????????????
            
                                //????????????????????????????????????
                                $this->orderPaymentStatusRepository->paySuccess($Order, $ref->getCvsCd());
            
                                //????????????????????????????????????
                                $Order->setOrderStatus($this->orderStatusRepository->find(OrderStatus::PAID));
                                $Order->setPaymentDate(new \DateTime());
                                $this->entityManager->persist($Order);
                                $this->entityManager->flush();
                                
                                $this->util->addSuccess($request, 'admin.common.save_complete', 'admin');
                                return;
                            } else {
                                throw new SlnException("?????????????????????????????????????????????????????????");
                            }
                        }
                        break;
                    case "sln_cvs_chg"://??????????????????
                        $cvs->Chg($Order, $this->configRepository->getConfig(), $history);
                        $this->orderPaymentStatusRepository->requestSuccess($Order, $Order->getPaymentTotal());
                        //?????????????????????????????????
                        $this->orderPaymentStatusRepository->change($Order, $Order->getPaymentTotal());
                        $this->util->addSuccess($request, '?????????????????????????????????', 'admin');
                        return;
                    case "sln_cvs_del":
                        //????????????
                        $cvs->Del($Order, $this->configRepository->getConfig(), $history);
                        
                        $this->orderPaymentStatusRepository->cancel($Order);
                        $this->util->addSuccess($request, '??????????????????????????????', 'admin');
                        return;
                    case "sln_cvs_add":
                        list($link, $add) = $cvs->Add($Order, $this->configRepository->getConfig(), '');

                        //????????????????????????????????????
                        $this->orderPaymentStatusRepository
                                ->requestSuccess($Order, $add->getContent()->getAmount());
                    
                        //?????????????????????????????????
                        $this->orderPaymentStatusRepository->change($Order, $add->getContent()->getAmount());
                                
                        //????????????????????????????????????
                        $Order->setOrderStatus($this->orderStatusRepository->find(OrderStatus::NEW));
                        $this->entityManager->persist($Order);
                        $this->entityManager->flush();
                    
                        $this->util->addSuccess($request, '???????????????????????????', 'admin');
                        return;
                    case "sln_card_commit":
                        $card->Capture($Order, $this->configRepository->getConfig(), $history);
                        
                        //????????????????????????????????????
                        $this->orderPaymentStatusRepository->commit($Order, $Order->getPaymentTotal());
                        $this->util->addSuccess($request, '???????????????????????????????????????', 'admin');
                        return;
                    case "sln_card_cancel":
                        $card->Delete($Order, $this->configRepository->getConfig(), $history);
                        
                        //????????????????????????????????????
                        $this->orderPaymentStatusRepository->void($Order);
                        $this->util->addSuccess($request, '??????(??????)???????????????????????????', 'admin');
                        return;
                    case "sln_card_change":
                        $card->Change($Order, $this->configRepository->getConfig(), $history);
                    
                        //?????????????????????????????????
                        $this->orderPaymentStatusRepository->change($Order, $Order->getPaymentTotal());
                        $this->util->addSuccess($request, '?????????????????????????????????????????????', 'admin');
                        return;
                    case "sln_card_reauth":
                        
                        /* @var $kaiinHistory \Plugin\SlnPayment4\Entity\PlgSlnOrderPaymentHistory */
                        $kaiinHistory = $this->orderPaymentHistoryRepository
                                            ->findOneBy(
                                                array('orderId' => $Order->getId(),
                                                    'operateId' => array('1Auth', '1Gathering'),
                                                ),
                                                array('id' => 'ASC')
                                            );
                        
                        $card->ReAuth($Order, $this->configRepository->getConfig(), $history);
                        
                        if ($kaiinHistory->getOperateId() == '1Gathering') {
                            //???????????????
                            $this->orderPaymentStatusRepository->capture($Order, $Order->getPaymentTotal());
                        } else {
                            //???????????????
                            $this->orderPaymentStatusRepository->auth($Order, $Order->getPaymentTotal());
                        }
                        $this->util->addSuccess($request, '???????????????????????????????????????????????????', 'admin');
                        return;
                    default:
                        throw new SlnException("????????????????????????");
                        break;
                }
            } catch (SlnShoppingException $e) {
                log_error(__FILE__ . '(' . __LINE__ . ') ' . $e->getMessage());
                if (substr($mode, 4, 4) == 'card') {
                    $this->util->addCardNotice(sprintf("card order edit error:%s order_id(%s)", $e->getMessage(), $Order->getId() . " " . $e->getFile() . $e->getLine()));
                } else {
                    $this->util->addCvsNotice(sprintf("cvs order edit error:%s order_id(%s)", $e->getMessage(), $Order->getId() . " " . $e->getFile() . $e->getLine()));
                }
                $this->util->addErrorLog($e->getSlnErrorName() . $e->getSlnErrorDetail() . 'order_id:' . $Order->getId() . " " . $e->getFile() . $e->getLine());
                $this->ErrMss = $e->getSlnErrorDetail();
                $this->util->addWarning($request, $this->ErrMss, 'admin');
            } catch (SlnException $e) {
                log_error(__FILE__ . '(' . __LINE__ . ') ' . $e->getMessage());
                if (substr($mode, 4, 4) == 'card') {
                    $log = sprintf("card order edit error:%s order_id(%s)", $e->getMessage(), $Order->getId() . " " . $e->getFile() . $e->getLine());
                    $this->util->addCardNotice($log);
                } else {
                    $log = sprintf("cvs order edit error:%s order_id(%s)", $e->getMessage(), $Order->getId() . " " . $e->getFile() . $e->getLine());
                    $this->util->addCvsNotice($log);
                }
                $this->util->addErrorLog($log);
                $this->ErrMss = $e->getMessage();
                $this->util->addWarning($request, $this->ErrMss, 'admin');
            } catch (\Exception $e) {
                log_error(__FILE__ . '(' . __LINE__ . ') ' . $e->getMessage());
                if (substr($mode, 4, 4) == 'card') {
                    $log = sprintf("card order edit error:%s order_id(%s)", $e->getMessage(), $Order->getId() . " " . $e->getFile() . $e->getLine());
                    $this->util->addCardNotice($log);
                } else {
                    $log = sprintf("cvs order edit error:%s order_id(%s)", $e->getMessage(), $Order->getId() . " " . $e->getFile() . $e->getLine());
                    $this->util->addCvsNotice($log);
                }
                $this->util->addErrorLog($log);
                throw new \Exception($e->getMessage() . " " . $e->getFile() . $e->getLine());
            }
        }
    }

    public function onCartIndexTwig(TemplateEvent $event) {
        $isQuick = $this->configRepository->getConfig()->getQuickAccounts();
        if ($isQuick == 1) {
            $event->addSnippet('@SlnPayment4/sln_cart_quick_pay.twig', true);
        }
    }

    /**
     * ??????????????????????????????????????????
     */
    public function onShoppingConfirmTwig(TemplateEvent $event) {
        // ??????????????????????????????????????????
        $Order = $event->getParameter('Order');
        if ($Order) {
            $methodClass = $Order->getPayment()->getMethodClass();
            if (MethodUtils::isSlnPaymentMethodByOrder($Order)){
                $event->addSnippet('@SlnPayment4/sln_shopping_confirm.twig');
            }
        }
    }

    public function onMypageTwig(TemplateEvent $event) {
        $event->addSnippet('@SlnPayment4/sln_mypage_add_item.twig');
    }

    public function onFrontCartBuystepInitialize(EventArgs $event) {
        $session = $event->getRequest()->getSession();
        $session->remove('eccube.sln.pay.slClink');
        if (array_key_exists('slClink', $_GET) && $_GET['slClink'] == 1) {
            $session->set('eccube.sln.pay.slClink', 1);
        }
    }

    /**
     * ??????????????????????????????
     */
    public function onShoppingIndexTwig(TemplateEvent $event) {
        $slClink = false;
        $isCreditCardRegistered = false;
        $isEnabledQuickPay = false;

        // ??????????????????????????????
        $session = new Session();
        if ($session->get('eccube.sln.pay.slClink') == 1) {
            $session->remove('eccube.sln.pay.slClink');
            $slClink = true;
        }

        // ????????????????????????????????????
        try {
            $Customer = $event->getParameter('Order')->getCustomer();
            if ($Customer != null) {
                $ReMemRef = $this->mem->MemRef($Customer, $this->configRepository->getConfig());
                if ($ReMemRef->getContent()->getKaiinStatus() == 0) {
                    $isCreditCardRegistered = true;
                }
            }
        } catch(\Exception $e) {
            log_info($e->getMessage());
        }

        // ?????????????????????????????????????????????????????????????????????????????????????????????
        if ($slClink && $isCreditCardRegistered) {
            $isEnabledQuickPay = true;
        }

        // ??????????????????????????????ID??????
        $ccPayId = 0;
        $payment = $this->paymentRepository->findOneBy(['method_class' => CreditCard::class]);
        if ($payment) {
            $ccPayId = $payment->getId();
        }

        // ??????????????????????????????????????????ID??????
        $rcPayId = 0;
        $payment = $this->paymentRepository->findOneBy(['method_class' => RegisteredCreditCard::class]);
        if ($payment) {
            $rcPayId = $payment->getId();
        }

        $event->setParameter('slClink', $slClink);
        $event->setParameter('isEnabledQuickPay', $isEnabledQuickPay);
        $event->setParameter('isCreditCardRegistered', $isCreditCardRegistered);
        $event->setParameter('ccPayId', $ccPayId);
        $event->setParameter('rcPayId', $rcPayId);
        
        $event->addSnippet('@SlnPayment4/sln_shopping_quick_pay.twig');
    }

    /**
     * ??????????????????????????????????????????
     * @param EventArgs $event
     * @throws \Exception
     */
    public function onSlnServiceRegularNextorderComplete(EventArgs $event)
    {
        /* @var $Order \Plugin\SlnRegular4\Entity\SlnRegularOrder */
        $Order = $event->getArgument('Order');
        
        //?????????????????????????????????
        $methodClass = $Order->getPayment()->getMethodClass();
        if (!MethodUtils::isSlnPaymentMethod($methodClass)) {
            return;
        }
        
        $event->setArgument('isSendMail', false);
        
        $cvs = $this->container->get(Cvs::class);
        
        // ??????????????????????????????
        $em = $this->entityManager;
        
        $reUrl = "";
        
        try {
            
            if (MethodUtils::isCvsMethod($methodClass)) {
                //???????????????????????????
                $this->orderPaymentStatusRepository->unsettled($Order);
                
                //?????????????????????
                list($reUrl, $add) = $cvs->Add(
                    $Order,
                    $this->configRepository->getConfig(),
                    $event->getRequest()->getSchemeAndHttpHost() . $this->util->generateUrl('shopping_complete'));
                
                $this->orderPaymentStatusRepository->requestSuccess($Order, $add->getContent()->getAmount());
            } else {
                
                $method = $em->getRepository('\Plugin\SlnRegular4\Entity\SlnRegularPluginConfig')->getConfig()->getNextCreditMethod();
                
                //???????????????????????????
                $this->orderPaymentStatusRepository->unsettled($Order);
                
                $master = new \Plugin\SlnPayment4\Service\SlnContent\Credit\Master();
                $card = $this->container->get(Credit::class);
                
                list($KaiinId, $KaiinPass) = $this->util->getNewKaiin($this->memCardIdRepository, $Order->getCustomer(), $this->eccubeConfig->get('eccube_auth_magic'));
                $master->setKaiinId($KaiinId);
                $master->setKaiinPass($KaiinPass);
                
                $master->setPayType("01");
                
                if ($method == 1) {
                    $card->Auth($Order, $this->configRepository->getConfig(), $master);
                    $this->orderPaymentStatusRepository->auth($Order, $master->getAmount());
                } else {
                    $card->Gathering($Order, $this->configRepository->getConfig(), $master);
                    $this->orderPaymentStatusRepository->capture($Order, $master->getAmount());
                }
            }
        
        } catch (SlnShoppingException $e) {
            log_error(__FILE__ . '(' . __LINE__ . ') ' . $e->getMessage());
        
            if (MethodUtils::isCvsMethod($methodClass)) {
                $log = sprintf("cvs shopping error:%s order_id(%s)", $e->getSlnErrorCode() . '|' . $e->getSlnErrorName() . '|' . $e->getSlnErrorDetail(), $Order->getId());
                $this->util->addCvsNotice($log);
            } else {
                $log = sprintf("card shopping error:%s order_id(%s)", $e->getSlnErrorCode() . '|' . $e->getSlnErrorName() . '|' . $e->getSlnErrorDetail(), $Order->getId());
                $this->util->addCardNotice($log);
            }
            
            if ($e->checkSystemError()) {
                $this->util->addErrorLog($e->getSlnErrorName() . $e->getSlnErrorDetail() . 'order_id:' . $Order->getId() . " " . $e->getFile() . $e->getLine());
            }
        
            $this->orderPaymentStatusRepository->fail($Order);
            $event->setArgument('errorMess', sprintf('??????id:(%s) ?????????????????????????????????(%s)', $Order->getId(), $log));
            
            return ;
        
        } catch (ShoppingException $e) {
            log_error(__FILE__ . '(' . __LINE__ . ') ' . $e->getMessage());
        
            if (MethodUtils::isCvsMethod($methodClass)) {
                $log = sprintf("cvs shopping error:%s order_id(%s)", $e->getMessage(), $Order->getId() . " " . $e->getFile() . $e->getLine());
                $this->util->addCvsNotice($log);
            } else {
                $log = sprintf("card shopping error:%s order_id(%s)", $e->getMessage(), $Order->getId() . " " . $e->getFile() . $e->getLine());
                $this->util->addCardNotice($log);
            }
            
            $this->util->addErrorLog($log);
            
            $this->orderPaymentStatusRepository->fail($Order);
            $event->setArgument('errorMess', sprintf('??????id:(%s) ??????????????????????????????.(%s)', $Order->getId(), $log));
            
            return ;
        } catch (\Exception $e) {
            log_error(__FILE__ . '(' . __LINE__ . ') ' . $e->getMessage());
        
            if (MethodUtils::isCvsMethod($Order->getPayment()->getMethodClass())) {
                $log = sprintf("cvs shopping error:%s order_id(%s)", $e->getMessage(), $Order->getId() . " " . $e->getFile() . $e->getLine());
                $this->util->addCvsNotice($log);
            } else {
                $log = sprintf("card shopping error:%s order_id(%s)", $e->getMessage(), $Order->getId() . " " . $e->getFile() . $e->getLine());
                $this->util->addCardNotice($log);
            }
            
            $this->util->addErrorLog($log);
            
            $this->orderPaymentStatusRepository->fail($Order);
            
            throw new \Exception($e->getMessage());
        }
        
        $event->setArgument('errorMess', null);
        // ???????????????
        $this->mailService->sendOrderMail($Order, $reUrl);
    }
    
    /**
     * ????????????????????????????????????????????????????????????????????????
     */
    public function onSlnServiceRegularMypageHistoryChangePayids(EventArgs $EventArgs)
    {
        $changePayIds = $EventArgs->getArgument('changePayIds');
        $cardType = $this->paymentRepository->findOneBy(['method_class' => CreditCard::class]);
        $cardRegistType = $this->paymentRepository->findOneBy(['method_class' => RegisteredCreditCard::class]);
        $changePayIds[] = $cardType->getId();
        $changePayIds[] = $cardRegistType->getId();
        $EventArgs->setArgument('changePayIds', $changePayIds);
    }

    /**
     * ??????????????????e-SCOTT?????????????????????(Admin/CustomerEditController)
     */
    public function onAdminCustomerEditIndexInitialize(EventArgs $event)
    {
        $form = $event->getArgument("builder")->getForm();
        $oldStatusId = $form->getData()
            ->getStatus()
            ->getId();
        
        // ????????????????????????
        $this->eventDispatcher->addListener(EccubeEvents::ADMIN_CUSTOMER_EDIT_INDEX_COMPLETE, function (EventArgs $event) use ($oldStatusId) {
            $config = $this->configRepository->getConfig();
            $user = $event->getArgument("Customer");
            $form = $event->getArgument("form");
            $newStatusId = $form->getData()
                ->getStatus()
                ->getId();
            
            if ($oldStatusId != $newStatusId && $newStatusId == CustomerStatus::WITHDRAWING) {
                try {
                    // ????????????????????????????????????????????????
                    $this->mem->MemInval($user, $config);
                } catch (\Exception $e) {
                    // ????????????????????????????????????EC-CUBE??????????????????????????????????????????????????????
                }
            }
        });
    }
    
    /**
     * ??????????????????e-SCOTT?????????????????????(Admin/CustomerController)
     */
    public function onAdminCustomerDeleteComplete(EventArgs $event){
        //??????????????????????????????????????????
        if ($this->entityManager->isOpen()) {
            $config = $this->configRepository->getConfig();
            $id = $event->getRequest()->attributes->get("id");
            
            // ??????????????????ID????????????????????????????????????
            $user = new Customer();
            $user->setPropertiesFromArray(["id" => $id]);
            
            try {
                // ????????????????????????????????????????????????
                $this->mem->MemInval($user, $config);
            } catch (\Exception $e) {
                // ????????????????????????????????????EC-CUBE??????????????????????????????????????????????????????
            }
        }
    }
    
    /**
     * ??????????????????e-SCOTT?????????????????????(Mypage/WithdrawController)
     */
    public function onFrontMypageWithdrawComplete(EventArgs $event){
        //??????????????????????????????????????????
        if ($this->entityManager->isOpen()) {
            $config = $this->configRepository->getConfig();
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            
            try {
                // ????????????????????????????????????????????????
                $this->mem->MemInval($user, $config);
            } catch (\Exception $e) {
                // ????????????????????????????????????EC-CUBE??????????????????????????????????????????????????????
            }
        }
    }
}
