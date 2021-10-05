<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\TabaCustomFields\Controller\Order;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Eccube\Request\Context;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eccube\Controller\AbstractController;
use Eccube\Entity\ExportCsvRow;
use Eccube\Entity\Master\CsvType;
use Eccube\Repository\BaseInfoRepository;
use Plugin\TabaCustomFields\Common\Constants;
use Plugin\TabaCustomFields\Controller\AbstractCsvController;
use Plugin\TabaCustomFields\Service\CsvExportService;
use Plugin\TabaCustomFields\Repository\CustomFieldsContentsRepository;
use Plugin\TabaCustomFields\Repository\CustomFieldsRepository;
use Plugin\TabaCustomFields\Common\UserConfig;

/**
 * @Route(Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_URI_PREFIX, name=Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_BIND_PREFIX)
 */
class CsvController extends AbstractCsvController
{   
    protected $searchQBTableAliasName = 'o';

    protected $targetEntity = 'order';


    /**
     * 受注CSVの出力.
     *
     * @Route("/order/export", name="order_csv_export")
     *
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function export(Request $request)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $this->entityManager;
        $em->getConfiguration()->setSQLLogger(null);

        // 追加フィールドリポジトリ
        $customFieldsContentsRepository = $this->customFieldsContentsRepository;

        $response = new StreamedResponse();
        $response->setCallback(function () use ($request, $customFieldsContentsRepository) {
            // CSV種別を元に初期化.
            $this->csvExportService->initCsvType(CsvType::CSV_TYPE_ORDER);

            // ヘッダ行の出力.
            $this->csvExportService->exportHeader();

            // 受注データ検索用のクエリビルダを取得.
            $qb = $this->csvExportService
                ->getOrderQueryBuilder($request);

            // 出力項目を追加
            $qb->addSelect('p', 'c');
            $qb->leftJoin('oi.Product', 'p');
            $qb->leftJoin('o.Customer', 'c');

            // カスタムフィールドの値を取得用のクエリビルダ
            $cf_qb = $this->csvExportService
            ->getOrderQueryBuilder($request);


            // カスタムフィールド検索条件用のサブクエリセット
            $session = $request->getSession();
            if ($session->has('eccube.admin.product.search')) {
                $viewData = $session->get('eccube.admin.order.search', []);
                $custom_fields_content_ids = $this->getCustomFieldSubQuery($viewData);
                if ($custom_fields_content_ids) {
                    $qb->andWhere($qb->expr()->in( $this->searchQBTableAliasName . '.id', ':custom_fields_content_ids'))
                    ->setParameter('custom_fields_content_ids', $custom_fields_content_ids);

                    $cf_qb->andWhere($qb->expr()->in( $this->searchQBTableAliasName . '.id', ':custom_fields_content_ids'))
                    ->setParameter('custom_fields_content_ids', $custom_fields_content_ids);
                }
            }

            // カスタムフィールドの値を取得
            $Orders = $cf_qb->getQuery()->getResult();
            if ($Orders) {
                $ids['order'] = [];
                $ids['customer'] = [];
                $ids['product'] = [];
                foreach ($Orders as $Order) {
                    $ids['order'][] = $Order->getId();
                    if ($Order->getCustomer()) {
                        $ids['customer'][] = $Order->getCustomer()->getId();
                    }
                    $OrderItems = $Order->getOrderItems();
                    foreach ($OrderItems as $OrderItem) {
                        if ($OrderItem->getProduct() && method_exists($OrderItem->getProduct(),'getId')) {
                            $ids['product'][] = $OrderItem->getProduct()->getId();
                        }
                    }
                }

                $condition = [];
                $_or_condition = [];
                $cfc_qb = $customFieldsContentsRepository->createQueryBuilder('cfc');
                if (count($ids['order'])>0) { 
                    $__condition = [];
                    $__condition[] = $cfc_qb->expr()->in('cfc.targetId', $ids['order']);
                    $__condition[] = $cfc_qb->expr()->eq('cfc.entity', ':order');
                    $_or_condition[] = call_user_func_array(array($cfc_qb->expr(), 'andX'), $__condition);
                }
                if (count($ids['customer'])>0) { 
                    $__condition = [];
                    $__condition[] = $cfc_qb->expr()->in('cfc.targetId', $ids['customer']);
                    $__condition[] = $cfc_qb->expr()->eq('cfc.entity', ':customer');
                    $_or_condition[] = call_user_func_array(array($cfc_qb->expr(), 'andX'), $__condition);
                }
                if (count($ids['product'])>0) { 
                    $__condition = [];
                    $__condition[] = $cfc_qb->expr()->in('cfc.targetId', $ids['product']);
                    $__condition[] = $cfc_qb->expr()->eq('cfc.entity', ':product');
                    $_or_condition[] = call_user_func_array(array($cfc_qb->expr(), 'andX'), $__condition);
                }
                if (count($_or_condition)>0) {
                    $condition[] = call_user_func_array(array($cfc_qb->expr(), 'orX'), $_or_condition);
                }
                $condition = call_user_func_array(array($qb->expr(), 'andX'), $condition);
                $cfc_qb->where($condition);
                $cfc_qb->setParameter('order', 'order');
                $cfc_qb->setParameter('customer', 'customer');
                $cfc_qb->setParameter('product', 'product');
                // $sql = $cfc_qb->getQuery()->getSQL();
                $CustomFieldsContents = $cfc_qb->getQuery()->getResult();
            }


            // データ行の出力.
            $this->csvExportService->setExportQueryBuilder($qb);
            $this->csvExportService->exportData(function ($entity, $csvService) use ($request, $CustomFieldsContents) {
                $Csvs = $csvService->getCsvs();

                $Order = $entity;
                $OrderItems = $Order->getOrderItems();

                /** @var $customFieldsContents */
                // 注文 情報のカスタムフィールド
                if (isset($OrderCustomFieldsContents)) { unset($OrderCustomFieldsContents); }
                foreach ($CustomFieldsContents as $CustomFieldsContent) { 
                    if ($CustomFieldsContent->getTargetId() == $Order->getId() && $CustomFieldsContent->getEntity() == "order") {
                        $OrderCustomFieldsContents = $CustomFieldsContent;
                        break;
                    }
                }

                /** @var $customFieldsContents */
                // 会員 情報のカスタムフィールド
                if (isset($CustomerCustomFieldsContents)) { unset($CustomerCustomFieldsContents); }
                if ($Order->getCustomer()) {
                    foreach ($CustomFieldsContents as $CustomFieldsContent) { 
                        if ($CustomFieldsContent->getTargetId() == $Order->getCustomer()->getId() && $CustomFieldsContent->getEntity() == "customer") {
                            $CustomerCustomFieldsContents = $CustomFieldsContent;
                            break;
                        }
                    }
                }
                foreach ($OrderItems as $OrderItem) {
                    $ExportCsvRow = new ExportCsvRow();

                    /** @var $customFieldsContents */
                    // 商品 情報のカスタムフィールド
                    if (isset($ProductCustomFieldsContents)) { unset($ProductCustomFieldsContents); }
                    foreach ($CustomFieldsContents as $CustomFieldsContent) { 
                        if (($OrderItem->getProduct() && method_exists($OrderItem->getProduct(),'getId')) && $CustomFieldsContent->getTargetId() == $OrderItem->getProduct()->getId() && $CustomFieldsContent->getEntity() == "product") {
                            $ProductCustomFieldsContents = $CustomFieldsContent;
                            break;
                        }
                    }

                    // CSV出力項目と合致するデータを取得.
                    foreach ($Csvs as $Csv) {
                        // 受注データを検索.
                        $ExportCsvRow->setData($csvService->getData($Csv, $Order));
                        if ($ExportCsvRow->isDataNull()) {
                            // 受注データにない場合は, 受注明細を検索.
                            $ExportCsvRow->setData($csvService->getData($Csv, $OrderItem));
                        }
                        if ($ExportCsvRow->isDataNull() && $Shipping = $OrderItem->getShipping()) {
                            // 受注明細データにない場合は, 出荷を検索.
                            $ExportCsvRow->setData($csvService->getData($Csv, $Shipping));
                        }
                        

                        if ($ExportCsvRow->isDataNull()) {
                            // カスタムフィールドをセット
                            if (isset($OrderCustomFieldsContents)) {
                                $ExportCsvRow->setData($csvService->getData($Csv, $OrderCustomFieldsContents));
                            }
                        }
                        if ($ExportCsvRow->isDataNull()) {
                            if (isset($ProductCustomFieldsContents)) {
                                $ExportCsvRow->setData($csvService->getData($Csv, $ProductCustomFieldsContents, Constants::PRODUCT_CUSTOM_FIELDS_CONTENTS_ENTITY));
                            }
                        }
                        if ($ExportCsvRow->isDataNull()) {
                            if (isset($CustomerCustomFieldsContents)) {
                                $ExportCsvRow->setData($csvService->getData($Csv, $CustomerCustomFieldsContents, Constants::CUSTOMER_CUSTOM_FIELDS_CONTENTS_ENTITY));
                            }
                        }

                        $ExportCsvRow->pushData();
                    }

                    //$row[] = number_format(memory_get_usage(true));
                    // 出力.
                    $csvService->fputcsv($ExportCsvRow->getRow());
                }
            });
        });

        $fileName = 'order_'.(new \DateTime())->format('YmdHis').'.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$fileName);
        $response->send();

        return $response;
    }
}
