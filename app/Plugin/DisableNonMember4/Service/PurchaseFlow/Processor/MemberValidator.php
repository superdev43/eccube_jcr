<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\DisableNonMember4\Service\PurchaseFlow\Processor;

use Eccube\Annotation\CartFlow;
use Eccube\Annotation\OrderFlow;
use Eccube\Annotation\ShoppingFlow;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\ItemInterface;
use Eccube\Entity\Order;
use Eccube\Service\PurchaseFlow\InvalidItemException;
use Eccube\Service\PurchaseFlow\ItemHolderPostValidator;
use Eccube\Service\PurchaseFlow\ItemHolderValidator;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\ItemValidator;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * 商品を１個のみしか購入できないようにするサンプル
 *
 * # 使い方
 * PurchaseFlowに新しいProcessorを追加する
 *
 * ## 追加できるプロセッサ
 * 以下のクラスまたはインタフェースを継承または実装している必要がある
 * * ItemPreprocessor
 * * ItemValidator
 * * ItemHolderPreprocessor
 * * ItemHolderValidator
 * * PurchaseProcessor
 *
 * ## 追加対象のフローの指定方法
 * * カートのPurchaseFlowにProcessorを追加する場合はCartFlowアノテーションを追加
 * * 購入フローのPurchaseFlowにProcessorを追加する場合はShoppingFlowアノテーションを追加
 * * 管理画面でのPurchaseFlowにProcessorを追加する場合はOrderFlowアノテーションを追加
 *
 * @ShoppingFlow
 */
class MemberValidator extends ItemHolderValidator
{

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * DeliveryFeePreprocessor constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(
        RequestStack $requestStack,
        EccubeConfig $eccubeConfig
    ) {
        $this->requestStack = $requestStack;
        $this->eccubeConfig = $eccubeConfig;
    }


    /**
     * @param ItemHolderInterface $itemHolder
     * @param PurchaseContext $context
     * @throws InvalidItemException
     */
    protected function validate(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        /* @var $Order Order */
        $Order = $itemHolder;

        // 受注の生成直後はチェックしない.
        if (!$Order->getCustomer()) {
            $this->throwInvalidItemException('please.login.to.purchase', null, false);
        }

    }

    protected function handle(ItemHolderInterface $item)
    {

    }
}
