<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\TabaCustomFields\Controller\Product;

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



/**
 * @Route(Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_URI_PREFIX, name=Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_BIND_PREFIX)
 */
class CsvController extends AbstractCsvController
{
    protected $searchQBTableAliasName = 'p';

    protected $targetEntity = 'product';


    /**
     * 商品CSVの出力.
     *
     * @Route("/product/export", name="product_csv_export")
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
            $this->csvExportService->initCsvType(CsvType::CSV_TYPE_PRODUCT);

            // ヘッダ行の出力.
            $this->csvExportService->exportHeader();

            // 商品データ検索用のクエリビルダを取得.
            $qb = $this->csvExportService
                ->getProductQueryBuilder($request);

            // Get stock status
            $isOutOfStock = 0;
            $session = $request->getSession();
            if ($session->has('eccube.admin.product.search')) {
                $searchData = $session->get('eccube.admin.product.search', []);
                if (isset($searchData['stock_status']) && $searchData['stock_status'] === 0) {
                    $isOutOfStock = 1;
                }
            }

            // joinする場合はiterateが使えないため, select句をdistinctする.
            // http://qiita.com/suin/items/2b1e98105fa3ef89beb7
            // distinctのmysqlとpgsqlの挙動をあわせる.
            // http://uedatakeshi.blogspot.jp/2010/04/distinct-oeder-by-postgresmysql.html
            $qb->resetDQLPart('select')
                ->resetDQLPart('orderBy')
                ->orderBy('p.update_date', 'DESC');

            if ($isOutOfStock) {
                $qb->select('p, pc')
                    ->distinct();
            } else {
                $qb->select('p')
                    ->distinct();
            }

            /** CustomField */
            // カスタムフィールド検索条件用のサブクエリ取得
            if ($session->has('eccube.admin.product.search')) {
                $viewData = $session->get('eccube.admin.product.search', []);
                $custom_fields_content_ids = $this->getCustomFieldSubQuery($viewData);
                // 取得したサブクエリをqbにセット
                if ($custom_fields_content_ids) {
                    $qb->andWhere($qb->expr()->in( $this->searchQBTableAliasName . '.id', ':custom_fields_content_ids'))
                    ->setParameter('custom_fields_content_ids', $custom_fields_content_ids);
                }
            }


            // データ行の出力.
            $this->csvExportService->setExportQueryBuilder($qb);

            $this->csvExportService->exportData(function ($entity, CsvExportService $csvService) use ($request, $customFieldsContentsRepository) {
                $Csvs = $csvService->getCsvs();

                /** @var $Product \Eccube\Entity\Product */
                $Product = $entity;

                /** @var $ProductClassess \Eccube\Entity\ProductClass[] */
                $ProductClassess = $Product->getProductClasses();


                /** @var $customFieldsContents */
                $target_entity = "product";
                if ($target_id = $Product->getId()) {
                    $customFieldsContents = $customFieldsContentsRepository->getCustomFieldsContents($target_entity, $target_id);
                }
                if (!isset($customFieldsContents) || !$customFieldsContents) {
                    // 新規
                    $customFieldsContents = $customFieldsContentsRepository->newCustomFieldsContents($target_entity, $target_id);            
                }

                foreach ($ProductClassess as $ProductClass) {
                    $ExportCsvRow = new ExportCsvRow();

                    // CSV出力項目と合致するデータを取得.
                    foreach ($Csvs as $Csv) {
                        // 商品データを検索.
                        $ExportCsvRow->setData($csvService->getData($Csv, $Product));
                        if ($ExportCsvRow->isDataNull()) {
                            // 商品規格情報を検索.
                            $ExportCsvRow->setData($csvService->getData($Csv, $ProductClass));
                        }

                        if ($ExportCsvRow->isDataNull()) {
                            // 商品カスタムフィールドをセット
                            $ExportCsvRow->setData($csvService->getData($Csv, $customFieldsContents));
                        }

                        $ExportCsvRow->pushData();
                    }

                    // $row[] = number_format(memory_get_usage(true));
                    // 出力.
                    $csvService->fputcsv($ExportCsvRow->getRow());
                }
            });
        });

        $now = new \DateTime();
        $filename = 'product_'.$now->format('YmdHis').'.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
        $response->send();

        log_info('商品CSV出力ファイル名', [$filename]);

        return $response;
    }
}
