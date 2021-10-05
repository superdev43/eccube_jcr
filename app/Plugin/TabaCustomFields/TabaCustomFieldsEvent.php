<?php
namespace Plugin\TabaCustomFields;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Plugin\TabaCustomFields\Event\Admin\CustomerEvent as AdminCustomerEvent;
use Eccube\Event\EventArgs;
use Plugin\TabaCustomFields\Common\Constants;
use Doctrine\ORM\EntityManagerInterface;
use Plugin\TabaCustomFields\Event\Admin\ProductEvent as AdminProductEvent;
use Plugin\TabaCustomFields\Event\Admin\OrderEvent as AdminOrderEvent;
use Eccube\Event\TemplateEvent;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Eccube\Common\Constant;
use Plugin\TabaCustomFields\Event\CustomerEvent;
use Plugin\TabaCustomFields\Event\OrderEvent;
use Plugin\TabaCustomFields\Repository\CustomFieldsContentsRepository;
use Plugin\TabaCustomFields\Repository\CustomFieldsRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eccube\Request\Context;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Eccube\Event\EccubeEvents;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;


class TabaCustomFieldsEvent implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Context
     */
    protected $requestContext;

    /**
     * @var CustomFieldsContentsRepository
     */
    protected $customFieldsContentsRepository;

    /**
     * @var CustomFieldsRepository
     */
    protected $customFieldsRepository;

    /**
     * @var CsrfTokenManagerInterface
     */
    protected $tokenManager;

    /**
     * @var AdminCustomerEvent
     */
    protected $adminCustomerEvent;

    /**
     * @var AdminProductEvent
     */
    protected $adminProductEvent;

    /**
     * @var AdminOrderEvent
     */
    protected $adminOrderEvent;

    /**
     * @var CustomerEvent
     */
    protected $customerEvent;

    /**
     * TabaCustomFieldsEvent constructor.
     */
    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Context $requestContext,
        CustomFieldsContentsRepository $customFieldsContentsRepository,
        CustomFieldsRepository $customFieldsRepository,
        CsrfTokenManagerInterface $tokenManager,
        AdminCustomerEvent $adminCustomerEvent,
        AdminProductEvent $adminProductEvent,
        AdminOrderEvent $adminOrderEvent,
        CustomerEvent $customerEvent
    ) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestContext = $requestContext;
        $this->customFieldsContentsRepository = $customFieldsContentsRepository;
        $this->customFieldsRepository = $customFieldsRepository;
        $this->tokenManager = $tokenManager;
        $this->adminCustomerEvent = $adminCustomerEvent;
        $this->adminProductEvent = $adminProductEvent;
        $this->adminOrderEvent = $adminOrderEvent;
        $this->customerEvent = $customerEvent;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => [['onKernelController', 100000000]],
            EccubeEvents::ADMIN_CUSTOMER_EDIT_INDEX_INITIALIZE => [['onAdminCustomerEditInit', 10]],
            EccubeEvents::ADMIN_CUSTOMER_EDIT_INDEX_COMPLETE => [['onAdminCustomerEditComplete', 10]],
            EccubeEvents::ADMIN_CUSTOMER_INDEX_INITIALIZE => [['onAdminCustomerSearchInit', 10]],
            EccubeEvents::ADMIN_CUSTOMER_INDEX_SEARCH => [['onAdminCustomerSearch', 10]],
            EccubeEvents::ADMIN_PRODUCT_EDIT_INITIALIZE => [['onAdminProductEditInit', 10]],
            EccubeEvents::ADMIN_PRODUCT_EDIT_COMPLETE => [['onAdminProductEditComplete', 10]],
            EccubeEvents::ADMIN_PRODUCT_INDEX_INITIALIZE => [['onAdminProductSearchInit', 10]],
            EccubeEvents::ADMIN_PRODUCT_INDEX_SEARCH => [['onAdminProductSearch', 10]],
            EccubeEvents::ADMIN_ORDER_EDIT_INDEX_INITIALIZE => [['onAdminOrderEditInit', 10]],
            EccubeEvents::ADMIN_ORDER_EDIT_INDEX_COMPLETE => [['onAdminOrderEditComplete', 10]],
            EccubeEvents::ADMIN_ORDER_INDEX_INITIALIZE => [['onAdminOrderSearchInit', 10]],
            EccubeEvents::ADMIN_ORDER_INDEX_SEARCH => [['onAdminOrderSearch', 10]],
            EccubeEvents::FRONT_ENTRY_INDEX_INITIALIZE => [['onFrontCustomerInit', 10]],
            EccubeEvents::FRONT_ENTRY_INDEX_COMPLETE => [['onFrontCustomerComplete', 10]],
            EccubeEvents::FRONT_MYPAGE_CHANGE_INDEX_INITIALIZE => [['onFrontCustomerInit', 10]],
            EccubeEvents::FRONT_MYPAGE_CHANGE_INDEX_COMPLETE => [['onFrontCustomerComplete', 10]],
            '@admin/Customer/index.twig' =>  [['onAdminCfCustomerCsvDownloadLinkRender', 10]],
            '@admin/Customer/edit.twig' => [['onAdminFileUploadJSRender', 10]],
            '@admin/Product/index.twig' =>  [['onAdminCfProductCsvDownloadLinkRender', 10]],
            '@admin/Product/product.twig' => [['onAdminFileUploadJSRender', 10]],
            '@admin/Order/index.twig' =>  [['onAdminCfOrderCsvDownloadLinkRender', 10]],
            '@admin/Order/edit.twig' => [['onAdminFileUploadJSRender', 10]],
            'Shopping/index.twig' => [['onTemplateShoppingIndex', 10]],
            'Shopping/confirm.twig' => [['onTemplateShoppingConfirm', 10]],
            'Mypage/history.twig' => [['onTemplateMypageHistory', 10]],
        ];
    }

    /**
     * Initialize fields into form builder
     *
     * @param EventArgs $event
     */
    public function onAdminCustomerEditInit(EventArgs $event)
    {
        $this->adminCustomerEvent->onAdminCustomerEditInit($event);
    }

    /**
     * Handle data
     *
     * @param EventArgs $event
     */
    public function onAdminCustomerEditComplete(EventArgs $event)
    {
        $this->adminCustomerEvent->onAdminCustomerEditComplete($event);
    }

    /**
     * Initialize fields into form builder
     *
     * @param EventArgs $event
     */
    public function onAdminCustomerSearchInit(EventArgs $event)
    {
        $this->adminCustomerEvent->onAdminCustomerSearchInit($event);
    }

    /**
     * Handle data
     *
     * @param EventArgs $event
     */
    public function onAdminCustomerSearch(EventArgs $event)
    {
        $this->adminCustomerEvent->onAdminCustomerSearch($event);
    }

    /**
     * Initialize fields into form builder
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditInit(EventArgs $event)
    {
        $this->adminProductEvent->onAdminProductEditInit($event);
    }

    /**
     * Handle data
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditComplete(EventArgs $event)
    {
        $this->adminProductEvent->onAdminProductEditComplete($event);
    }

    /**
     * Initialize fields into form builder
     *
     * @param EventArgs $event
     */
    public function onAdminProductSearchInit(EventArgs $event)
    {
        $this->adminProductEvent->onAdminProductSearchInit($event);
    }

    /**
     * Handle data
     *
     * @param EventArgs $event
     */
    public function onAdminProductSearch(EventArgs $event)
    {
        $this->adminProductEvent->onAdminProductSearch($event);
    }

    /**
     * Initialize fields into form builder
     *
     * @param EventArgs $event
     */
    public function onAdminOrderEditInit(EventArgs $event)
    {
        $this->adminOrderEvent->onAdminOrderEditInit($event);
    }

    /**
     * Handle data
     *
     * @param EventArgs $event
     */
    public function onAdminOrderEditComplete(EventArgs $event)
    {
        $this->adminOrderEvent->onAdminOrderEditComplete($event);
    }

    /**
     * Initialize fields into form builder
     *
     * @param EventArgs $event
     */
    public function onAdminOrderSearchInit(EventArgs $event)
    {
        $this->adminOrderEvent->onAdminOrderSearchInit($event);
    }

    /**
     * Handle data
     *
     * @param EventArgs $event
     */
    public function onAdminOrderSearch(EventArgs $event)
    {
        $this->adminOrderEvent->onAdminOrderSearch($event);
    }

    /**
     * Add snippet JS into template
     *
     * @param TemplateEvent $templateEvent
     */
    public function onAdminFileUploadJSRender(TemplateEvent $templateEvent)
    {
        // args
        $templateEvent->setParameter('upload_urlpath_route', Constants::ADMIN_BIND_PREFIX.'file_upload');
        $templateEvent->setParameter('formtype_name', Constants::FILE_UPLOAD_FORMTYPE_NAME);

        //csrf
        $templateEvent->setParameter('csrf_token_name', Constant::TOKEN_NAME);
        $templateEvent->setParameter('csrf_token_key', $this->tokenManager->getToken(Constants::FILE_UPLOAD_FORMTYPE_NAME)->getValue());

        // form
        $templateEvent->setParameter('bilde_form_type_name', $templateEvent->getParameter('form')->vars['id']);

        if ($templateEvent->hasParameter('Customer')) {
            $templateEvent->setParameter('entity', 'customer');
        } elseif ($templateEvent->hasParameter('Product')) {
            $templateEvent->setParameter('entity', 'product');
        } elseif ($templateEvent->hasParameter('Order')) {
            $templateEvent->setParameter('entity', 'order');
        }

        $templateEvent->addSnippet('@TabaCustomFields/common/fileUpload/asset.twig');
        $templateEvent->addSnippet('@TabaCustomFields/common/fileUpload/functionJS.twig');
        // Eyemirrorは独自実装のため、CFで対応後はEyemirrorのEventより削除する
        // $templateEvent->addSnippet('@TabaCustomFields/admin/snippet/datetimepicker.twig');
    }

    /**
     * Initialize fields into form builder
     *
     * @param EventArgs $event
     */
    public function onFrontCustomerInit(EventArgs $event)
    {
        $this->customerEvent->onFrontCustomerInit($event);
    }

    /**
     * Handle data
     *
     * @param EventArgs $event
     */
    public function onFrontCustomerComplete(EventArgs $event)
    {
        $this->customerEvent->onFrontCustomerComplete($event);
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        // 管理画面イベント
        if ($this->requestContext->isAdmin()) {
            // テンプレートイベント
            if ($event->getRequest()->attributes->has('_template')) {
                $template = $event->getRequest()->attributes->get('_template');

                $this->eventDispatcher->addListener($template->getTemplate(), function (TemplateEvent $templateEvent) {
                    // 管理画面のナビゲーションにtaba app のメニューを差し込みます。
                    $taba = $this->container->get(Constants::CONTAINER_KEY_NAME);
                    if (!$taba->get(Constants::PLUGIN_CATEGORY_ID . ".menu")) {
                        $templateEvent->addSnippet('@TabaCustomFields/admin/snippet/nav_taba_app.twig');
                        $taba->set(Constants::PLUGIN_CATEGORY_ID . ".menu",true);
                    }

                    // メニューを差し込みます。
                    $templateEvent->addSnippet('@TabaCustomFields/admin/snippet/nav.twig');
                });
            }
        }
    }

    /**
     * Add js to display field on form
     *
     * @param TemplateEvent $templateEvent
     */
    public function onTemplateShoppingIndex(TemplateEvent $templateEvent)
    {
        $templateEvent->addSnippet('@TabaCustomFields/snippet/shopping_index.twig');
    }

    /**
     * Add js to display field on form
     *
     * @param TemplateEvent $templateEvent
     */
    public function onTemplateShoppingConfirm(TemplateEvent $templateEvent)
    {
        $templateEvent->addSnippet('@TabaCustomFields/snippet/shopping_confirm.twig');
    }

    /**
     * Add js to display field on form
     *
     * @param TemplateEvent $templateEvent
     */
    public function onTemplateMypageHistory(TemplateEvent $templateEvent)
    {
        $target_entity = 'order';
        $target_id = $templateEvent->getParameter('Order')->getId();
        $customFieldsContents = $this->customFieldsContentsRepository->getCustomFieldsContents($target_entity, $target_id);
        if (!isset($customFieldsContents) || !$customFieldsContents) {
            $customFieldsContents = $this->customFieldsContentsRepository->newCustomFieldsContents($target_entity, $target_id);
        }
        $templateEvent->setParameter('customFieldsContents', $customFieldsContents);

        // カスタムフィールドの定義を取得
        $customFields = $this->customFieldsRepository->getCustomFields($target_entity);
        $templateEvent->setParameter('customFields', $customFields);

        $templateEvent->addSnippet('@TabaCustomFields/snippet/mypage_history.twig');
    }
    /**
     * カスタムフィールド付きの会員情報CSVダウンロード リンク設置
     *
     * @param TemplateEvent $templateEvent
     */
    public function onAdminCfCustomerCsvDownloadLinkRender(TemplateEvent $templateEvent)
    {
        $templateEvent->addSnippet('@TabaCustomFields/admin/snippet/csv_download_customer_link.twig');
    }
    /**
     * カスタムフィールド付きの注文情報CSVダウンロード リンク設置
     *
     * @param TemplateEvent $templateEvent
     */
    public function onAdminCfOrderCsvDownloadLinkRender(TemplateEvent $templateEvent)
    {
        $templateEvent->addSnippet('@TabaCustomFields/admin/snippet/csv_download_order_link.twig');
    }
    /**
     * カスタムフィールド付きの商品情報CSVダウンロード リンク設置
     *
     * @param TemplateEvent $templateEvent
     */
    public function onAdminCfProductCsvDownloadLinkRender(TemplateEvent $templateEvent)
    {
        $templateEvent->addSnippet('@TabaCustomFields/admin/snippet/csv_download_product_link.twig');
    }
}
